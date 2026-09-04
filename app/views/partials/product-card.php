<?php
/** @var array $p produto (com chave image opcional) */
$img = $p['image'] ?? null;
$madeToOrder = !empty($p['is_made_to_order']);
$tracks = !empty($p['track_stock']) && !$madeToOrder;
$stock = (int) ($p['stock_quantity'] ?? 0);
$out = $tracks && $stock <= 0;
$onSale = !empty($p['compare_at_price']) && (float) $p['compare_at_price'] > (float) $p['price'];
?>
<article class="card">
  <a class="thumb" href="<?= url('produto/' . $p['slug']) ?>">
    <?php if ($img): ?>
      <img src="<?= upload_url($img) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
    <?php else: ?>
      <div class="placeholder">Idéias Flamboyant</div>
    <?php endif; ?>
    <?php if ($madeToOrder): ?><span class="tag">Sob encomenda</span>
    <?php elseif ($out): ?><span class="tag out">Esgotado</span>
    <?php elseif (!empty($p['is_featured'])): ?><span class="tag">Destaque</span><?php endif; ?>
    <?php if ($onSale): ?><span class="tag sale">Oferta</span><?php endif; ?>
  </a>
  <div class="body">
    <?php if (!empty($p['category_name'])): ?><span class="cat"><?= e($p['category_name']) ?></span><?php endif; ?>
    <h3><a href="<?= url('produto/' . $p['slug']) ?>"><?= e($p['name']) ?></a></h3>
    <div class="price">
      <?php if ((float) $p['price'] > 0): ?>
        <span class="now"><?= money($p['price']) ?></span>
        <?php if ($onSale): ?><span class="was"><?= money($p['compare_at_price']) ?></span><?php endif; ?>
      <?php else: ?>
        <span class="now">Sob consulta</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="actions">
    <?php if ($out): ?>
      <a class="btn ghost sm block" href="<?= e(whatsapp_link('Olá! Quero saber sobre disponibilidade do produto: ' . $p['name'])) ?>" target="_blank" rel="noopener">Avise-me / encomendar</a>
    <?php elseif ((float) $p['price'] > 0): ?>
      <button class="btn sm block js-add-cart" data-id="<?= (int) $p['id'] ?>">Adicionar</button>
    <?php else: ?>
      <a class="btn sm block" href="<?= url('produto/' . $p['slug']) ?>">Ver detalhes</a>
    <?php endif; ?>
  </div>
</article>
