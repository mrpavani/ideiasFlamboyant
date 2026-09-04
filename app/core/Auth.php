<?php

declare(strict_types=1);

namespace Core;

/**
 * Autenticação do painel administrativo (sessão).
 */
final class Auth
{
    private const KEY = 'admin';

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::fetch(
            'SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1',
            ['email' => $email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            Database::update('users', [
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            ], 'id = :id', ['id' => $user['id']]);
        }

        session_regenerate_id(true);
        $_SESSION[self::KEY] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
        activity('login', 'user', (int) $user['id']);

        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::KEY]['id']);
    }

    public static function user(): ?array
    {
        return $_SESSION[self::KEY] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION[self::KEY]['id'] ?? null;
    }

    public static function require(): void
    {
        if (!self::check()) {
            $_SESSION['_flash']['error'] = 'Faça login para acessar o painel.';
            redirect('admin/login');
        }
    }

    public static function logout(): void
    {
        activity('logout', 'user', self::id());
        unset($_SESSION[self::KEY]);
        session_regenerate_id(true);
    }
}
