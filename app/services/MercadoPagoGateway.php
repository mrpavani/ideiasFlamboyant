<?php

declare(strict_types=1);

namespace Services;

use Models\Order;
use Models\Setting;

/**
 * Integração com o Mercado Pago via API de Preferências (Checkout Pro).
 * Documentação: https://www.mercadopago.com.br/developers
 */
final class MercadoPagoGateway implements PaymentGateway
{
    private const API = 'https://api.mercadopago.com';

    public function key(): string
    {
        return 'mercadopago';
    }

    public function label(): string
    {
        return 'Mercado Pago (cartão, Pix, boleto)';
    }

    public function isEnabled(): bool
    {
        return Setting::get('mp_enabled') === '1' && trim((string) Setting::get('mp_access_token')) !== '';
    }

    private function token(): string
    {
        return trim((string) Setting::get('mp_access_token'));
    }

    public function createCheckout(array $order): array
    {
        $items = [];
        foreach ($order['items'] as $item) {
            $items[] = [
                'title'       => $item['product_name'],
                'quantity'    => (int) $item['quantity'],
                'unit_price'  => round((float) $item['unit_price'], 2),
                'currency_id' => 'BRL',
            ];
        }
        if ((float) $order['shipping_cost'] > 0) {
            $items[] = [
                'title'       => 'Frete — ' . ($order['shipping_method'] ?: 'Envio'),
                'quantity'    => 1,
                'unit_price'  => round((float) $order['shipping_cost'], 2),
                'currency_id' => 'BRL',
            ];
        }

        $payload = [
            'items'              => $items,
            'external_reference' => $order['reference'],
            'payer'              => [
                'name'  => $order['customer_name'],
                'email' => $order['customer_email'] ?: 'cliente@ideiasflamboyant.com.br',
            ],
            'back_urls' => [
                'success' => url('pedido/' . $order['reference'] . '?pg=sucesso'),
                'pending' => url('pedido/' . $order['reference'] . '?pg=pendente'),
                'failure' => url('pedido/' . $order['reference'] . '?pg=falha'),
            ],
            'auto_return'        => 'approved',
            'notification_url'   => url('webhook/mercadopago'),
            'statement_descriptor' => 'IDEIASFLAMBOYANT',
        ];

        $res = Http::post(self::API . '/checkout/preferences', [
            'headers' => ['Authorization: Bearer ' . $this->token()],
            'json'    => $payload,
        ]);

        if ($res['status'] >= 300 || !is_array($res['body'])) {
            throw new \RuntimeException('Mercado Pago: falha ao criar preferência — ' . $res['raw']);
        }

        $sandbox = Setting::get('mp_sandbox') === '1';
        $checkoutUrl = $sandbox
            ? ($res['body']['sandbox_init_point'] ?? $res['body']['init_point'] ?? '')
            : ($res['body']['init_point'] ?? '');

        return [
            'checkout_url' => $checkoutUrl,
            'gateway_id'   => $res['body']['id'] ?? null,
            'raw'          => $res['body'],
        ];
    }

    public function parseWebhook(array $request, string $rawBody): array
    {
        // MP envia ?type=payment&data.id=XXX (ou topic=payment&id=XXX)
        $type = $request['type'] ?? $request['topic'] ?? '';
        $paymentId = $request['data']['id'] ?? $request['data.id'] ?? $request['id'] ?? null;

        $result = [
            'order_ref'  => null,
            'gateway_id' => $paymentId,
            'status'     => 'desconhecido',
            'raw'        => $request,
        ];

        if (!in_array($type, ['payment', 'merchant_order'], true) || !$paymentId) {
            return $result;
        }

        $endpoint = $type === 'merchant_order'
            ? self::API . '/merchant_orders/' . $paymentId
            : self::API . '/v1/payments/' . $paymentId;

        $res = Http::get($endpoint, [
            'headers' => ['Authorization: Bearer ' . $this->token()],
        ]);

        if ($res['status'] >= 300 || !is_array($res['body'])) {
            return $result;
        }

        $data = $res['body'];
        $result['raw'] = $data;
        $result['order_ref'] = $data['external_reference']
            ?? ($data['order']['external_reference'] ?? null);

        $mpStatus = $data['status'] ?? ($data['payments'][0]['status'] ?? '');
        $result['status'] = match ($mpStatus) {
            'approved', 'closed'         => 'aprovado',
            'pending', 'in_process', 'authorized' => 'pendente',
            'rejected', 'cancelled'      => 'recusado',
            'refunded', 'charged_back'   => 'estornado',
            default                      => 'desconhecido',
        };

        return $result;
    }
}
