<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$buildDir = __DIR__ . '/../public/build';

if (! is_dir($buildDir)) {
    mkdir($buildDir, 0755, true);
}

$manifestPath = $buildDir . '/manifest.json';

if (! file_exists($manifestPath)) {
    $manifest = [];

    // Основные точки входа
    if (file_exists(__DIR__ . '/../resources/js/app.tsx')) {
        $manifest['resources/js/app.tsx'] = [
            'file' => 'assets/app.js',
            'src' => 'resources/js/app.tsx',
            'isEntry' => true,
        ];
    } elseif (file_exists(__DIR__ . '/../resources/js/app.js')) {
        $manifest['resources/js/app.js'] = [
            'file' => 'assets/app.js',
            'src' => 'resources/js/app.js',
            'isEntry' => true,
        ];
    }

    if (file_exists(__DIR__ . '/../resources/css/app.css')) {
        $manifest['resources/css/app.css'] = [
            'file' => 'assets/app.css',
            'src' => 'resources/css/app.css',
        ];
    }

    // Страницы Inertia
    $pagesDir = __DIR__ . '/../resources/js/pages';
    if (is_dir($pagesDir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pagesDir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'tsx') {
                $relative = 'resources/js/pages/' . str_replace('\\', '/', $iterator->getSubPathName());
                $manifest[$relative] = [
                    'file' => 'assets/' . $file->getBasename('.tsx') . '.js',
                    'src' => $relative,
                ];
            }
        }
    }

    file_put_contents(
        $manifestPath,
        json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
}

