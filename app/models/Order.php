<?php

declare(strict_types=1);

namespace Models;

use Core\Database;

final class Order
{
    public const STATUSES = ['pendente', 'pago', 'em_producao', 'enviado', 'concluido', 'cancelado'];

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM orders WHERE id = :id', ['id' => $id]);
    }

    public static function findByReference(string $reference): ?array
    {
        return Database::fetch('SELECT * FROM orders WHERE reference = :r', ['r' => $reference]);
    }

    public static function items(int $orderId): array
    {
        return Database::fetchAll('SELECT * FROM order_items WHERE order_id = :id ORDER BY id', ['id' => $orderId]);
    }

    public static function withItems(int $id): ?array
    {
        $order = self::find($id);
        if ($order) {
            $order['items'] = self::items($id);
            $order['events'] = Database::fetchAll(
                'SELECT * FROM payment_events WHERE order_id = :id ORDER BY created_at DESC',
                ['id' => $id]
            );
        }
        return $order;
    }

    /**
     * Cria o pedido a partir do carrinho + dados do cliente.
     * @param array $cart itens no formato do CartController::items()
     */
    public static function createFromCart(array $cart, array $customer, array $totals): array
    {
        Database::beginTransaction();
        try {
            $id = Database::insert('orders', [
                'reference'         => 'TEMP',
                'status'            => 'pendente',
                'payment_status'    => 'pendente',
                'payment_method'    => $customer['payment_method'] ?? null,
                'customer_name'     => $customer['name'],
                'customer_email'    => $customer['email'] ?: null,
                'customer_phone'    => $customer['phone'],
                'customer_document' => $customer['document'] ?: null,
                'shipping_zip'        => $customer['zip'] ?: null,
                'shipping_street'     => $customer['street'] ?: null,
                'shipping_number'     => $customer['number'] ?: null,
                'shipping_complement' => $customer['complement'] ?: null,
                'shipping_district'   => $customer['district'] ?: null,
                'shipping_city'       => $customer['city'] ?: null,
                'shipping_state'      => $customer['state'] ?: null,
                'shipping_method'     => $totals['shipping_label'] ?? 'Envio padrão',
                'subtotal'          => $totals['subtotal'],
                'shipping_cost'     => $totals['shipping'],
                'discount'          => $totals['discount'] ?? 0,
                'total'             => $totals['total'],
                'notes'            => $customer['notes'] ?: null,
            ]);

            Database::update('orders', ['reference' => order_reference($id)], 'id = :id', ['id' => $id]);

            foreach ($cart as $line) {
                Database::insert('order_items', [
                    'order_id'     => $id,
                    'product_id'   => $line['id'],
                    'product_name' => $line['name'],
                    'sku'          => $line['sku'] ?? null,
                    'unit_price'   => $line['price'],
                    'quantity'     => $line['qty'],
                    'line_total'   => $line['price'] * $line['qty'],
                ]);
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }

        return self::withItems($id);
    }

    public static function setStatus(int $id, string $status, ?string $note = null): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Status inválido: {$status}");
        }
        $order = self::find($id);
        if (!$order) {
            return;
        }

        Database::update('orders', ['status' => $status], 'id = :id', ['id' => $id]);

        // Efeitos colaterais no estoque
        if ($status === 'cancelado') {
            Stock::releaseOrder($id);
        } elseif (in_array($status, ['pago', 'em_producao', 'enviado', 'concluido'], true)) {
            Stock::commitOrder($id);
        }

        if ($note !== null) {
            Database::insert('payment_events', [
                'order_id'   => $id,
                'gateway'    => 'manual',
                'event_type' => 'status_change',
                'status'     => $status,
                'payload'    => json_encode(['note' => $note], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public static function markPaid(int $id, string $gateway, ?string $gatewayId = null, array $payload = []): void
    {
        $order = self::find($id);
        if (!$order) {
            return;
        }

        Database::update('orders', [
            'payment_status'     => 'aprovado',
            'status'             => $order['status'] === 'pendente' ? 'pago' : $order['status'],
            'payment_gateway_id' => $gatewayId ?? $order['payment_gateway_id'],
            'paid_at'            => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);

        Database::insert('payment_events', [
            'order_id'   => $id,
            'gateway'    => $gateway,
            'event_type' => 'payment.approved',
            'status'     => 'aprovado',
            'payload'    => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        Stock::commitOrder($id);
        activity('pedido.pago', 'order', $id, ['gateway' => $gateway]);
    }

    public static function recordEvent(?int $orderId, string $gateway, string $type, ?string $status, array $payload): void
    {
        Database::insert('payment_events', [
            'order_id'   => $orderId,
            'gateway'    => $gateway,
            'event_type' => $type,
            'status'     => $status,
            'payload'    => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
    }

    // ---- Admin / API ----------------------------------------------------

    public static function adminList(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['since'])) {
            $where[] = 'created_at >= :since';
            $params['since'] = $filters['since'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(reference LIKE :q OR customer_name LIKE :q OR customer_phone LIKE :q)';
            $params['q'] = '%' . $filters['search'] . '%';
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $limit = (int) ($filters['limit'] ?? 200);

        return Database::fetchAll(
            "SELECT * FROM orders $whereSql ORDER BY created_at DESC LIMIT $limit",
            $params
        );
    }

    public static function dashboardStats(): array
    {
        return [
            'total_orders'   => (int) Database::column('SELECT COUNT(*) FROM orders'),
            'pending_orders' => (int) Database::column("SELECT COUNT(*) FROM orders WHERE status = 'pendente'"),
            'paid_today'     => (float) Database::column(
                "SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status = 'aprovado' AND DATE(paid_at) = CURDATE()"
            ),
            'revenue_month'  => (float) Database::column(
                "SELECT COALESCE(SUM(total),0) FROM orders
                  WHERE payment_status = 'aprovado'
                    AND YEAR(paid_at) = YEAR(CURDATE()) AND MONTH(paid_at) = MONTH(CURDATE())"
            ),
            'low_stock'      => (int) Database::column(
                'SELECT COUNT(*) FROM products
                  WHERE is_active = 1 AND track_stock = 1 AND is_made_to_order = 0
                    AND stock_quantity <= low_stock_alert'
            ),
            'active_products' => (int) Database::column('SELECT COUNT(*) FROM products WHERE is_active = 1'),
        ];
    }

    public static function recent(int $limit = 8): array
    {
        return Database::fetchAll("SELECT * FROM orders ORDER BY created_at DESC LIMIT $limit");
    }
}
