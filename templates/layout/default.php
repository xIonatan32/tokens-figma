<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'Figma Data Extractor';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <script src="https://cdn.tailwindcss.com"></script>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        <a href="<?= $this->Url->build('/') ?>" class="flex items-center">
                            <img src="https://aiven.io/remix-assets/logo-aiven-DLhBz3IO.svg" alt="Aiven" class="h-8">
                        </a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="<?= $this->Url->build(['controller' => 'FigmaFiles', 'action' => 'index']) ?>" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Files
                        </a>
                        <a href="<?= $this->Url->build(['controller' => 'FigmaFiles', 'action' => 'add']) ?>" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Import
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Custom Flash Messages -->
            <?php
                $flash = $this->request->getSession()->read('Flash');
                if ($flash):
                    foreach ($flash as $key => $messages):
                        foreach ($messages as $message):
                            $type = $message['type'] ?? 'info';
                            
                            // Determine styling based on message type
                            $styles = [
                                'success' => [
                                    'bg' => 'bg-green-50',
                                    'border' => 'border-green-400',
                                    'text' => 'text-green-800',
                                    'icon_bg' => 'bg-green-100',
                                    'icon_color' => 'text-green-600',
                                    'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />'
                                ],
                                'error' => [
                                    'bg' => 'bg-red-50',
                                    'border' => 'border-red-400',
                                    'text' => 'text-red-800',
                                    'icon_bg' => 'bg-red-100',
                                    'icon_color' => 'text-red-600',
                                    'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />'
                                ],
                                'warning' => [
                                    'bg' => 'bg-yellow-50',
                                    'border' => 'border-yellow-400',
                                    'text' => 'text-yellow-800',
                                    'icon_bg' => 'bg-yellow-100',
                                    'icon_color' => 'text-yellow-600',
                                    'icon' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />'
                                ],
                                'info' => [
                                    'bg' => 'bg-blue-50',
                                    'border' => 'border-blue-400',
                                    'text' => 'text-blue-800',
                                    'icon_bg' => 'bg-blue-100',
                                    'icon_color' => 'text-blue-600',
                                    'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />'
                                ]
                            ];
                            
                            $style = $styles[$type] ?? $styles['info'];
            ?>
            <div class="<?= $style['bg'] ?> border-l-4 <?= $style['border'] ?> p-4 mb-4 rounded-r-lg shadow-md" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="<?= $style['icon_bg'] ?> rounded-full p-1">
                            <svg class="h-5 w-5 <?= $style['icon_color'] ?>" viewBox="0 0 20 20" fill="currentColor">
                                <?= $style['icon'] ?>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium <?= $style['text'] ?>">
                            <?= h($message['message']) ?>
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md p-1.5 <?= $style['text'] ?> hover:bg-opacity-20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-<?= explode('-', $style['bg'])[1] ?>-50 focus:ring-<?= explode('-', $style['border'])[1] ?>-600">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                        endforeach;
                    endforeach;
                    // Clear flash messages after displaying
                    $this->request->getSession()->delete('Flash');
                endif;
            ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                Built with 💗 by <strong>Ioan N.</strong> for <strong>Aquarium Design System</strong>
            </p>
        </div>
    </footer>
</body>
</html>
