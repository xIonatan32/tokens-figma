<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\FigmaFile> $figmaFiles
 */
?>
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">Imported Figma Files</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">List of all imported files.</p>
        </div>
        <?= $this->Html->link('Import New File', ['action' => 'add'], ['class' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500']) ?>
    </div>
    <div class="border-t border-gray-200">
        <ul role="list" class="divide-y divide-gray-200">
            <?php foreach ($figmaFiles as $file): ?>
            <li class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center flex-1 min-w-0">
                        <?php if ($file->thumbnail_url): ?>
                            <img class="h-12 w-12 rounded-md object-cover mr-4" src="<?= h($file->thumbnail_url) ?>" alt="">
                        <?php else: ?>
                            <div class="h-12 w-12 rounded-md bg-gray-200 mr-4 flex items-center justify-center text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <a href="<?= $this->Url->build(['action' => 'view', $file->id]) ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 truncate block">
                                <?= h($file->name) ?>
                            </a>
                            <p class="text-xs text-gray-500 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <?= h($file->file_key) ?>
                                </span>
                                <span class="ml-2">Imported on <?= $file->created->format('M d, Y H:i') ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="ml-4 flex items-center space-x-2">
                        <!-- Sync Button -->
                        <button onclick="openSyncModal(<?= $file->id ?>, '<?= h($file->name) ?>')" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Sync
                        </button>
                        
                        <!-- Delete Button -->
                        <?= $this->Form->postLink(
                            '<svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>Delete',
                            ['action' => 'delete', $file->id],
                            [
                                'confirm' => 'Are you sure you want to delete "' . $file->name . '"?',
                                'class' => 'inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
                                'escape' => false
                            ]
                        ) ?>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
            <?php if ($figmaFiles->items()->isEmpty()): ?>
                <li class="px-4 py-12 text-center text-gray-500">
                    No files imported yet.
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Sync Modal -->
<div id="syncModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeSyncModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <?= $this->Form->create(null, ['id' => 'syncForm', 'url' => ['action' => 'sync', 0]]) ?>
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Sync <span id="syncFileName"></span>
                        </h3>
                        <div class="mt-4">
                            <label for="sync_token" class="block text-sm font-medium text-gray-700 text-left">Personal Access Token</label>
                            <input type="password" name="token" id="sync_token" required class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            <p class="mt-2 text-sm text-gray-500 text-left">Enter your Figma token to re-import this file with the latest changes.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Sync Now
                    </button>
                    <button type="button" onclick="closeSyncModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
function openSyncModal(fileId, fileName) {
    document.getElementById('syncModal').classList.remove('hidden');
    document.getElementById('syncFileName').textContent = fileName;
    document.getElementById('syncForm').action = '<?= $this->Url->build(['action' => 'sync']) ?>/' + fileId;
    document.getElementById('sync_token').value = '';
}

function closeSyncModal() {
    document.getElementById('syncModal').classList.add('hidden');
}
</script>
