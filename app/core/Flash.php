<?php

declare(strict_types=1);

namespace Core;

/**
 * Mensagens temporárias (sucesso/erro) entre requisições.
 */
final class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    /** Guarda os dados do formulário para repovoar após erro de validação. */
    public static function withInput(array $input): void
    {
        unset($input['_token'], $input['_method'], $input['password'], $input['password_confirmation']);
        $_SESSION['_old'] = $input;
    }

    public static function all(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }
}
