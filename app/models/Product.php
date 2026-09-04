<?php

declare(strict_types=1);

namespace Models;

use Core\Database;

final class Product
{
    private const FILLABLE = [
        'category_id', 'name', 'slug', 'sku', 'short_description', 'description',
        'price', 'compare_at_price', 'cost_price', 'material', 'color', 'weight_grams',
        'dimensions', 'print_time_hours', 'is_made_to_order', 'stock_quantity',
        'low_stock_alert', 'track_stock', 'is_active', 'is_featured',
    ];

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM products WHERE id = :id', ['id' => $id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        $product = Database::fetch(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
              WHERE p.slug = :slug AND p.is_active = 1',
            ['slug' => $slug]
        );
        if ($product) {
            $product['images'] = self::images((int) $product['id']);
        }
        return $product;
    }

    public static function images(int $productId): array
    {
        return Database::fetchAll(
            'SELECT * FROM product_images WHERE product_id = :id ORDER BY is_primary DESC, position, id',
            ['id' => $productId]
        );
    }

    public static function primaryImage(int $productId): ?string
    {
        $path = Database::column(
            'SELECT path FROM product_images WHERE product_id = :id ORDER BY is_primary DESC, position, id LIMIT 1',
            ['id' => $productId]
        );
        return $path ? (string) $path : null;
    }

    /**
     * Listagem paginada da vitrine com filtros.
     * @return array{items: array, total: int, pages: int, page: int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'c.slug = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :q OR p.short_description LIKE :q OR p.sku LIKE :q)';
            $params['q'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['featured'])) {
            $where[] = 'p.is_featured = 1';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $order = match ($filters['sort'] ?? '') {
            'preco_asc'  => 'p.price ASC',
            'preco_desc' => 'p.price DESC',
            'nome'       => 'p.name ASC',
            default      => 'p.is_featured DESC, p.created_at DESC',
        };

        $total = (int) Database::column(
            "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id $whereSql",
            $params
        );

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    (SELECT path FROM product_images pi
                      WHERE pi.product_id = p.id
                      ORDER BY pi.is_primary DESC, pi.position, pi.id LIMIT 1) AS image
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
              $whereSql
           ORDER BY $order
              LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'items' => $items,
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
            'page'  => $page,
        ];
    }

    public static function featured(int $limit = 6): array
    {
        return Database::fetchAll(
            "SELECT p.*, (SELECT path FROM product_images pi WHERE pi.product_id = p.id
                          ORDER BY pi.is_primary DESC, pi.position, pi.id LIMIT 1) AS image
               FROM products p
              WHERE p.is_active = 1 AND p.is_featured = 1
           ORDER BY p.updated_at DESC
              LIMIT $limit"
        );
    }

    public static function related(int $productId, ?int $categoryId, int $limit = 4): array
    {
        $params = ['id' => $productId];
        $categoryFilter = '';
        if ($categoryId !== null) {
            $categoryFilter = ' AND p.category_id = :cat';
            $params['cat'] = $categoryId;
        }

        return Database::fetchAll(
            "SELECT p.*, (SELECT path FROM product_images pi WHERE pi.product_id = p.id
                          ORDER BY pi.is_primary DESC, pi.position, pi.id LIMIT 1) AS image
               FROM products p
              WHERE p.is_active = 1 AND p.id <> :id{$categoryFilter}
           ORDER BY RAND() LIMIT $limit",
            $params
        );
    }

    // ---- Admin ---------------------------------------------------------

    public static function adminList(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (($filters['status'] ?? '') === 'ativos') {
            $where[] = 'p.is_active = 1';
        } elseif (($filters['status'] ?? '') === 'inativos') {
            $where[] = 'p.is_active = 0';
        } elseif (($filters['status'] ?? '') === 'baixo_estoque') {
            $where[] = 'p.track_stock = 1 AND p.is_made_to_order = 0 AND p.stock_quantity <= p.low_stock_alert';
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :q OR p.sku LIKE :q)';
            $params['q'] = '%' . $filters['search'] . '%';
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        return Database::fetchAll(
            "SELECT p.*, c.name AS category_name,
                    (SELECT path FROM product_images pi WHERE pi.product_id = p.id
                      ORDER BY pi.is_primary DESC, pi.position, pi.id LIMIT 1) AS image
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
              $whereSql
           ORDER BY p.created_at DESC",
            $params
        );
    }

    public static function create(array $data): int
    {
        $data = self::sanitize($data);
        $data['slug'] = self::uniqueSlug($data['slug'] ?: slugify($data['name']));
        return Database::insert('products', $data);
    }

    public static function update(int $id, array $data): void
    {
        $data = self::sanitize($data);
        if (($data['slug'] ?? '') !== '') {
            $data['slug'] = self::uniqueSlug($data['slug'], $id);
        }
        Database::update('products', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        foreach (self::images($id) as $img) {
            @unlink(UPLOAD_PATH . '/' . $img['path']);
        }
        Database::delete('products', 'id = :id', ['id' => $id]);
    }

    public static function incrementViews(int $id): void
    {
        Database::run('UPDATE products SET views = views + 1 WHERE id = :id', ['id' => $id]);
    }

    public static function lowStock(): array
    {
        return Database::fetchAll(
            'SELECT * FROM products
              WHERE is_active = 1 AND track_stock = 1 AND is_made_to_order = 0
                AND stock_quantity <= low_stock_alert
           ORDER BY stock_quantity ASC'
        );
    }

    public static function inStock(array $product, int $qty = 1): bool
    {
        if ($product['is_made_to_order'] || !$product['track_stock']) {
            return true;
        }
        return (int) $product['stock_quantity'] >= $qty;
    }

    // ---- Helpers -----------------------------------------------------

    private static function sanitize(array $data): array
    {
        $out = array_intersect_key($data, array_flip(self::FILLABLE));

        foreach (['price', 'compare_at_price', 'cost_price', 'print_time_hours'] as $f) {
            if (array_key_exists($f, $out)) {
                $out[$f] = $out[$f] === '' || $out[$f] === null
                    ? ($f === 'price' ? 0 : null)
                    : (float) str_replace(',', '.', (string) $out[$f]);
            }
        }
        foreach (['category_id', 'weight_grams', 'stock_quantity', 'low_stock_alert'] as $f) {
            if (array_key_exists($f, $out)) {
                $out[$f] = $out[$f] === '' || $out[$f] === null ? ($f === 'category_id' ? null : 0) : (int) $out[$f];
            }
        }
        foreach (['is_made_to_order', 'track_stock', 'is_active', 'is_featured'] as $f) {
            if (array_key_exists($f, $out)) {
                $out[$f] = (int) (bool) $out[$f];
            }
        }
        return $out;
    }

    private static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = slugify($slug);
        $slug = $base;
        $i = 2;
        while (true) {
            $exists = Database::column(
                'SELECT id FROM products WHERE slug = :slug' . ($ignoreId ? ' AND id <> :id' : ''),
                $ignoreId ? ['slug' => $slug, 'id' => $ignoreId] : ['slug' => $slug]
            );
            if (!$exists) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }
}
