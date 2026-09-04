<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Csrf;
use Core\Database;
use Core\Flash;
use Core\Upload;
use Models\Category;
use Models\Product;
use Models\Stock;

final class ProductController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'search' => $this->input('q', ''),
            'status' => $this->input('status', ''),
        ];
        $this->adminView('products/index', [
            'title'    => 'Produtos — Painel',
            'products' => Product::adminList($filters),
            'filters'  => $filters,
        ]);
    }

    public function create(): void
    {
        $this->adminView('products/form', [
            'title'      => 'Novo produto — Painel',
            'product'    => null,
            'categories' => Category::all(),
        ]);
    }

    public function edit(string $id): void
    {
        $product = Product::find((int) $id);
        if (!$product) {
            Flash::error('Produto não encontrado.');
            redirect('admin/produtos');
        }
        $this->adminView('products/form', [
            'title'      => 'Editar: ' . $product['name'],
            'product'    => $product,
            'categories' => Category::all(),
            'images'     => Product::images((int) $id),
        ]);
    }

    public function store(): void
    {
        Csrf::check();
        $data = $this->payload();

        [$valid, $errors] = $this->validate($data, [
            'name'  => 'required|min:3|max:180',
            'price' => 'required|numeric',
        ]);
        if (!$valid) {
            Flash::error(implode(' ', $errors));
            Flash::withInput($_POST);
            redirect('admin/produtos/novo');
        }

        $initialStock = (int) $this->input('stock_quantity', 0);
        $data['stock_quantity'] = 0; // o saldo entra via movimentação
        $id = Product::create($data);

        if ($initialStock > 0) {
            Stock::move($id, 'entrada', $initialStock, 'Estoque inicial', 'cadastro', \Core\Auth::id());
        }

        $this->handleImageUploads($id);

        activity('produto.criado', 'product', $id);
        Flash::success('Produto cadastrado com sucesso.');
        redirect('admin/produtos/' . $id . '/editar');
    }

    public function update(string $id): void
    {
        Csrf::check();
        $product = Product::find((int) $id);
        if (!$product) {
            Flash::error('Produto não encontrado.');
            redirect('admin/produtos');
        }

        $data = $this->payload();
        [$valid, $errors] = $this->validate($data, [
            'name'  => 'required|min:3|max:180',
            'price' => 'required|numeric',
        ]);
        if (!$valid) {
            Flash::error(implode(' ', $errors));
            Flash::withInput($_POST);
            redirect('admin/produtos/' . $id . '/editar');
        }

        // Ajuste de estoque explícito (opcional)
        $newStock = $this->input('stock_quantity', null);
        unset($data['stock_quantity']);
        Product::update((int) $id, $data);

        if ($newStock !== null && $newStock !== '' && (int) $newStock !== (int) $product['stock_quantity']) {
            Stock::move((int) $id, 'ajuste', (int) $newStock, 'Ajuste manual (edição do produto)', 'admin', \Core\Auth::id());
        }

        $this->handleImageUploads((int) $id);

        activity('produto.atualizado', 'product', (int) $id);
        Flash::success('Produto atualizado.');
        redirect('admin/produtos/' . $id . '/editar');
    }

    public function destroy(string $id): void
    {
        Csrf::check();
        Product::delete((int) $id);
        activity('produto.excluido', 'product', (int) $id);
        Flash::success('Produto excluído.');
        redirect('admin/produtos');
    }

    // ---- Imagens ---------------------------------------------------

    public function uploadImage(string $id): void
    {
        Csrf::check();
        $this->handleImageUploads((int) $id);
        redirect('admin/produtos/' . $id . '/editar');
    }

    public function deleteImage(string $imageId): void
    {
        Csrf::check();
        $image = Database::fetch('SELECT * FROM product_images WHERE id = :id', ['id' => $imageId]);
        if ($image) {
            @unlink(UPLOAD_PATH . '/' . $image['path']);
            Database::delete('product_images', 'id = :id', ['id' => $imageId]);
            Flash::success('Imagem removida.');
            redirect('admin/produtos/' . $image['product_id'] . '/editar');
        }
        redirect('admin/produtos');
    }

    public function setPrimaryImage(string $imageId): void
    {
        Csrf::check();
        $image = Database::fetch('SELECT * FROM product_images WHERE id = :id', ['id' => $imageId]);
        if ($image) {
            Database::update('product_images', ['is_primary' => 0], 'product_id = :pid', ['pid' => $image['product_id']]);
            Database::update('product_images', ['is_primary' => 1], 'id = :id', ['id' => $imageId]);
            Flash::success('Imagem principal definida.');
            redirect('admin/produtos/' . $image['product_id'] . '/editar');
        }
        redirect('admin/produtos');
    }

    // ---- Helpers -------------------------------------------------

    /** Converte "79,90" -> "79.90" para validação e gravação consistentes. */
    private function decimal(string $field, string $default = ''): string
    {
        $raw = (string) $this->input($field, $default);
        return $raw === '' ? '' : str_replace(',', '.', $raw);
    }

    private function payload(): array
    {
        return [
            'category_id'      => $this->input('category_id', ''),
            'name'             => $this->input('name', ''),
            'slug'             => $this->input('slug', ''),
            'sku'              => $this->input('sku', ''),
            'short_description'=> $this->input('short_description', ''),
            'description'      => $_POST['description'] ?? '',
            'price'            => $this->decimal('price', '0'),
            'compare_at_price' => $this->decimal('compare_at_price'),
            'cost_price'       => $this->decimal('cost_price'),
            'material'         => $this->input('material', '') ?: 'PLA',
            'color'            => $this->input('color', ''),
            'weight_grams'     => $this->input('weight_grams', ''),
            'dimensions'       => $this->input('dimensions', ''),
            'is_made_to_order' => $this->boolInput('is_made_to_order'),
            'track_stock'      => $this->boolInput('track_stock'),
            'low_stock_alert'  => $this->input('low_stock_alert', '3'),
            'stock_quantity'   => $this->input('stock_quantity', '0'),
            'is_active'        => $this->boolInput('is_active'),
            'is_featured'      => $this->boolInput('is_featured'),
        ];
    }

    private function handleImageUploads(int $productId): void
    {
        if (empty($_FILES['images']['name'][0])) {
            return;
        }
        $files = $_FILES['images'];
        $hasPrimary = (int) Database::column(
            'SELECT COUNT(*) FROM product_images WHERE product_id = :id',
            ['id' => $productId]
        ) > 0;

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            try {
                $path = Upload::image([
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ], 'products');

                Database::insert('product_images', [
                    'product_id' => $productId,
                    'path'       => $path,
                    'position'   => $i,
                    'is_primary' => $hasPrimary ? 0 : 1,
                ]);
                $hasPrimary = true;
            } catch (\Throwable $e) {
                Flash::error('Imagem ' . ($i + 1) . ': ' . $e->getMessage());
            }
        }
    }
}
