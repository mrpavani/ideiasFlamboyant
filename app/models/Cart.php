<?php

declare(strict_types=1);

namespace Models;

/**
 * Carrinho de compras persistido na sessão.
 * Estrutura: $_SESSION['cart'] = [ productId => qty ]
 */
final class Cart
{
    private const KEY = 'cart';

    public static function add(int $productId, int $qty = 1): void
    {
        $qty = max(1, $qty);
        $_SESSION[self::KEY][$productId] = ($_SESSION[self::KEY][$productId] ?? 0) + $qty;
        self::normalize($productId);
    }

    public static function set(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            self::remove($productId);
            return;
        }
        $_SESSION[self::KEY][$productId] = $qty;
        self::normalize($productId);
    }

    public static function remove(int $productId): void
    {
        unset($_SESSION[self::KEY][$productId]);
    }

    public static function clear(): void
    {
        unset($_SESSION[self::KEY]);
    }

    public static function count(): int
    {
        return array_sum($_SESSION[self::KEY] ?? []);
    }

    public static function isEmpty(): bool
    {
        return empty($_SESSION[self::KEY]);
    }

    /**
     * Limita a quantidade ao estoque disponível de produtos rastreados.
     */
    private static function normalize(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product['is_active']) {
            self::remove($productId);
            return;
        }
        if (!$product['is_made_to_order'] && $product['track_stock']) {
            $max = (int) $product['stock_quantity'];
            if ($max <= 0) {
                self::remove($productId);
            } elseif ($_SESSION[self::KEY][$productId] > $max) {
                $_SESSION[self::KEY][$productId] = $max;
            }
        }
    }

    /**
     * Retorna as linhas do carrinho com dados atuais do produto.
     * @return array<int, array{id:int,name:string,slug:string,sku:?string,price:float,qty:int,line_total:float,image:?string,max:int,made_to_order:bool}>
     */
    public static function items(): array
    {
        $lines = [];
        foreach ($_SESSION[self::KEY] ?? [] as $id => $qty) {
            $product = Product::find((int) $id);
            if (!$product || !$product['is_active']) {
                continue;
            }
            $price = (float) $product['price'];
            $lines[] = [
                'id'            => (int) $product['id'],
                'name'          => $product['name'],
                'slug'          => $product['slug'],
                'sku'           => $product['sku'],
                'price'         => $price,
                'qty'           => (int) $qty,
                'line_total'    => $price * (int) $qty,
                'image'         => Product::primaryImage((int) $product['id']),
                'max'           => $product['is_made_to_order'] || !$product['track_stock']
                                    ? 999 : (int) $product['stock_quantity'],
                'made_to_order' => (bool) $product['is_made_to_order'],
            ];
        }
        return $lines;
    }

    /**
     * Calcula subtotal, frete e total conforme as regras da loja.
     */
    public static function totals(): array
    {
        $items = self::items();
        $subtotal = array_sum(array_column($items, 'line_total'));

        $flat = (float) setting('shipping_flat_rate', 0);
        $freeAbove = (float) setting('shipping_free_above', 0);

        $shipping = 0.0;
        $shippingLabel = 'A combinar pelo WhatsApp';
        if ($subtotal > 0 && $flat > 0) {
            $shipping = ($freeAbove > 0 && $subtotal >= $freeAbove) ? 0.0 : $flat;
            $shippingLabel = $shipping === 0.0 ? 'Frete grátis' : 'Frete padrão';
        }

        $total = $subtotal + $shipping;

        return [
            'items'          => $items,
            'count'          => self::count(),
            'subtotal'       => round($subtotal, 2),
            'shipping'       => round($shipping, 2),
            'shipping_label' => $shippingLabel,
            'discount'       => 0.0,
            'total'          => round($total, 2),
        ];
    }
}
