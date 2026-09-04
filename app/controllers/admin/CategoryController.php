<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Csrf;
use Core\Database;
use Core\Flash;
use Models\Category;

final class CategoryController extends AdminController
{
    public function index(): void
    {
        $this->adminView('categories/index', [
            'title'      => 'Categorias — Painel',
            'categories' => Category::all(),
        ]);
    }

    public function store(): void
    {
        Csrf::check();
        $data = $this->data();
        [$valid, $errors] = $this->validate($data, ['name' => 'required|min:2|max:120']);
        if (!$valid) {
            Flash::error(implode(' ', $errors));
            redirect('admin/categorias');
        }
        Category::create($data);
        activity('categoria.criada', 'category');
        Flash::success('Categoria criada.');
        redirect('admin/categorias');
    }

    public function update(string $id): void
    {
        Csrf::check();
        $data = $this->data();
        [$valid, $errors] = $this->validate($data, ['name' => 'required|min:2|max:120']);
        if (!$valid) {
            Flash::error(implode(' ', $errors));
            redirect('admin/categorias');
        }
        Category::update((int) $id, $data);
        Flash::success('Categoria atualizada.');
        redirect('admin/categorias');
    }

    public function destroy(string $id): void
    {
        Csrf::check();
        $count = (int) Database::column(
            'SELECT COUNT(*) FROM products WHERE category_id = :id',
            ['id' => $id]
        );
        if ($count > 0) {
            Flash::error("Não é possível excluir: há {$count} produto(s) nesta categoria.");
            redirect('admin/categorias');
        }
        Category::delete((int) $id);
        Flash::success('Categoria excluída.');
        redirect('admin/categorias');
    }

    private function data(): array
    {
        return [
            'name'        => $this->input('name', ''),
            'slug'        => $this->input('slug', ''),
            'description' => $this->input('description', ''),
            'position'    => (int) $this->input('position', 0),
            'is_active'   => $this->boolInput('is_active'),
        ];
    }
}
