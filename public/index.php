<?php

declare(strict_types=1);

/**
 * Front controller — todas as requisições passam por aqui.
 */

$config = require dirname(__DIR__) . '/app/bootstrap.php';

use Core\Router;

$router = new Router();
(require dirname(__DIR__) . '/app/routes.php')($router);

try {
    $router->dispatch($_SERVER['REQUEST_URI'] ?? '/');
} catch (\Throwable $e) {
    if ($config['app']['debug']) {
        http_response_code(500);
        echo '<pre style="padding:20px;font:14px/1.5 monospace;color:#b00">';
        echo e($e::class . ': ' . $e->getMessage()) . "\n\n";
        echo e($e->getFile() . ':' . $e->getLine()) . "\n\n";
        echo e($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        try {
            \Core\View::render('errors/500', ['title' => 'Erro interno'], 'layouts/site');
        } catch (\Throwable) {
            echo '<h1>Ops! Algo deu errado.</h1>';
        }
    }
}
