<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Auth;
use Core\Csrf;
use Core\Flash;
use Models\User;

final class UserController extends AdminController
{
    public function index(): void
    {
        $this->adminView('users/index', [
            'title' => 'Usuários — Painel',
            'users' => User::all(),
        ]);
    }

    public function store(): void
    {
        Csrf::check();
        $name = (string) $this->input('name', '');
        $email = strtolower(trim((string) $this->input('email', '')));
        $password = (string) $this->input('password', '');
        $role = $this->input('role', 'admin') === 'manager' ? 'manager' : 'admin';

        [$valid, $errors] = $this->validate(
            ['name' => $name, 'email' => $email, 'password' => $password],
            ['name' => 'required|min:3', 'email' => 'required|email', 'password' => 'required|min:6']
        );
        if (!$valid) {
            Flash::error(implode(' ', $errors));
            redirect('admin/usuarios');
        }
        if (User::emailExists($email)) {
            Flash::error('Já existe um usuário com este e-mail.');
            redirect('admin/usuarios');
        }

        $id = User::create($name, $email, $password, $role);
        activity('usuario.criado', 'user', $id);
        Flash::success('Usuário criado.');
        redirect('admin/usuarios');
    }

    public function update(string $id): void
    {
        Csrf::check();
        $user = User::find((int) $id);
        if (!$user) {
            Flash::error('Usuário não encontrado.');
            redirect('admin/usuarios');
        }

        $email = strtolower(trim((string) $this->input('email', $user['email'])));
        if ($email !== $user['email'] && User::emailExists($email, (int) $id)) {
            Flash::error('E-mail já em uso.');
            redirect('admin/usuarios');
        }

        User::update((int) $id, [
            'name'      => $this->input('name', $user['name']),
            'email'     => $email,
            'role'      => $this->input('role', $user['role']) === 'manager' ? 'manager' : 'admin',
            'is_active' => $this->boolInput('is_active'),
            'password'  => $this->input('password', ''),
        ]);
        Flash::success('Usuário atualizado.');
        redirect('admin/usuarios');
    }

    public function destroy(string $id): void
    {
        Csrf::check();
        if ((int) $id === Auth::id()) {
            Flash::error('Você não pode excluir o próprio usuário.');
            redirect('admin/usuarios');
        }
        User::delete((int) $id);
        activity('usuario.excluido', 'user', (int) $id);
        Flash::success('Usuário excluído.');
        redirect('admin/usuarios');
    }
}
