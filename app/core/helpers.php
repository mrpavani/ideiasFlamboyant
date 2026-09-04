<?php

declare(strict_types=1);

use Core\Database;

/**
 * Funções utilitárias globais.
 */

function config(?string $key = null): mixed
{
    static $config;
    if ($config === null) {
        $config = require APP_PATH . '/config/config.php';
    }
    if ($key === null) {
        return $config;
    }
    $value = $config;
    foreach (explode('.', $key) as $segment) {
        $value = $value[$segment] ?? null;
    }
    return $value;
}

/** URL absoluta a partir da raiz da aplicação. */
function url(string $path = ''): string
{
    return config('app.url') . '/' . ltrim($path, '/');
}

/** URL de asset estático. */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** URL de imagem enviada (upload). */
function upload_url(string $path): string
{
    return url('uploads/' . ltrim($path, '/'));
}

/** Escapa para saída HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Formata valor monetário em BRL. */
function money(float|int|string|null $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

/** Gera slug amigável. */
function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item-' . substr(md5($text . microtime()), 0, 6);
}

/** Redireciona e encerra. */
function redirect(string $path): never
{
    $location = str_starts_with($path, 'http') ? $path : url($path);
    header('Location: ' . $location);
    exit;
}

/** Retorna JSON e encerra. */
function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

/** Valor antigo de formulário (após validação com erro). */
function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

/** Acesso a configurações da loja (tabela settings) com cache em request. */
function setting(string $key, mixed $default = null): mixed
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (Database::fetchAll('SELECT `key`, `value` FROM settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    $value = $cache[$key] ?? $default;
    return $value === '' && $default !== null ? $default : $value;
}

/** Monta link do WhatsApp com mensagem pré-preenchida. */
function whatsapp_link(?string $message = null): string
{
    $number = preg_replace('/\D+/', '', (string) setting('whatsapp_number', ''));
    $text = $message ?? (string) setting('whatsapp_message', '');
    return 'https://wa.me/' . $number . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/** Gera referência curta de pedido: IF-000123. */
function order_reference(int $id): string
{
    return 'IF-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

/** IP do cliente. */
function client_ip(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Registra ação no log de auditoria. */
function activity(string $action, ?string $entity = null, ?int $entityId = null, array $meta = []): void
{
    try {
        Database::insert('activity_log', [
            'user_id'   => $_SESSION['admin']['id'] ?? null,
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId,
            'meta'      => $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
            'ip'        => client_ip(),
        ]);
    } catch (\Throwable) {
        // log de auditoria nunca deve quebrar o fluxo
    }
}
