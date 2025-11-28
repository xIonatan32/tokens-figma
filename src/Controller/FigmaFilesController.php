<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\FigmaService;
use Cake\Event\EventInterface;

/**
 * FigmaFiles Controller
 *
 * @property \App\Model\Table\FigmaFilesTable $FigmaFiles
 */
class FigmaFilesController extends AppController
{
    protected FigmaService $figmaService;

    public function initialize(): void
    {
        parent::initialize();
        $this->figmaService = new FigmaService();
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $figmaFiles = $this->paginate($this->FigmaFiles);

        $this->set(compact('figmaFiles'));
    }

    /**
     * View method
     *
     * @param string|null $id Figma File id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $figmaFile = $this->FigmaFiles->get($id, contain: ['FigmaNodes']);

        $this->set(compact('figmaFile'));
    }

    /**
     * Add method (Import)
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $figmaFile = $this->FigmaFiles->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $fileKey = $data['file_key'] ?? '';
            $token = $data['token'] ?? '';

            if (empty($fileKey) || empty($token)) {
                $this->Flash->error(__('Please provide both File Key and Access Token.'));
            } else {
                try {
                    // Extract key if user pasted a full URL
                    $cleanKey = $this->extractFileKey($fileKey);
                    
                    $result = $this->figmaService->importFile($cleanKey, $token);
                    
                    if ($result) {
                        $this->Flash->success(__('The figma file has been imported.'));
                        return $this->redirect(['action' => 'view', $result->id]);
                    }
                } catch (\Exception $e) {
                    $this->Flash->error(__('Import failed: ' . $e->getMessage()));
                }
            }
        }
        
        $this->set(compact('figmaFile'));
    }

    /**
     * Extract Figma File Key from input string (URL or Key).
     * 
     * @param string $input
     * @return string
     */
    protected function extractFileKey(string $input): string
    {
        $input = trim($input);
        
        // Check if it's a URL
        if (strpos($input, 'figma.com') !== false) {
            // Try to match /file/KEY pattern
            if (preg_match('/file\/([a-zA-Z0-9]+)/', $input, $matches)) {
                return $matches[1];
            }
        }
        
        // If it's not a URL, or regex failed, assume the input IS the key (or contains it)
        // Remove everything that is NOT alphanumeric
        // This handles "KEY?param=1", " KEY ", "KEY/"
        // Figma keys are typically alphanumeric strings like "fK3M8..."
        
        // First, if it looks like a path, take the last segment
        $parts = explode('/', $input);
        $candidate = end($parts);
        
        // Remove query params
        $parts = explode('?', $candidate);
        $candidate = $parts[0];
        
        // Finally, strip non-alphanumeric characters
        return preg_replace('/[^a-zA-Z0-9]/', '', $candidate);
    }

    /**
     * Delete method
     *
     * @param string|null $id Figma File id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $figmaFile = $this->FigmaFiles->get($id);
        
        if ($this->FigmaFiles->delete($figmaFile)) {
            $this->Flash->success(__('The figma file has been deleted.'));
        } else {
            $this->Flash->error(__('The figma file could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Sync method - Re-import an existing file
     *
     * @param string|null $id Figma File id.
     * @return \Cake\Http\Response|null Redirects on successful sync.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function sync($id = null)
    {
        $this->request->allowMethod(['post']);
        $figmaFile = $this->FigmaFiles->get($id);
        
        // Get the token from POST data
        $token = $this->request->getData('token');
        
        if (empty($token)) {
            $this->Flash->error(__('Please provide your Access Token to sync.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $result = $this->figmaService->importFile($figmaFile->file_key, $token);
            
            if ($result) {
                $this->Flash->success(__('The figma file has been synced successfully.'));
                return $this->redirect(['action' => 'view', $result->id]);
            }
        } catch (\Exception $e) {
            $this->Flash->error(__('Sync failed: ' . $e->getMessage()));
        }

        return $this->redirect(['action' => 'index']);
    }
}
