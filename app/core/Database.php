<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Conexão única (singleton) com o MySQL via PDO + helpers de consulta.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function init(array $config): void
    {
        if (self::$pdo instanceof PDO) {
            return;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Emulação ligada: permite reutilizar o mesmo parâmetro nomeado
                // (ex.: :q em vários LIKE). As consultas continuam usando
                // prepared statements com bind — sem concatenação de entrada.
                PDO::ATTR_EMULATE_PREPARES   => true,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            $hint = '';
            if (str_contains($e->getMessage(), '1045')) {
                $hint = "\n\nCausa provável: usuário/senha do MySQL incorretos, ou o arquivo .env não foi lido"
                      . " (senha chegou vazia = \"using password: NO\").\n"
                      . "Verifique se existe o arquivo .env na raiz do projeto e se DB_USER / DB_PASS estão corretos.";
            } elseif (str_contains($e->getMessage(), '2002') || str_contains($e->getMessage(), '2003')) {
                $hint = "\n\nCausa provável: o servidor MySQL não está rodando ou o host/porta estão errados.";
            } elseif (str_contains($e->getMessage(), 'Unknown database')) {
                $hint = "\n\nCausa provável: o banco \"{$config['name']}\" não existe. Importe database/schema.sql e database/seed.sql.";
            }
            exit(
                "Falha ao conectar ao banco de dados.\n"
                . "host={$config['host']}:{$config['port']}  db={$config['name']}  user={$config['user']}\n\n"
                . $e->getMessage() . $hint
            );
        }
    }

    public static function pdo(): PDO
    {
        if (!self::$pdo instanceof PDO) {
            throw new \RuntimeException('Database::init() não foi chamado.');
        }
        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function column(string $sql, array $params = []): mixed
    {
        $value = self::run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $placeholders = array_map(static fn ($c) => ':' . $c, $cols);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $cols),
            implode(', ', $placeholders)
        );
        self::run($sql, $data);
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(static fn ($c) => "`$c` = :$c", array_keys($data)));
        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, $set, $where);
        return self::run($sql, array_merge($data, $whereParams))->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        return self::run(sprintf('DELETE FROM `%s` WHERE %s', $table, $where), $params)->rowCount();
    }

    public static function beginTransaction(): void
    {
        self::pdo()->beginTransaction();
    }

    public static function commit(): void
    {
        self::pdo()->commit();
    }

    public static function rollBack(): void
    {
        if (self::pdo()->inTransaction()) {
            self::pdo()->rollBack();
        }
    }
}
