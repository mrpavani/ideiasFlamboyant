<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Models\Order;
use Models\Product;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        $this->adminView('dashboard', [
            'title'    => 'Painel — Idéias Flamboyant',
            'stats'    => Order::dashboardStats(),
            'recent'   => Order::recent(8),
            'lowStock' => Product::lowStock(),
        ]);
    }
}
