<?php

declare(strict_types=1);

namespace Models;

use Core\Database;

final class Category
{
    public static function all(bool $onlyActive = false): array
    {
        $where = $onlyActive ? 'WHERE is_active = 1' : '';
        return Database::fetchAll("SELECT * FROM categories $where ORDER BY position, name");
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE id = :id', ['id' => $id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE slug = :slug AND is_active = 1', ['slug' => $slug]);
    }

    /** Categorias ativas que possuem ao menos um produto ativo. */
    public static function withProducts(): array
    {
        return Database::fetchAll(
            'SELECT c.*, COUNT(p.id) AS product_count
               FROM categories c
               JOIN products p ON p.category_id = c.id AND p.is_active = 1
              WHERE c.is_active = 1
              GROUP BY c.id
              ORDER BY c.position, c.name'
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('categories', self::sanitize($data));
    }

    public static function update(int $id, array $data): void
    {
        Database::update('categories', self::sanitize($data), 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::delete('categories', 'id = :id', ['id' => $id]);
    }

    private static function sanitize(array $data): array
    {
        $allowed = ['name', 'slug', 'description', 'parent_id', 'position', 'is_active'];
        $out = array_intersect_key($data, array_flip($allowed));
        if (isset($out['slug']) && $out['slug'] === '') {
            $out['slug'] = slugify($out['name'] ?? 'categoria');
        }
        if (array_key_exists('parent_id', $out)) {
            $out['parent_id'] = $out['parent_id'] ?: null;
        }
        return $out;
    }
}
