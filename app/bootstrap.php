<?php

declare(strict_types=1);

/**
 * Bootstrap da aplicação: autoload, configuração, sessão, banco e helpers.
 */

error_reporting(E_ALL);

$config = require __DIR__ . '/config/config.php';

ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

/**
 * Autoloader PSR-4 minimalista.
 *   Core\Foo            -> app/core/Foo.php
 *   Models\Foo          -> app/models/Foo.php
 *   Controllers\Foo     -> app/controllers/Foo.php
 *   Controllers\Admin\X -> app/controllers/admin/X.php
 *   Services\Foo        -> app/services/Foo.php
 */
spl_autoload_register(static function (string $class): void {
    $map = [
        'Core\\'        => __DIR__ . '/core/',
        'Models\\'      => __DIR__ . '/models/',
        'Controllers\\' => __DIR__ . '/controllers/',
        'Services\\'    => __DIR__ . '/services/',
    ];
    foreach ($map as $prefix => $dir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        $relative = str_replace('\\', '/', $relative);
        // pastas em minúsculo (admin/), arquivo mantém o nome da classe
        $parts = explode('/', $relative);
        $filename = array_pop($parts);
        $folders = array_map('strtolower', $parts);
        $path = $dir . ($folders ? implode('/', $folders) . '/' : '') . $filename . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

require __DIR__ . '/core/helpers.php';

Core\Database::init($config['db']);

// ---- Sessão --------------------------------------------------------
session_name($config['session']['name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => str_starts_with($config['app']['url'], 'https://'),
]);
session_start();

return $config;
