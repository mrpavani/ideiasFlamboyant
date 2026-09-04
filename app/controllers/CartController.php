<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Csrf;
use Core\Flash;
use Models\Cart;
use Models\Product;

final class CartController extends Controller
{
    public function index(): void
    {
        $this->view('shop/cart', [
            'title'  => 'Meu carrinho — ' . setting('store_name', 'Idéias Flamboyant'),
            'totals' => Cart::totals(),
        ]);
    }

    public function add(): void
    {
        Csrf::check();
        $id = (int) $this->input('product_id', 0);
        $qty = max(1, (int) $this->input('qty', 1));

        $product = Product::find($id);
        if (!$product || !$product['is_active']) {
            $this->respond(false, 'Produto indisponível.');
            return;
        }
        if (!Product::inStock($product, $qty)) {
            $this->respond(false, 'Estoque insuficiente para este produto.');
            return;
        }

        Cart::add($id, $qty);
        $this->respond(true, 'Produto adicionado ao carrinho.');
    }

    public function update(): void
    {
        Csrf::check();
        $items = $_POST['qty'] ?? [];
        if (is_array($items)) {
            foreach ($items as $id => $qty) {
                Cart::set((int) $id, (int) $qty);
            }
        }
        Flash::success('Carrinho atualizado.');
        redirect('carrinho');
    }

    public function remove(): void
    {
        Csrf::check();
        Cart::remove((int) $this->input('product_id', 0));
        $this->respond(true, 'Item removido.');
    }

    public function mini(): void
    {
        $totals = Cart::totals();
        $this->json([
            'count'    => $totals['count'],
            'subtotal' => money($totals['subtotal']),
            'items'    => array_map(static fn ($i) => [
                'id'    => $i['id'],
                'name'  => $i['name'],
                'qty'   => $i['qty'],
                'price' => money($i['price']),
                'image' => $i['image'] ? upload_url($i['image']) : null,
                'url'   => url('produto/' . $i['slug']),
            ], $totals['items']),
        ]);
    }

    /** Resposta que serve tanto para fetch (JSON) quanto para POST comum. */
    private function respond(bool $ok, string $message): void
    {
        $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

        if ($wantsJson) {
            $this->json(['ok' => $ok, 'message' => $message, 'count' => Cart::count()], $ok ? 200 : 422);
        }

        $ok ? Flash::success($message) : Flash::error($message);
        redirect($_SERVER['HTTP_REFERER'] ?? url('carrinho'));
    }
}
