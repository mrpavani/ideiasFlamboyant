<?php

declare(strict_types=1);

use Core\Router;
use Controllers\HomeController;
use Controllers\ShopController;
use Controllers\CartController;
use Controllers\CheckoutController;
use Controllers\WebhookController;
use Controllers\ApiController;
use Controllers\Admin\AuthController;
use Controllers\Admin\DashboardController;
use Controllers\Admin\ProductController;
use Controllers\Admin\CategoryController;
use Controllers\Admin\OrderController;
use Controllers\Admin\StockController;
use Controllers\Admin\SettingController;
use Controllers\Admin\UserController;

/**
 * Tabela de rotas da aplicação.
 */
return static function (Router $r): void {

    // ---------------- Loja (público) ----------------
    $r->get('/',                        [HomeController::class, 'index']);
    $r->get('/produtos',                [ShopController::class, 'index']);
    $r->get('/categoria/{slug}',        [ShopController::class, 'category']);
    $r->get('/produto/{slug}',          [ShopController::class, 'show']);
    $r->get('/busca',                   [ShopController::class, 'search']);
    $r->get('/sobre',                   [HomeController::class, 'about']);
    $r->get('/contato',                 [HomeController::class, 'contact']);

    // Carrinho
    $r->get('/carrinho',                [CartController::class, 'index']);
    $r->post('/carrinho/adicionar',     [CartController::class, 'add']);
    $r->post('/carrinho/atualizar',     [CartController::class, 'update']);
    $r->post('/carrinho/remover',       [CartController::class, 'remove']);
    $r->get('/carrinho/mini',           [CartController::class, 'mini']); // JSON p/ o header

    // Checkout
    $r->get('/checkout',               [CheckoutController::class, 'index']);
    $r->post('/checkout',              [CheckoutController::class, 'process']);
    $r->get('/pedido/{reference}',     [CheckoutController::class, 'confirmation']);

    // Webhooks de pagamento
    $r->post('/webhook/mercadopago',   [WebhookController::class, 'mercadopago']);
    $r->get('/webhook/mercadopago',    [WebhookController::class, 'mercadopago']);
    $r->post('/webhook/infinitepay',   [WebhookController::class, 'infinitepay']);
    $r->get('/webhook/infinitepay',    [WebhookController::class, 'infinitepay']);

    // ---------------- API v1 (integração ERP) ----------------
    $r->get('/api/v1/ping',                    [ApiController::class, 'ping']);
    $r->get('/api/v1/products',                [ApiController::class, 'products']);
    $r->get('/api/v1/products/{id}',           [ApiController::class, 'product']);
    $r->patch('/api/v1/products/{id}/stock',   [ApiController::class, 'updateStock']);
    $r->get('/api/v1/orders',                  [ApiController::class, 'orders']);
    $r->get('/api/v1/orders/{id}',             [ApiController::class, 'order']);
    $r->post('/api/v1/orders/{id}/status',     [ApiController::class, 'updateOrderStatus']);
    $r->get('/api/v1/stock/movements',         [ApiController::class, 'stockMovements']);

    // ---------------- Painel administrativo ----------------
    $r->get('/admin',                  [DashboardController::class, 'index']);
    $r->get('/admin/login',            [AuthController::class, 'showLogin']);
    $r->post('/admin/login',           [AuthController::class, 'login']);
    $r->post('/admin/logout',          [AuthController::class, 'logout']);

    // Produtos
    $r->get('/admin/produtos',              [ProductController::class, 'index']);
    $r->get('/admin/produtos/novo',         [ProductController::class, 'create']);
    $r->post('/admin/produtos',             [ProductController::class, 'store']);
    $r->get('/admin/produtos/{id}/editar',  [ProductController::class, 'edit']);
    $r->post('/admin/produtos/{id}',        [ProductController::class, 'update']);
    $r->post('/admin/produtos/{id}/excluir',[ProductController::class, 'destroy']);
    $r->post('/admin/produtos/{id}/imagens',        [ProductController::class, 'uploadImage']);
    $r->post('/admin/produtos/imagens/{imageId}/excluir', [ProductController::class, 'deleteImage']);
    $r->post('/admin/produtos/imagens/{imageId}/principal', [ProductController::class, 'setPrimaryImage']);

    // Categorias
    $r->get('/admin/categorias',              [CategoryController::class, 'index']);
    $r->post('/admin/categorias',             [CategoryController::class, 'store']);
    $r->post('/admin/categorias/{id}',        [CategoryController::class, 'update']);
    $r->post('/admin/categorias/{id}/excluir',[CategoryController::class, 'destroy']);

    // Pedidos
    $r->get('/admin/pedidos',            [OrderController::class, 'index']);
    $r->get('/admin/pedidos/{id}',       [OrderController::class, 'show']);
    $r->post('/admin/pedidos/{id}/status',   [OrderController::class, 'updateStatus']);
    $r->post('/admin/pedidos/{id}/pago',     [OrderController::class, 'markPaid']);

    // Estoque
    $r->get('/admin/estoque',           [StockController::class, 'index']);
    $r->post('/admin/estoque/movimentar', [StockController::class, 'move']);

    // Configurações
    $r->get('/admin/configuracoes',           [SettingController::class, 'index']);
    $r->post('/admin/configuracoes',          [SettingController::class, 'update']);
    $r->post('/admin/configuracoes/api-token', [SettingController::class, 'createApiToken']);
    $r->post('/admin/configuracoes/api-token/{id}/revogar', [SettingController::class, 'revokeApiToken']);

    // Usuários
    $r->get('/admin/usuarios',              [UserController::class, 'index']);
    $r->post('/admin/usuarios',             [UserController::class, 'store']);
    $r->post('/admin/usuarios/{id}',        [UserController::class, 'update']);
    $r->post('/admin/usuarios/{id}/excluir',[UserController::class, 'destroy']);
};
