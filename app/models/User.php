<?php

declare(strict_types=1);

namespace Models;

use Core\Database;

final class User
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT id, name, email, role, is_active, last_login_at, created_at FROM users ORDER BY name');
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    }

    public static function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = ['email' => $email];
        if ($ignoreId) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }
        return Database::column($sql, $params) !== null;
    }

    public static function create(string $name, string $email, string $password, string $role = 'admin'): int
    {
        return Database::insert('users', [
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'role'          => $role,
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $allowed = array_intersect_key($data, array_flip(['name', 'email', 'role', 'is_active']));
        if (!empty($data['password'])) {
            $allowed['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        if ($allowed) {
            Database::update('users', $allowed, 'id = :id', ['id' => $id]);
        }
    }

    public static function delete(int $id): void
    {
        Database::delete('users', 'id = :id', ['id' => $id]);
    }
}
