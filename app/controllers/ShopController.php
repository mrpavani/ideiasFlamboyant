<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Product;
use Models\Category;

final class ShopController extends Controller
{
    public function index(): void
    {
        $this->renderList([
            'search'   => $this->input('q', ''),
            'sort'     => $this->input('ordem', ''),
            'featured' => $this->input('destaque', ''),
        ], 'Todos os produtos');
    }

    public function category(string $slug): void
    {
        $category = Category::findBySlug($slug);
        if (!$category) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Categoria não encontrada']);
            return;
        }
        $this->renderList([
            'category' => $slug,
            'sort'     => $this->input('ordem', ''),
        ], $category['name'], $category);
    }

    public function search(): void
    {
        $this->renderList([
            'search' => $this->input('q', ''),
            'sort'   => $this->input('ordem', ''),
        ], 'Busca por "' . $this->input('q', '') . '"');
    }

    private function renderList(array $filters, string $heading, ?array $category = null): void
    {
        $page = max(1, (int) $this->input('pagina', 1));
        $result = Product::paginate(array_filter($filters), $page, 12);

        $this->view('shop/products', [
            'title'      => $heading . ' — ' . setting('store_name', 'Idéias Flamboyant'),
            'heading'    => $heading,
            'category'   => $category,
            'categories' => Category::withProducts(),
            'result'     => $result,
            'filters'    => $filters,
        ]);
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Produto não encontrado']);
            return;
        }

        Product::incrementViews((int) $product['id']);

        $this->view('shop/product', [
            'title'   => $product['name'] . ' — ' . setting('store_name', 'Idéias Flamboyant'),
            'product' => $product,
            'related' => Product::related((int) $product['id'], $product['category_id'] ? (int) $product['category_id'] : null),
        ]);
    }
}
