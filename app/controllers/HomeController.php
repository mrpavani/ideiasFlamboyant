<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Product;
use Models\Category;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('shop/home', [
            'title'      => setting('store_name', 'Idéias Flamboyant') . ' — Produtos impressos em 3D',
            'featured'   => Product::featured(6),
            'categories' => Category::withProducts(),
            'novidades'  => Product::paginate(['sort' => ''], 1, 8)['items'],
        ]);
    }

    public function about(): void
    {
        $this->view('shop/about', [
            'title' => 'Sobre — ' . setting('store_name', 'Idéias Flamboyant'),
        ]);
    }

    public function contact(): void
    {
        $this->view('shop/contact', [
            'title' => 'Contato — ' . setting('store_name', 'Idéias Flamboyant'),
        ]);
    }
}
