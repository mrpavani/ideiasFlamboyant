<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Controller;
use Core\Auth;

/**
 * Base dos controladores do painel: exige login e usa o layout do admin.
 */
abstract class AdminController extends Controller
{
    public function __construct()
    {
        Auth::require();
    }

    protected function adminView(string $template, array $data = []): void
    {
        $data['authUser'] = Auth::user();
        $this->view('admin/' . $template, $data, 'layouts/admin');
    }
}
