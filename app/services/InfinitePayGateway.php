<?php

declare(strict_types=1);

namespace Services;

use Models\Setting;

/**
 * Integração com o InfinitePay (Link de Pagamento / Checkout hospedado).
 *
 * Fluxo:
 *   1. Geramos a URL de checkout em checkout.infinitepay.io/{handle} com os itens.
 *   2. Cliente paga na página do InfinitePay.
 *   3. InfinitePay redireciona/notifica com order_nsu + transaction_nsu.
 *   4. Confirmamos o pagamento via endpoint payment_check.
 *
 * Confirme os nomes exatos dos campos no painel de desenvolvedor do InfinitePay
 * antes de ir para produção.
 */
final class InfinitePayGateway implements PaymentGateway
{
    private const CHECKOUT_BASE = 'https://checkout.infinitepay.io/';
    private const API = 'https://api.infinitepay.io';

    public function key(): string
    {
        return 'infinitepay';
    }

    public function label(): string
    {
        return 'InfinitePay (cartão e Pix)';
    }

    public function isEnabled(): bool
    {
        return Setting::get('infinitepay_enabled') === '1'
            && trim((string) Setting::get('infinitepay_handle')) !== '';
    }

    private function handle(): string
    {
        return ltrim(trim((string) Setting::get('infinitepay_handle')), '$@');
    }

    public function createCheckout(array $order): array
    {
        $items = [];
        foreach ($order['items'] as $item) {
            $items[] = [
                'name'     => $item['product_name'],
                'price'    => (int) round((float) $item['unit_price'] * 100), // centavos
                'quantity' => (int) $item['quantity'],
            ];
        }
        if ((float) $order['shipping_cost'] > 0) {
            $items[] = [
                'name'     => 'Frete',
                'price'    => (int) round((float) $order['shipping_cost'] * 100),
                'quantity' => 1,
            ];
        }

        $query = http_build_query([
            'items'        => json_encode($items, JSON_UNESCAPED_UNICODE),
            'order_nsu'    => $order['reference'],
            'redirect_url' => url('webhook/infinitepay'),
        ]);

        $checkoutUrl = self::CHECKOUT_BASE . rawurlencode($this->handle()) . '?' . $query;

        return [
            'checkout_url' => $checkoutUrl,
            'gateway_id'   => $order['reference'],
            'raw'          => ['items' => $items, 'checkout_url' => $checkoutUrl],
        ];
    }

    public function parseWebhook(array $request, string $rawBody): array
    {
        $orderNsu = $request['order_nsu'] ?? $request['order_id'] ?? null;
        $transactionNsu = $request['transaction_nsu'] ?? $request['nsu'] ?? null;
        $slug = $request['slug'] ?? null;

        $result = [
            'order_ref'  => $orderNsu,
            'gateway_id' => $transactionNsu,
            'status'     => 'desconhecido',
            'raw'        => $request,
        ];

        if (!$orderNsu || !$transactionNsu) {
            return $result;
        }

        // Confirmação server-to-server
        $res = Http::post(self::API . '/invoices/public/checkout/payment_check/' . rawurlencode($this->handle()), [
            'headers' => array_filter([
                trim((string) Setting::get('infinitepay_token')) !== ''
                    ? 'Authorization: Bearer ' . trim((string) Setting::get('infinitepay_token'))
                    : null,
            ]),
            'json' => [
                'handle'            => $this->handle(),
                'order_nsu'         => $orderNsu,
                'transaction_nsu'   => $transactionNsu,
                'external_order_id' => $orderNsu,
                'slug'              => $slug,
            ],
        ]);

        $result['raw'] = ['request' => $request, 'check' => $res['body']];

        if ($res['status'] < 300 && is_array($res['body'])) {
            $paid = ($res['body']['paid'] ?? false) === true
                || ($res['body']['success'] ?? false) === true
                || ($res['body']['status'] ?? '') === 'paid';
            $result['status'] = $paid ? 'aprovado' : 'pendente';
        }

        return $result;
    }
}
