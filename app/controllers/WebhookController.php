<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Order;
use Services\PaymentManager;

/**
 * Recebe notificações de pagamento dos gateways.
 * Sempre responde 200 rapidamente para o gateway não reenviar em loop;
 * o processamento é idempotente.
 */
final class WebhookController extends Controller
{
    public function mercadopago(): void
    {
        $this->handle('mercadopago');
    }

    public function infinitepay(): void
    {
        $this->handle('infinitepay');
    }

    private function handle(string $key): void
    {
        $gateway = PaymentManager::get($key);
        $rawBody = file_get_contents('php://input') ?: '';
        $request = array_merge($_GET, $_POST);

        $json = json_decode($rawBody, true);
        if (is_array($json)) {
            $request = array_merge($request, $json);
        }

        if (!$gateway) {
            $this->json(['ok' => false, 'error' => 'gateway desconhecido'], 404);
        }

        try {
            $result = $gateway->parseWebhook($request, $rawBody);
        } catch (\Throwable $e) {
            Order::recordEvent(null, $key, 'webhook.error', 'erro', ['message' => $e->getMessage(), 'req' => $request]);
            $this->json(['ok' => false], 200);
        }

        $order = null;
        if (!empty($result['order_ref'])) {
            $order = Order::findByReference($result['order_ref']);
        }

        Order::recordEvent(
            $order['id'] ?? null,
            $key,
            'webhook.received',
            $result['status'],
            $result['raw']
        );

        if ($order && $result['status'] === 'aprovado' && $order['payment_status'] !== 'aprovado') {
            Order::markPaid((int) $order['id'], $key, $result['gateway_id'], $result['raw']);
        } elseif ($order && in_array($result['status'], ['recusado', 'cancelado'], true)) {
            \Core\Database::update('orders', ['payment_status' => $result['status']], 'id = :id', ['id' => $order['id']]);
        } elseif ($order && $result['status'] === 'estornado') {
            \Core\Database::update('orders', ['payment_status' => 'estornado'], 'id = :id', ['id' => $order['id']]);
            \Models\Stock::releaseOrder((int) $order['id']);
        }

        // InfinitePay redireciona o navegador do cliente para cá (GET) — leve à confirmação.
        if ($key === 'infinitepay' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $order) {
            redirect('pedido/' . $order['reference'] . '?pg=' . ($result['status'] === 'aprovado' ? 'sucesso' : 'pendente'));
        }

        $this->json(['ok' => true], 200);
    }
}
