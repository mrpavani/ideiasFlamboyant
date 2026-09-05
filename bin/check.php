<?php

declare(strict_types=1);

/**
 * Diagnóstico do ambiente. Rode:  php bin/check.php
 */

$root = dirname(__DIR__);
$ok = true;
$line = static fn (string $s, bool $good) => printf("  [%s] %s\n", $good ? 'OK ' : 'ERR', $s);

echo "\n== Idéias Flamboyant — diagnóstico ==\n\n";

// PHP
$phpOk = PHP_VERSION_ID >= 80100;
$line('PHP ' . PHP_VERSION . ($phpOk ? '' : ' (requer 8.1+)'), $phpOk);
$ok = $ok && $phpOk;

// Extensões
foreach (['pdo_mysql', 'curl', 'mbstring', 'fileinfo', 'json'] as $ext) {
    $has = extension_loaded($ext);
    $line("Extensão {$ext}", $has);
    $ok = $ok && $has;
}

// .env
$envPath = $root . '/.env';
$hasEnv = is_file($envPath);
$line('.env encontrado (' . $envPath . ')', $hasEnv);
if (!$hasEnv) {
    echo "\n  -> Copie .env.example para .env e configure o MySQL.\n";
    exit(1);
}
$ok = $ok && $hasEnv;

// Carrega config
$config = require $root . '/app/config/config.php';
$db = $config['db'];
printf("      host=%s:%d  db=%s  user=%s  senha=%s\n",
    $db['host'], $db['port'], $db['name'], $db['user'],
    $db['pass'] === '' ? '(vazia)' : '(' . strlen((string) $db['pass']) . ' caracteres)');

// uploads graváveis
$upOk = is_writable($root . '/uploads');
$line('uploads/ gravável', $upOk);

// Conexão MySQL
try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $line('Conexão MySQL', true);

    $exists = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($db['name']))->fetch();
    $line("Banco \"{$db['name']}\" existe", (bool) $exists);
    if (!$exists) {
        echo "\n  -> Crie o banco e importe:\n";
        echo "     mysql -u {$db['user']} -p -e \"CREATE DATABASE {$db['name']} CHARACTER SET utf8mb4\"\n";
        echo "     mysql -u {$db['user']} -p {$db['name']} < database/schema.sql\n";
        echo "     mysql -u {$db['user']} -p {$db['name']} < database/seed.sql\n";
        exit(1);
    }

    $pdo->exec("USE `{$db['name']}`");
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $expected = ['users', 'settings', 'categories', 'products', 'product_images', 'orders',
                 'order_items', 'payment_events', 'stock_movements', 'api_tokens', 'activity_log'];
    $missing = array_diff($expected, $tables);
    $line(count($tables) . ' tabelas encontradas' . ($missing ? ' (faltam: ' . implode(', ', $missing) . ')' : ''), !$missing);
    if ($missing) {
        echo "\n  -> Importe database/schema.sql (e seed.sql).\n";
        exit(1);
    }

    $prod = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $admin = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $line("{$prod} produto(s), {$admin} usuário(s) admin", $admin > 0);
    if ($admin === 0) {
        echo "\n  -> Importe database/seed.sql para criar o admin.\n";
    }
} catch (Throwable $e) {
    $line('Conexão MySQL — ' . $e->getMessage(), false);
    if (str_contains($e->getMessage(), '1045')) {
        echo "\n  -> Usuário/senha do MySQL incorretos no .env (DB_USER / DB_PASS).\n";
    } elseif (str_contains($e->getMessage(), '2002') || str_contains($e->getMessage(), '2003')) {
        echo "\n  -> O MySQL não está rodando ou o host/porta no .env estão errados.\n";
    }
    exit(1);
}

echo "\n" . ($ok ? "Tudo certo. Rode:  php -S localhost:8000 server.php\n\n" : "Corrija os itens [ERR] acima.\n\n");
exit($ok ? 0 : 1);
