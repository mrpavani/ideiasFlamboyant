<?php

declare(strict_types=1);

namespace Services;

/**
 * Contrato comum a todos os gateways de pagamento.
 * Facilita adicionar novos provedores no futuro sem tocar no checkout.
 */
interface PaymentGateway
{
    /** Identificador interno: "mercadopago", "infinitepay"... */
    public function key(): string;

    /** Nome amigável exibido ao cliente. */
    public function label(): string;

    /** O gateway está ativo e configurado? */
    public function isEnabled(): bool;

    /**
     * Cria a cobrança/preferência e devolve os dados de redirecionamento.
     *
     * @param array $order  pedido já persistido (com "items")
     * @return array{checkout_url: string, gateway_id: ?string, raw: array}
     */
    public function createCheckout(array $order): array;

    /**
     * Interpreta a notificação (webhook) recebida do gateway.
     *
     * @return array{order_ref: ?string, gateway_id: ?string, status: string, raw: array}
     *         status normalizado: aprovado | pendente | recusado | estornado | cancelado | desconhecido
     */
    public function parseWebhook(array $request, string $rawBody): array;
}
