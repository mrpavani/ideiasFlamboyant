<?php

declare(strict_types=1);

/**
 * Router para o servidor embutido do PHP (uso local: `php -S localhost:8000 server.php`).
 *
 * O servidor embutido do PHP não lê .htaccess, então este arquivo replica
 * localmente as mesmas regras de bloqueio que a produção aplica via Apache
 * (Require all denied em app/, database/, bin/, logo/ e em dotfiles).
 */

$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if (preg_match('#^/(app|database|bin|logo)(/|$)#', $path) || preg_match('#(^|/)\.#', $path)) {
    http_response_code(403);
    echo '403 Forbidden';
    return true;
}

// Arquivo estático existente (assets/, uploads/...) — deixa o servidor
// embutido servir normalmente.
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
