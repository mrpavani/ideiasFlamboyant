<?php

declare(strict_types=1);

namespace Models;

use Core\Database;

final class ApiToken
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM api_tokens ORDER BY created_at DESC');
    }

    /**
     * Gera um token novo. Retorna o valor em claro UMA única vez.
     * @return array{id:int, token:string}
     */
    public static function generate(string $name, string $scopes = 'read'): array
    {
        $plain = 'ifk_' . bin2hex(random_bytes(24));
        $id = Database::insert('api_tokens', [
            'name'          => $name,
            'token_hash'    => hash('sha256', $plain),
            'token_preview' => substr($plain, 0, 12),
            'scopes'        => $scopes,
        ]);
        return ['id' => $id, 'token' => $plain];
    }

    public static function resolve(string $plain): ?array
    {
        $token = Database::fetch(
            'SELECT * FROM api_tokens WHERE token_hash = :h AND is_active = 1',
            ['h' => hash('sha256', $plain)]
        );
        if ($token) {
            Database::update('api_tokens', ['last_used_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $token['id']]);
        }
        return $token;
    }

    public static function revoke(int $id): void
    {
        Database::update('api_tokens', ['is_active' => 0], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::delete('api_tokens', 'id = :id', ['id' => $id]);
    }
}
