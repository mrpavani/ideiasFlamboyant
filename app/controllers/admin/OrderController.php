<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Csrf;
use Core\Flash;
use Models\Order;

final class OrderController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'status' => $this->input('status', ''),
            'search' => $this->input('q', ''),
        ];
        $this->adminView('orders/index', [
            'title'   => 'Pedidos — Painel',
            'orders'  => Order::adminList($filters),
            'filters' => $filters,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function show(string $id): void
    {
        $order = Order::withItems((int) $id);
        if (!$order) {
            Flash::error('Pedido não encontrado.');
            redirect('admin/pedidos');
        }

        $lines = ["Olá {$order['customer_name']}! Sobre o seu pedido {$order['reference']} na "
            . setting('store_name', 'Idéias Flamboyant') . ':'];
        $waCustomer = 'https://wa.me/' . preg_replace('/\D+/', '', $order['customer_phone'])
            . '?text=' . rawurlencode(implode("\n", $lines));

        $this->adminView('orders/show', [
            'title'      => 'Pedido ' . $order['reference'],
            'order'      => $order,
            'statuses'   => Order::STATUSES,
            'waCustomer' => $waCustomer,
        ]);
    }

    public function updateStatus(string $id): void
    {
        Csrf::check();
        $status = (string) $this->input('status', '');
        try {
            Order::setStatus((int) $id, $status, $this->input('note', '') ?: null);
            activity('pedido.status', 'order', (int) $id, ['status' => $status]);
            Flash::success('Status atualizado para "' . str_replace('_', ' ', $status) . '".');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
        redirect('admin/pedidos/' . $id);
    }

    public function markPaid(string $id): void
    {
        Csrf::check();
        Order::markPaid((int) $id, 'manual', null, ['by' => \Core\Auth::user()['email'] ?? 'admin']);
        Flash::success('Pedido marcado como pago. Estoque baixado.');
        redirect('admin/pedidos/' . $id);
    }
}
