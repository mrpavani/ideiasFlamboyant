<?php
/**
 * Carrega variáveis de ambiente (.env) e expõe a configuração da aplicação.
 */

declare(strict_types=1);

defined('BASE_PATH')   || define('BASE_PATH', dirname(__DIR__, 2));
defined('APP_PATH')    || define('APP_PATH', BASE_PATH . '/app');
defined('PUBLIC_PATH') || define('PUBLIC_PATH', BASE_PATH . '/public');
defined('UPLOAD_PATH') || define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

/**
 * Leitor minimalista de .env (sem dependências).
 */
if (!function_exists('load_env')) {
function load_env(string $file): void
{
    if (!is_file($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $name = trim($name);
        $value = trim($value);
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'")) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}
}

if (!function_exists('env')) {
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true'  => true,
        'false' => false,
        'null'  => null,
        default => $value,
    };
}
}

load_env(BASE_PATH . '/.env');

return [
    'app' => [
        'name'  => env('APP_NAME', 'Idéias Flamboyant'),
        'env'   => env('APP_ENV', 'production'),
        'debug' => (bool) env('APP_DEBUG', false),
        'url'   => rtrim((string) env('APP_URL', 'http://localhost:8000'), '/'),
    ],
    'db' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => (int) env('DB_PORT', 3306),
        'name'    => env('DB_NAME', 'ideias_flamboyant'),
        'user'    => env('DB_USER', 'root'),
        'pass'    => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => env('SESSION_NAME', 'ideias_flamboyant_sess'),
    ],
];
