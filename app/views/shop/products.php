<?php
use Core\View;
/** @var array $result @var array $categories @var array $filters @var string $heading @var ?array $category */
$items = $result['items'];
$qs = static function (array $extra) use ($filters) {
    return http_build_query(array_filter(array_merge([
        'q' => $filters['search'] ?? null,
        'ordem' => $filters['sort'] ?? null,
    ], $extra)));
};
?>
<div class="wrap shop-layout">
  <aside class="filters">
    <div>
      <h4>Categorias</h4>
      <ul>
        <li><a href="<?= url('produtos') ?>" class="<?= empty($category) ? 'active' : '' ?>">Todos os produtos</a></li>
        <?php foreach ($categories as $c): ?>
          <li><a href="<?= url('categoria/' . $c['slug']) ?>" class="<?= (!empty($category) && $category['id'] == $c['id']) ? 'active' : '' ?>">
            <?= e($c['name']) ?> <span class="muted">(<?= (int) $c['product_count'] ?>)</span>
          </a></li>
        <?php endforeach; ?>
      </ul>
      <h4>Precisa de algo sob medida?</h4>
      <a class="btn ghost sm block" href="<?= e(whatsapp_link('Olá! Quero um projeto personalizado em 3D.')) ?>" target="_blank" rel="noopener">Pedir pelo WhatsApp</a>
    </div>
  </aside>

  <div>
    <div class="section-head" style="margin-bottom:8px">
      <div><span class="kicker">Catálogo</span><h2><?= e($heading) ?></h2></div>
    </div>
    <div class="toolbar">
      <span class="muted"><?= (int) $result['total'] ?> produto<?= $result['total'] == 1 ? '' : 's' ?></span>
      <form method="get" onchange="this.submit()">
        <?php if (!empty($filters['search'])): ?><input type="hidden" name="q" value="<?= e($filters['search']) ?>"><?php endif; ?>
        <select name="ordem" aria-label="Ordenar">
          <option value="">Mais relevantes</option>
          <option value="nome" <?= ($filters['sort'] ?? '') === 'nome' ? 'selected' : '' ?>>Nome (A–Z)</option>
          <option value="preco_asc" <?= ($filters['sort'] ?? '') === 'preco_asc' ? 'selected' : '' ?>>Menor preço</option>
          <option value="preco_desc" <?= ($filters['sort'] ?? '') === 'preco_desc' ? 'selected' : '' ?>>Maior preço</option>
        </select>
      </form>
    </div>

    <?php if (!$items): ?>
      <div class="empty">
        <h3>Nenhum produto encontrado</h3>
        <p class="muted">Tente outra categoria ou fale com a gente para uma peça personalizada.</p>
        <a class="btn" href="<?= url('produtos') ?>">Ver todos os produtos</a>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($items as $p) { echo View::partial('partials/product-card', ['p' => $p]); } ?>
      </div>

      <?php if ($result['pages'] > 1): ?>
      <nav class="pagination">
        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
          <?php if ($i === $result['page']): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="?<?= $qs(['pagina' => $i]) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
