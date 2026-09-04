<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Auth;
use Core\Csrf;
use Core\Flash;
use Models\Product;
use Models\Stock;

final class StockController extends AdminController
{
    public function index(): void
    {
        $this->adminView('stock/index', [
            'title'     => 'Estoque — Painel',
            'products'  => Product::adminList(['status' => 'ativos']),
            'lowStock'  => Product::lowStock(),
            'movements' => Stock::movements(null, 60),
        ]);
    }

    public function move(): void
    {
        Csrf::check();
        $productId = (int) $this->input('product_id', 0);
        $type = (string) $this->input('type', 'entrada');
        $quantity = (int) $this->input('quantity', 0);
        $reason = (string) $this->input('reason', '');

        if (!Product::find($productId)) {
            Flash::error('Produto inválido.');
            redirect('admin/estoque');
        }
        if ($quantity < 0 || ($type !== 'ajuste' && $quantity === 0)) {
            Flash::error('Informe uma quantidade válida.');
            redirect('admin/estoque');
        }

        try {
            Stock::move($productId, $type, $quantity, $reason ?: null, 'admin', Auth::id());
            activity('estoque.movimentado', 'product', $productId, ['type' => $type, 'qty' => $quantity]);
            Flash::success('Movimentação registrada.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
        redirect('admin/estoque');
    }
}
