<?php

declare(strict_types=1);

namespace Models;

use Core\Database;

final class Setting
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Database::column('SELECT `value` FROM settings WHERE `key` = :k', ['k' => $key]);
        return $value === null ? $default : $value;
    }

    public static function all(?string $group = null): array
    {
        $sql = 'SELECT * FROM settings';
        $params = [];
        if ($group !== null) {
            $sql .= ' WHERE `group` = :g';
            $params['g'] = $group;
        }
        $sql .= ' ORDER BY `group`, `key`';
        return Database::fetchAll($sql, $params);
    }

    /** Retorna um mapa chave => valor. */
    public static function map(?string $group = null): array
    {
        $out = [];
        foreach (self::all($group) as $row) {
            $out[$row['key']] = $row['value'];
        }
        return $out;
    }

    public static function set(string $key, mixed $value, string $group = 'geral', bool $secret = false): void
    {
        $exists = Database::column('SELECT `key` FROM settings WHERE `key` = :k', ['k' => $key]);
        if ($exists !== null) {
            Database::update('settings', ['value' => (string) $value], '`key` = :k', ['k' => $key]);
        } else {
            Database::insert('settings', [
                'key'       => $key,
                'value'     => (string) $value,
                'group'     => $group,
                'is_secret' => $secret ? 1 : 0,
            ]);
        }
    }

    /**
     * Atualiza vários valores de uma vez.
     * Campos de segredo em branco NÃO sobrescrevem o valor já salvo.
     */
    public static function setMany(array $values, array $secretKeys = []): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, $secretKeys, true) && trim((string) $value) === '') {
                continue;
            }
            self::set($key, $value);
        }
    }
}
