<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FigmaFile $figmaFile
 */
?>
<div class="space-y-6">
    <!-- File Header -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex items-center">
            <?php if ($figmaFile->thumbnail_url): ?>
                <img class="h-16 w-16 rounded-lg object-cover mr-4" src="<?= h($figmaFile->thumbnail_url) ?>" alt="">
            <?php endif; ?>
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900"><?= h($figmaFile->name) ?></h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Key: <?= h($figmaFile->file_key) ?></p>
            </div>
            <div class="ml-auto">
                <?= $this->Html->link('Back to List', ['action' => 'index'], ['class' => 'text-indigo-600 hover:text-indigo-900']) ?>
            </div>
        </div>
    </div>

    <!-- Styles Sections -->
    <?php 
        // Separate styles by type
        $textStyles = [];
        $colorStyles = [];
        
        foreach ($figmaFile->figma_nodes as $node) {
            if (strpos($node->type, 'STYLE_TEXT') !== false) {
                $textStyles[] = $node;
            } elseif (strpos($node->type, 'STYLE_FILL') !== false) {
                $colorStyles[] = $node;
            }
        }
    ?>

    <!-- Text Styles Section -->
    <?php if (!empty($textStyles)): ?>
    <div>
        <h4 class="text-lg font-medium text-gray-900 mb-4">Text Styles (<?= count($textStyles) ?>)</h4>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($textStyles as $node): ?>
                <?php 
                    $rawData = json_decode((string)$node->raw_data, true);
                ?>
                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= h(str_replace('STYLE_', '', $node->type)) ?>
                            </span>
                        </div>
                        
                        <h5 class="text-base font-bold text-gray-900 mb-4" title="<?= h($node->name) ?>">
                            <?= h($node->name) ?>
                        </h5>

                        <!-- Visual Display -->
                        <?php if (!empty($rawData['textStyle'])): ?>
                            <?php $textStyle = $rawData['textStyle']; ?>
                            <div class="space-y-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <?php if (!empty($textStyle['fontFamily'])): ?>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Font Family:</span>
                                        <span class="text-sm font-semibold text-gray-900"><?= h($textStyle['fontFamily']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($textStyle['fontWeight'])): ?>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Weight:</span>
                                        <span class="text-sm font-semibold text-gray-900"><?= h($textStyle['fontWeight']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($textStyle['fontSize'])): ?>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Size:</span>
                                        <span class="text-sm font-semibold text-gray-900"><?= h($textStyle['fontSize']) ?>px</span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($textStyle['lineHeightPx'])): ?>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Line Height:</span>
                                        <span class="text-sm font-semibold text-gray-900"><?= h($textStyle['lineHeightPx']) ?>px</span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($textStyle['letterSpacing'])): ?>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Letter Spacing:</span>
                                        <span class="text-sm font-semibold text-gray-900"><?= h($textStyle['letterSpacing']) ?>px</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-500 italic">
                                No text properties available
                            </div>
                        <?php endif; ?>

                        <!-- Raw Data Toggle -->
                        <details class="mt-4">
                            <summary class="text-xs text-indigo-600 cursor-pointer hover:text-indigo-800">View Raw Data</summary>
                            <pre class="mt-2 text-xs bg-gray-50 p-2 rounded overflow-x-auto border border-gray-100"><?= json_encode($rawData, JSON_PRETTY_PRINT) ?></pre>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Color Styles Section -->
    <?php if (!empty($colorStyles)): ?>
    <div class="<?= !empty($textStyles) ? 'mt-8' : '' ?>">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Color Styles (<?= count($colorStyles) ?>)</h4>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($colorStyles as $node): ?>
                <?php 
                    $rawData = json_decode((string)$node->raw_data, true);
                ?>
                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <?= h(str_replace('STYLE_', '', $node->type)) ?>
                            </span>
                        </div>
                        
                        <h5 class="text-base font-bold text-gray-900 mb-4" title="<?= h($node->name) ?>">
                            <?= h($node->name) ?>
                        </h5>

                        <!-- Visual Display -->
                        <?php if (!empty($rawData['color'])): ?>
                            <?php 
                                $color = $rawData['color'];
                                $r = round($color['r'] * 255);
                                $g = round($color['g'] * 255);
                                $b = round($color['b'] * 255);
                                $a = $color['a'] ?? 1;
                                $rgbaColor = "rgba($r, $g, $b, $a)";
                                $hexColor = sprintf("#%02x%02x%02x", $r, $g, $b);
                            ?>
                            <div class="space-y-3">
                                <!-- Color Swatch -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-20 h-20 rounded-lg shadow-md border-2 border-gray-200" style="background-color: <?= $rgbaColor ?>"></div>
                                    <div class="flex-1">
                                        <div class="text-sm font-mono bg-gray-50 px-3 py-2 rounded border border-gray-200">
                                            <div class="font-semibold text-gray-700"><?= $hexColor ?></div>
                                            <div class="text-xs text-gray-500 mt-1">RGB(<?= $r ?>, <?= $g ?>, <?= $b ?>)</div>
                                            <?php if ($a < 1): ?>
                                                <div class="text-xs text-gray-500">Opacity: <?= round($a * 100) ?>%</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-500 italic">
                                No color properties available
                            </div>
                        <?php endif; ?>

                        <!-- Raw Data Toggle -->
                        <details class="mt-4">
                            <summary class="text-xs text-indigo-600 cursor-pointer hover:text-indigo-800">View Raw Data</summary>
                            <pre class="mt-2 text-xs bg-gray-50 p-2 rounded overflow-x-auto border border-gray-100"><?= json_encode($rawData, JSON_PRETTY_PRINT) ?></pre>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
