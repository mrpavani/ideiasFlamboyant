<?php
/** @var string $content @var array $authUser */
use Core\Csrf;
$flash = \Core\Flash::all();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$is = static fn (string $p) => str_starts_with($path, $p) ? 'active' : '';
$nav = [
    ['/admin', 'Painel', 'M3 13h8V3H3zm0 8h8v-6H3zm10 0h8V11h-8zm0-18v6h8V3z'],
    ['/admin/pedidos', 'Pedidos', 'M19 3H5a2 2 0 0 0-2 2v14l4-4h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2'],
    ['/admin/produtos', 'Produtos', 'M12 2 2 7l10 5 10-5zm0 7L2 14l10 5 10-5z'],
    ['/admin/categorias', 'Categorias', 'M10 3H4v6h6zm10 0h-6v6h6zM10 13H4v8h6zm10 4h-6v4h6z'],
    ['/admin/estoque', 'Estoque', 'M20 6H4V4h16zm0 2H4v12h16zm-8 4h4v2h-4z'],
    ['/admin/configuracoes', 'Configurações', 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8m8.4 4-2 3.5.4 2.6-2.6 1.5-2.6-.4-2.6.4-2.6-1.5.4-2.6-2-3.5 2-3.5-.4-2.6 2.6-1.5 2.6.4 2.6-.4 2.6 1.5-.4 2.6z'],
    ['/admin/usuarios', 'Usuários', 'M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6m0 2c-2.7 0-8 1.3-8 4v3h10v-3c0-1 .4-2.5 2-3.6C10.6 13.2 9 13 8 13m8 0c-.3 0-.7 0-1.1.1C16.5 14.4 17 16 17 17v3h7v-3c0-2.7-5.3-4-8-4'],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Painel — Idéias Flamboyant') ?></title>
<link rel="icon" href="<?= asset('img/logo.png') ?>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=3">
</head>
<body>
<div class="admin-shell">
  <aside class="sidebar">
    <a class="side-brand" href="<?= url('admin') ?>">
      <img src="<?= asset('img/logo.png') ?>" alt="">
      <span>Idéias<strong>Flamboyant</strong></span>
    </a>
    <nav>
      <?php foreach ($nav as [$href, $label, $icon]): ?>
        <a href="<?= url(ltrim($href, '/')) ?>" class="<?= $href === '/admin' ? ($path === '/admin' ? 'active' : '') : $is($href) ?>">
          <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="<?= $icon ?>"/></svg>
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <a href="<?= url('/') ?>" target="_blank" rel="noopener">↗ Ver loja</a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-top">
      <button class="menu-toggle" onclick="document.body.classList.toggle('nav-open')" aria-label="Menu">☰</button>
      <h1><?= e($title ?? 'Painel') ?></h1>
      <div class="admin-user">
        <span><?= e($authUser['name'] ?? '') ?></span>
        <form method="post" action="<?= url('admin/logout') ?>">
          <?= Csrf::field() ?>
          <button class="link-btn" type="submit">Sair</button>
        </form>
      </div>
    </header>

    <?php if (!empty($flash['success'])): ?><div class="a-alert ok"><?= e($flash['success']) ?></div><?php endif; ?>
    <?php if (!empty($flash['error'])): ?><div class="a-alert err"><?= e($flash['error']) ?></div><?php endif; ?>

    <div class="admin-content">
      <?= $content ?>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('[data-confirm]').forEach(f=>f.addEventListener('submit',e=>{
  if(!confirm(f.dataset.confirm)) e.preventDefault();
}));
</script>
</body>
</html>
