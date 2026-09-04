<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Database;
use Models\ApiToken;
use Models\Order;
use Models\Product;
use Models\Stock;

/**
 * API REST v1 — preparada para integração com sistema de vendas / ERP.
 * Autenticação: header  Authorization: Bearer <token>
 * Tokens gerenciados em Admin → Configurações → API.
 */
final class ApiController extends Controller
{
    private ?array $token = null;

    // ---- Autenticação -------------------------------------------------

    private function auth(string $scope = 'read'): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
            $this->json(['error' => 'Token ausente. Use o header Authorization: Bearer <token>.'], 401);
        }

        $this->token = ApiToken::resolve($m[1]);
        if (!$this->token) {
            $this->json(['error' => 'Token inválido ou revogado.'], 401);
        }

        $scopes = explode(',', (string) $this->token['scopes']);
        if ($scope === 'write' && !in_array('write', $scopes, true)) {
            $this->json(['error' => 'Token sem permissão de escrita.'], 403);
        }
    }

    private function body(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        return is_array($json) ? $json : $_POST;
    }

    // ---- Endpoints --------------------------------------------------

    public function ping(): void
    {
        $this->auth();
        $this->json([
            'ok'      => true,
            'service' => 'Idéias Flamboyant API',
            'version' => 'v1',
            'time'    => date('c'),
            'token'   => $this->token['name'],
        ]);
    }

    public function products(): void
    {
        $this->auth();
        $rows = Database::fetchAll(
            'SELECT p.id, p.name, p.slug, p.sku, p.price, p.compare_at_price, p.cost_price,
                    p.stock_quantity, p.low_stock_alert, p.track_stock, p.is_made_to_order,
                    p.is_active, p.material, p.color, p.weight_grams, p.dimensions,
                    c.name AS category, p.updated_at
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
           ORDER BY p.id'
        );
        $this->json(['data' => $rows, 'count' => count($rows)]);
    }

    public function product(string $id): void
    {
        $this->auth();
        $product = Product::find((int) $id);
        if (!$product) {
            $this->json(['error' => 'Produto não encontrado.'], 404);
        }
        $product['images'] = array_map(
            static fn ($i) => upload_url($i['path']),
            Product::images((int) $id)
        );
        $this->json(['data' => $product]);
    }

    public function updateStock(string $id): void
    {
        $this->auth('write');
        $product = Product::find((int) $id);
        if (!$product) {
            $this->json(['error' => 'Produto não encontrado.'], 404);
        }

        $body = $this->body();
        $type = $body['type'] ?? 'ajuste'; // entrada|saida|ajuste
        $quantity = (int) ($body['quantity'] ?? 0);
        $reason = $body['reason'] ?? 'Ajuste via API';

        if (!in_array($type, ['entrada', 'saida', 'ajuste'], true)) {
            $this->json(['error' => 'type deve ser entrada, saida ou ajuste.'], 422);
        }

        Stock::move((int) $id, $type, $quantity, $reason, 'API:' . $this->token['name']);
        $fresh = Product::find((int) $id);

        $this->json([
            'data' => [
                'id'             => (int) $id,
                'stock_quantity' => (int) $fresh['stock_quantity'],
                'type'           => $type,
                'quantity'       => $quantity,
            ],
        ]);
    }

    public function orders(): void
    {
        $this->auth();
        $rows = Order::adminList([
            'status' => $_GET['status'] ?? null,
            'since'  => $_GET['since'] ?? null,
            'limit'  => min(500, (int) ($_GET['limit'] ?? 100)),
        ]);
        $this->json(['data' => $rows, 'count' => count($rows)]);
    }

    public function order(string $id): void
    {
        $this->auth();
        $order = Order::withItems((int) $id);
        if (!$order) {
            $this->json(['error' => 'Pedido não encontrado.'], 404);
        }
        $this->json(['data' => $order]);
    }

    public function updateOrderStatus(string $id): void
    {
        $this->auth('write');
        $order = Order::find((int) $id);
        if (!$order) {
            $this->json(['error' => 'Pedido não encontrado.'], 404);
        }

        $body = $this->body();
        $status = $body['status'] ?? '';
        if (!in_array($status, Order::STATUSES, true)) {
            $this->json(['error' => 'Status inválido.', 'allowed' => Order::STATUSES], 422);
        }

        Order::setStatus((int) $id, $status, $body['note'] ?? 'Atualizado via API');

        if (!empty($body['external_ref'])) {
            Database::update('orders', ['external_ref' => (string) $body['external_ref']], 'id = :id', ['id' => $id]);
        }

        $this->json(['data' => Order::withItems((int) $id)]);
    }

    public function stockMovements(): void
    {
        $this->auth();
        $productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : null;
        $this->json(['data' => Stock::movements($productId, min(500, (int) ($_GET['limit'] ?? 100)))]);
    }
}
