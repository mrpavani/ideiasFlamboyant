<?php

declare(strict_types=1);

namespace Models;

use Core\Database;

/**
 * Controle de estoque + histórico de movimentações.
 * Toda alteração de saldo deve passar por aqui.
 */
final class Stock
{
    /**
     * Aplica uma movimentação e atualiza o saldo do produto.
     *
     * @param string $type  entrada|saida|ajuste
     * @param int    $qty    quantidade absoluta (>0). Para "ajuste", é o novo saldo.
     */
    public static function move(
        int $productId,
        string $type,
        int $qty,
        ?string $reason = null,
        ?string $reference = null,
        ?int $userId = null
    ): int {
        $product = Product::find($productId);
        if (!$product) {
            throw new \RuntimeException("Produto {$productId} não encontrado.");
        }

        $current = (int) $product['stock_quantity'];

        $newBalance = match ($type) {
            'entrada' => $current + abs($qty),
            'saida'   => max(0, $current - abs($qty)),
            'ajuste'  => max(0, $qty),
            default   => throw new \InvalidArgumentException("Tipo de movimentação inválido: {$type}"),
        };

        $movedQty = $type === 'ajuste' ? abs($newBalance - $current) : abs($qty);

        Database::beginTransaction();
        try {
            Database::update('products', ['stock_quantity' => $newBalance], 'id = :id', ['id' => $productId]);
            $id = Database::insert('stock_movements', [
                'product_id'    => $productId,
                'type'          => $type,
                'quantity'      => $movedQty,
                'balance_after' => $newBalance,
                'reason'        => $reason,
                'reference'     => $reference,
                'user_id'       => $userId,
            ]);
            Database::commit();
            return $id;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Baixa o estoque referente a um pedido pago (idempotente via flag stock_committed).
     */
    public static function commitOrder(int $orderId): void
    {
        $order = Database::fetch('SELECT * FROM orders WHERE id = :id', ['id' => $orderId]);
        if (!$order || $order['stock_committed']) {
            return;
        }

        $items = Database::fetchAll('SELECT * FROM order_items WHERE order_id = :id', ['id' => $orderId]);
        foreach ($items as $item) {
            if (!$item['product_id']) {
                continue;
            }
            $product = Product::find((int) $item['product_id']);
            if (!$product || $product['is_made_to_order'] || !$product['track_stock']) {
                continue;
            }
            self::move(
                (int) $item['product_id'],
                'saida',
                (int) $item['quantity'],
                'Venda',
                'pedido ' . $order['reference'],
            );
        }

        Database::update('orders', ['stock_committed' => 1], 'id = :id', ['id' => $orderId]);
    }

    /**
     * Devolve o estoque de um pedido cancelado que já havia baixado.
     */
    public static function releaseOrder(int $orderId): void
    {
        $order = Database::fetch('SELECT * FROM orders WHERE id = :id', ['id' => $orderId]);
        if (!$order || !$order['stock_committed']) {
            return;
        }

        foreach (Database::fetchAll('SELECT * FROM order_items WHERE order_id = :id', ['id' => $orderId]) as $item) {
            if (!$item['product_id']) {
                continue;
            }
            $product = Product::find((int) $item['product_id']);
            if (!$product || $product['is_made_to_order'] || !$product['track_stock']) {
                continue;
            }
            self::move(
                (int) $item['product_id'],
                'entrada',
                (int) $item['quantity'],
                'Devolução por cancelamento',
                'pedido ' . $order['reference'],
            );
        }

        Database::update('orders', ['stock_committed' => 0], 'id = :id', ['id' => $orderId]);
    }

    public static function movements(?int $productId = null, int $limit = 100): array
    {
        $where = $productId ? 'WHERE m.product_id = :pid' : '';
        $params = $productId ? ['pid' => $productId] : [];
        return Database::fetchAll(
            "SELECT m.*, p.name AS product_name, p.sku, u.name AS user_name
               FROM stock_movements m
               JOIN products p ON p.id = m.product_id
          LEFT JOIN users u ON u.id = m.user_id
              $where
           ORDER BY m.created_at DESC, m.id DESC
              LIMIT $limit",
            $params
        );
    }
}
