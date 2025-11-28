<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Client;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Log\Log;

class FigmaService
{
    use LocatorAwareTrait;

    protected Client $client;
    protected string $currentToken = '';

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
        ]);
    }

    /**
     * Fetch file from Figma API and save to database.
     *
     * @param string $fileKey
     * @param string $token
     * @return \App\Model\Entity\FigmaFile|null
     */
    public function importFile(string $fileKey, string $token)
    {
        try {
            // Store token for later use
            $this->currentToken = $token;
            
            // Ensure key is safe for URL
            $encodedKey = urlencode($fileKey);
            $url = "https://api.figma.com/v1/files/{$encodedKey}";

            $response = $this->client->get($url, [], [
                'headers' => ['X-Figma-Token' => $token]
            ]);

            if (!$response->isOk()) {
                $body = $response->getStringBody();
                $errorMsg = "Figma API Error ({$response->getStatusCode()}): " . $response->getReasonPhrase();
                if (!empty($body)) {
                    $json = json_decode($body, true);
                    if (isset($json['err'])) {
                        $errorMsg .= " - " . $json['err'];
                    } elseif (isset($json['message'])) {
                        $errorMsg .= " - " . $json['message'];
                    }
                }
                throw new \Exception($errorMsg);
            }

            $data = $response->getJson();
            
            if (empty($data['document'])) {
                throw new \Exception("Invalid Figma response: Document structure is missing.");
            }

            return $this->saveFile($fileKey, $data, $token);

        } catch (\Exception $e) {
            Log::error("Figma Service Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save file and variables to database.
     *
     * @param string $fileKey
     * @param array $data
     * @param string $token
     * @return \App\Model\Entity\FigmaFile
     */
    protected function saveFile(string $fileKey, array $data, string $token)
    {
        $filesTable = $this->fetchTable('FigmaFiles');
        $nodesTable = $this->fetchTable('FigmaNodes');

        // Check if file exists, update or create
        $file = $filesTable->find()->where(['file_key' => $fileKey])->first();
        if (!$file) {
            $file = $filesTable->newEmptyEntity();
            $file->file_key = $fileKey;
        }

        $file->name = $data['name'] ?? 'Untitled';
        $file->thumbnail_url = $data['thumbnailUrl'] ?? null;
        
        $filesTable->saveOrFail($file);

        // Clear existing nodes for this file (simple sync strategy)
        $nodesTable->deleteAll(['figma_file_id' => $file->id]);

        // Try to extract variables from the main file response first
        $variables = $this->extractVariablesFromFileData($data, $file->id, $fileKey);
        
        // If no variables found in file data, try the dedicated endpoint
        if (empty($variables)) {
            Log::info("No styles/variables in file data, trying dedicated API endpoint...");
            try {
                $variables = $this->fetchVariablesFromAPI($fileKey, $file->id);
            } catch (\Exception $e) {
                $errorCode = $e->getMessage();
                Log::warning("Variables API failed: " . $errorCode);
                
                // Provide helpful error message based on error type
                if (strpos($errorCode, '403') !== false) {
                    throw new \Exception("No styles or variables found. Note: Your Figma token doesn't have permission to access the Variables API (HTTP 403). The file was imported successfully, but only styles (if any) were extracted. To access variables, you may need to generate a new token with 'File content' read permissions in Figma.");
                } else {
                    throw new \Exception("No styles or variables found in this file. Error: " . $errorCode);
                }
            }
        }
        
        // Save variables as nodes
        if (!empty($variables)) {
            Log::info("Saving " . count($variables) . " variables to database");
            foreach (array_chunk($variables, 50) as $chunk) {
                $entities = $nodesTable->newEntities($chunk);
                $nodesTable->saveMany($entities);
            }
        } else {
            throw new \Exception("No styles or variables found in this Figma file. Please ensure the file has color styles, text styles, or variables defined.");
        }

        return $file;
    }

    /**
     * Extract styles (colors, text, effects) from the main file data response.
     *
     * @param array $data
     * @param int $fileId
     * @param string $fileKey
     * @return array
     */
    protected function extractVariablesFromFileData(array $data, int $fileId, string $fileKey): array
    {
        $variablesToSave = [];
        $styleDefinitions = [];

        // First, collect style metadata
        if (!empty($data['styles'])) {
            foreach ($data['styles'] as $styleId => $style) {
                $styleDefinitions[$styleId] = $style;
            }
        }

        // Traverse the document tree to find nodes that use these styles
        if (!empty($data['document'])) {
            $this->extractStyleValues($data['document'], $styleDefinitions);
        }

        // Check if we got any style values from the document
        $stylesWithValues = 0;
        foreach ($styleDefinitions as $style) {
            if (isset($style['color']) || isset($style['textStyle']) || isset($style['fills'])) {
                $stylesWithValues++;
            }
        }
        
        // If no styles have values, try fetching them directly from the Figma nodes API
        if ($stylesWithValues === 0 && !empty($styleDefinitions)) {
            $this->fetchStyleNodesDirectly($fileKey, $styleDefinitions);
        }

        // Save the enriched styles
        foreach ($styleDefinitions as $styleId => $style) {
            $variablesToSave[] = [
                'figma_file_id' => $fileId,
                'node_id' => $styleId,
                'name' => $style['name'] ?? 'Unnamed Style',
                'type' => 'STYLE_' . strtoupper($style['styleType'] ?? 'UNKNOWN'),
                'raw_data' => json_encode($style),
            ];
        }

        // Also check for variables in the response (newer API)
        if (!empty($data['variables'])) {
            foreach ($data['variables'] as $varId => $variable) {
                $variablesToSave[] = [
                    'figma_file_id' => $fileId,
                    'node_id' => $varId,
                    'name' => $variable['name'] ?? 'Unnamed Variable',
                    'type' => 'VARIABLE_' . strtoupper($variable['resolvedType'] ?? 'UNKNOWN'),
                    'raw_data' => json_encode($variable),
                ];
            }
        }

        return $variablesToSave;
    }

    /**
     * Attempt to fetch style node data directly from Figma API.
     *
     * @param string $fileKey
     * @param array &$styleDefinitions
     */
    protected function fetchStyleNodesDirectly(string $fileKey, array &$styleDefinitions): void
    {
        try {
            $styleIds = array_slice(array_keys($styleDefinitions), 0, 10);
            
            if (empty($styleIds)) {
                return;
            }
            
            $encodedKey = urlencode($fileKey);
            $nodeIds = implode(',', array_map('urlencode', $styleIds));
            $url = "https://api.figma.com/v1/files/{$encodedKey}/nodes?ids={$nodeIds}";
            
            $response = $this->client->get($url, [], [
                'headers' => ['X-Figma-Token' => $this->currentToken],
                'timeout' => 15
            ]);
            
            if (!$response->isOk()) {
                Log::warning("Failed to fetch style nodes: HTTP " . $response->getStatusCode());
                return;
            }
            
            $nodesData = $response->getJson();
            
            if (!empty($nodesData['nodes'])) {
                foreach ($nodesData['nodes'] as $nodeId => $nodeWrapper) {
                    if (isset($styleDefinitions[$nodeId]) && !empty($nodeWrapper['document'])) {
                        $node = $nodeWrapper['document'];
                        $styleDef = &$styleDefinitions[$nodeId];
                        
                        // Extract properties based on style type
                        if ($styleDef['styleType'] === 'FILL' && !empty($node['fills'])) {
                            $styleDef['fills'] = $node['fills'];
                            if (!empty($node['fills'][0]['color'])) {
                                $styleDef['color'] = $node['fills'][0]['color'];
                            }
                        } elseif ($styleDef['styleType'] === 'TEXT' && !empty($node['style'])) {
                            $styleDef['textStyle'] = $node['style'];
                        } elseif ($styleDef['styleType'] === 'EFFECT' && !empty($node['effects'])) {
                            $styleDef['effects'] = $node['effects'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error fetching style nodes: " . $e->getMessage());
        }
    }

    /**
     * Recursively traverse nodes to extract actual style values.
     *
     * @param array $node
     * @param array &$styleDefinitions
     */
    protected function extractStyleValues(array $node, array &$styleDefinitions): void
    {
        if (!empty($node['styles'])) {
            foreach ($node['styles'] as $styleType => $styleId) {
                if (isset($styleDefinitions[$styleId])) {
                    switch ($styleType) {
                        case 'fill':
                            if (!empty($node['fills'])) {
                                $styleDefinitions[$styleId]['fills'] = $node['fills'];
                                if (!empty($node['fills'][0]['color'])) {
                                    $styleDefinitions[$styleId]['color'] = $node['fills'][0]['color'];
                                }
                            }
                            break;
                        case 'stroke':
                            if (!empty($node['strokes'])) {
                                $styleDefinitions[$styleId]['strokes'] = $node['strokes'];
                            }
                            break;
                        case 'text':
                            if (!empty($node['style'])) {
                                $styleDefinitions[$styleId]['textStyle'] = $node['style'];
                            }
                            break;
                        case 'effect':
                            if (!empty($node['effects'])) {
                                $styleDefinitions[$styleId]['effects'] = $node['effects'];
                            }
                            break;
                    }
                }
            }
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->extractStyleValues($child, $styleDefinitions);
            }
        }
    }

    /**
     * Fetch variables from dedicated Figma API endpoint.
     *
     * @param string $fileKey
     * @param int $fileId
     * @return array
     */
    protected function fetchVariablesFromAPI(string $fileKey, int $fileId): array
    {
        try {
            $encodedKey = urlencode($fileKey);
            $url = "https://api.figma.com/v1/files/{$encodedKey}/variables/local";

            // Use a shorter timeout for the variables endpoint since it might not exist
            $response = $this->client->get($url, [], [
                'headers' => ['X-Figma-Token' => $this->currentToken],
                'timeout' => 10 // Shorter timeout
            ]);

            if (!$response->isOk()) {
                $errorMsg = "Variables API returned HTTP {$response->getStatusCode()}";
                Log::warning($errorMsg);
                throw new \Exception($errorMsg);
            }

            $data = $response->getJson();
            
            // Log the raw response for debugging
            Log::debug("Variables API Response: " . json_encode($data));
            
            $variablesToSave = [];

            // Check if we have the expected structure
            if (empty($data['meta'])) {
                throw new \Exception("Variables API response missing 'meta' field.");
            }

            if (empty($data['meta']['variableCollections'])) {
                throw new \Exception("No variable collections found.");
            }

            if (empty($data['meta']['variables'])) {
                throw new \Exception("No variables found.");
            }

            Log::info("Found " . count($data['meta']['variableCollections']) . " variable collections and " . count($data['meta']['variables']) . " variables");

            // Process variable collections
            foreach ($data['meta']['variableCollections'] as $collectionId => $collection) {
                // Process each variable in this collection
                foreach ($data['meta']['variables'] as $varId => $variable) {
                    if ($variable['variableCollectionId'] === $collectionId) {
                        $variablesToSave[] = [
                            'figma_file_id' => $fileId,
                            'node_id' => $varId,
                            'name' => $variable['name'],
                            'type' => 'VARIABLE_' . strtoupper($variable['resolvedType']),
                            'raw_data' => json_encode([
                                'collection' => $collection['name'],
                                'resolvedType' => $variable['resolvedType'],
                                'valuesByMode' => $variable['valuesByMode'] ?? [],
                                'scopes' => $variable['scopes'] ?? [],
                                'hiddenFromPublishing' => $variable['hiddenFromPublishing'] ?? false,
                            ]),
                        ];
                    }
                }
            }

            Log::info("Prepared " . count($variablesToSave) . " variables to save");
            return $variablesToSave;

        } catch (\Exception $e) {
            Log::error("Error fetching variables from API: " . $e->getMessage());
            throw $e;
        }
    }
}
