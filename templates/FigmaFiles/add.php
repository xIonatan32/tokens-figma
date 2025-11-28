<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FigmaFile $figmaFile
 */
?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Import Figma File</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Enter your Figma File Key and Personal Access Token to import data.</p>
            </div>
            
            <?= $this->Form->create($figmaFile, ['class' => 'mt-5 space-y-6']) ?>
                <div>
                    <label for="file_key" class="block text-sm font-medium text-gray-700">File Key</label>
                    <div class="mt-1">
                        <?= $this->Form->control('file_key', [
                            'label' => false,
                            'class' => 'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border',
                            'placeholder' => 'e.g. fK3M8...'
                        ]) ?>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Found in the file URL: figma.com/file/<strong>FILE_KEY</strong>/...</p>
                </div>

                <div>
                    <label for="token" class="block text-sm font-medium text-gray-700">Personal Access Token</label>
                    <div class="mt-1">
                        <?= $this->Form->control('token', [
                            'label' => false,
                            'type' => 'password',
                            'class' => 'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border',
                            'value' => '' // Don't retain value
                        ]) ?>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Generate one in Figma Settings -> Account -> Personal Access Tokens</p>
                </div>

                <div class="pt-5">
                    <div class="flex justify-end">
                        <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500']) ?>
                        <?= $this->Form->button('Import File', ['class' => 'ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500']) ?>
                    </div>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
