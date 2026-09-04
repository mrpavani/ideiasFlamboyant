<?php

declare(strict_types=1);

namespace Services;

/**
 * Registro central de gateways. Ponto único para o checkout e os webhooks.
 * Para adicionar um novo provedor, basta implementar PaymentGateway e
 * registrá-lo em self::all().
 */
final class PaymentManager
{
    /** @return PaymentGateway[] */
    public static function all(): array
    {
        return [
            new MercadoPagoGateway(),
            new InfinitePayGateway(),
        ];
    }

    /** @return PaymentGateway[] apenas os habilitados no painel */
    public static function enabled(): array
    {
        return array_values(array_filter(self::all(), static fn (PaymentGateway $g) => $g->isEnabled()));
    }

    public static function get(string $key): ?PaymentGateway
    {
        foreach (self::all() as $gateway) {
            if ($gateway->key() === $key) {
                return $gateway;
            }
        }
        return null;
    }

    /** Existe ao menos um meio de pagamento online configurado? */
    public static function hasOnline(): bool
    {
        return self::enabled() !== [];
    }
}
