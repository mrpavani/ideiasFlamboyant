<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Controller;
use Core\Auth;
use Core\Csrf;
use Core\Flash;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('admin');
        }
        $this->view('admin/login', [
            'title' => 'Entrar — Painel Idéias Flamboyant',
        ], 'layouts/blank');
    }

    public function login(): void
    {
        Csrf::check();
        $email = strtolower(trim((string) $this->input('email', '')));
        $password = (string) $this->input('password', '');

        if (Auth::attempt($email, $password)) {
            Flash::success('Bem-vindo(a) de volta!');
            redirect('admin');
        }

        Flash::error('E-mail ou senha inválidos.');
        Flash::withInput(['email' => $email]);
        redirect('admin/login');
    }

    public function logout(): void
    {
        Csrf::check();
        Auth::logout();
        Flash::success('Sessão encerrada.');
        redirect('admin/login');
    }
}
