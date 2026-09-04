<?php
/** @var string $content */
use Core\Csrf;
use Models\Category;
use Models\Cart;

$flash = \Core\Flash::all();
$navCategories = Category::withProducts();
$cartCount = Cart::count();
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? setting('store_name', 'Idéias Flamboyant')) ?></title>
<meta name="description" content="<?= e(setting('store_tagline', 'Produtos exclusivos impressos em 3D')) ?>">
<meta name="csrf-token" content="<?= Csrf::token() ?>">
<link rel="icon" href="<?= asset('img/logo.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>?v=4">
</head>
<body>

<div class="topbar">
  <div class="wrap">
    <span>Materiais terapêuticos, brinquedos e decoração • impressão 3D</span>
    <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">Fale conosco no WhatsApp</a>
  </div>
</div>

<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="<?= url('/') ?>">
      <img src="<?= asset('img/logo.png') ?>" alt="<?= e(setting('store_name', 'Idéias Flamboyant')) ?>">
      <span class="brand-text">
        <strong>Idéias</strong><em>Flamboyant</em>
      </span>
    </a>

    <form class="search" action="<?= url('busca') ?>" method="get" role="search">
      <input type="search" name="q" placeholder="Buscar produtos impressos em 3D..." value="<?= e($_GET['q'] ?? '') ?>" aria-label="Buscar">
      <button type="submit" aria-label="Buscar">
        <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 4.99L20.49 19zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14"/></svg>
      </button>
    </form>

    <nav class="header-actions">
      <a href="<?= url('produtos') ?>" class="nav-link">Produtos</a>
      <a href="<?= url('carrinho') ?>" class="cart-btn" data-cart-count>
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2M1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42a.25.25 0 0 1-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 20 4H5.21l-.94-2M17 18c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2"/></svg>
        <span class="badge<?= $cartCount ? '' : ' hidden' ?>" data-cart-badge><?= $cartCount ?></span>
      </a>
    </nav>
  </div>

  <nav class="catnav">
    <div class="wrap">
      <a href="<?= url('produtos') ?>">Todos</a>
      <?php foreach ($navCategories as $cat): ?>
        <a href="<?= url('categoria/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a>
      <?php endforeach; ?>
      <a href="<?= url('sobre') ?>">Sobre</a>
      <a href="<?= url('contato') ?>">Contato</a>
    </div>
  </nav>
</header>

<?php if (!empty($flash['success']) || !empty($flash['error'])): ?>
<div class="wrap">
  <?php if (!empty($flash['success'])): ?><div class="alert ok"><?= e($flash['success']) ?></div><?php endif; ?>
  <?php if (!empty($flash['error'])): ?><div class="alert err"><?= e($flash['error']) ?></div><?php endif; ?>
</div>
<?php endif; ?>

<main>
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap footer-grid">
    <div>
      <img src="<?= asset('img/logo.png') ?>" alt="" class="footer-logo">
      <p><?= e(setting('store_tagline', 'Produtos exclusivos impressos em 3D')) ?></p>
      <p class="muted"><?= e(setting('store_city', '')) ?></p>
    </div>
    <div>
      <h4>Loja</h4>
      <a href="<?= url('produtos') ?>">Todos os produtos</a>
      <?php foreach (array_slice($navCategories, 0, 4) as $cat): ?>
        <a href="<?= url('categoria/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div>
      <h4>Institucional</h4>
      <a href="<?= url('sobre') ?>">Sobre nós</a>
      <a href="<?= url('contato') ?>">Contato</a>
      <a href="<?= url('admin') ?>">Painel administrativo</a>
    </div>
    <div>
      <h4>Atendimento</h4>
      <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      <?php if (setting('instagram_url')): ?><a href="<?= e(setting('instagram_url')) ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
      <a href="mailto:<?= e(setting('store_email', '')) ?>"><?= e(setting('store_email', '')) ?></a>
    </div>
  </div>
  <div class="wrap copy">
    © <?= date('Y') ?> <?= e(setting('store_name', 'Idéias Flamboyant')) ?>. Todos os direitos reservados.
  </div>
</footer>

<a class="whatsapp-float" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" aria-label="Falar no WhatsApp">
  <svg viewBox="0 0 32 32" width="30" height="30"><path fill="currentColor" d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.7 1.9 6.7L3 29l7-1.8c1.9 1 4 1.6 6 1.6 7 0 12.5-5.5 12.5-12.5S23 3 16 3m0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-4.1 1.1 1.1-4-.3-.4a10.3 10.3 0 0 1-1.6-5.5c0-5.7 4.7-10.3 10.5-10.3 2.8 0 5.4 1.1 7.4 3s3 4.6 3 7.4c0 5.7-4.7 10.4-10.4 10.4m5.7-7.8c-.3-.2-1.8-.9-2.1-1s-.5-.2-.7.2-.8 1-.9 1.2-.3.2-.6.1a8.4 8.4 0 0 1-4.2-3.7c-.3-.5.3-.5.9-1.7l-.1-.6-1-2.3c-.2-.6-.4-.5-.6-.5h-.6c-.2 0-.5.1-.8.4a4 4 0 0 0-1.2 2.9c0 1.7 1.2 3.4 1.4 3.6s2.5 3.8 6 5.3c2.3 1 3.2 1.1 4.3.9.7-.1 1.8-.7 2.1-1.5.3-.7.3-1.4.2-1.5s-.2-.3-.5-.4"/></svg>
</a>

<script src="<?= asset('js/app.js') ?>?v=4"></script>
</body>
</html>
