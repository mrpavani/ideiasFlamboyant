<?php
use Core\View;
use Core\Csrf;
/** @var array $product @var array $related */
$images = $product['images'] ?? [];
$primary = $images[0]['path'] ?? null;
$tracks = $product['track_stock'] && !$product['is_made_to_order'];
$stock = (int) $product['stock_quantity'];
$out = $tracks && $stock <= 0;
$low = $tracks && $stock > 0 && $stock <= (int) $product['low_stock_alert'];
$onSale = !empty($product['compare_at_price']) && (float) $product['compare_at_price'] > (float) $product['price'];
$hasPrice = (float) $product['price'] > 0;
?>
<div class="wrap product-page">
  <div class="gallery">
    <div class="main">
      <?php if ($primary): ?>
        <img id="mainImg" src="<?= upload_url($primary) ?>" alt="<?= e($product['name']) ?>">
      <?php else: ?>
        <div class="placeholder" style="height:100%;display:grid;place-items:center;color:#e0cdbd;font-family:var(--display)">Sem imagem</div>
      <?php endif; ?>
    </div>
    <?php if (count($images) > 1): ?>
      <div class="thumbs">
        <?php foreach ($images as $i => $img): ?>
          <img src="<?= upload_url($img['path']) ?>" alt="" class="<?= $i === 0 ? 'active' : '' ?>"
               onclick="document.getElementById('mainImg').src=this.src;document.querySelectorAll('.thumbs img').forEach(t=>t.classList.remove('active'));this.classList.add('active')">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="product-info">
    <?php if (!empty($product['category_name'])): ?>
      <a class="cat" href="<?= url('categoria/' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
    <?php endif; ?>
    <h1><?= e($product['name']) ?></h1>
    <?php if ($product['short_description']): ?><p class="muted"><?= e($product['short_description']) ?></p><?php endif; ?>

    <div class="price-row">
      <?php if ($hasPrice): ?>
        <span class="now"><?= money($product['price']) ?></span>
        <?php if ($onSale): ?><span class="was"><?= money($product['compare_at_price']) ?></span><?php endif; ?>
      <?php else: ?>
        <span class="now">Orçamento sob consulta</span>
      <?php endif; ?>
    </div>

    <?php if ($product['is_made_to_order']): ?>
      <p class="stock-line low">✦ Produzido sob encomenda</p>
    <?php elseif ($out): ?>
      <p class="stock-line out">Esgotado no momento</p>
    <?php elseif ($low): ?>
      <p class="stock-line low">Últimas <?= $stock ?> unidades!</p>
    <?php elseif ($tracks): ?>
      <p class="stock-line in">Em estoque — pronto para produção/envio</p>
    <?php endif; ?>

    <div class="buy-box">
      <?php if ($out || !$hasPrice): ?>
        <p style="margin-top:0" class="muted"><?= $out ? 'Podemos produzir sob encomenda.' : 'Este item é orçado individualmente.' ?> Fale com a gente:</p>
        <a class="btn wa lg block" target="_blank" rel="noopener"
           href="<?= e(whatsapp_link('Olá! Tenho interesse no produto: ' . $product['name'] . ' (' . url('produto/' . $product['slug']) . ')')) ?>">
          Pedir pelo WhatsApp
        </a>
      <?php else: ?>
        <form class="js-buy" method="post" action="<?= url('carrinho/adicionar') ?>">
          <?= Csrf::field() ?>
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px">
            <div class="qty">
              <button type="button" data-step="-1">−</button>
              <input type="text" name="qty" value="1" inputmode="numeric" data-max="<?= $tracks ? $stock : 99 ?>">
              <button type="button" data-step="1">+</button>
            </div>
            <button class="btn lg" type="submit">Adicionar ao carrinho</button>
          </div>
        </form>
        <a class="btn ghost block" style="margin-top:12px" href="<?= url('carrinho') ?>">Ir para o carrinho</a>
      <?php endif; ?>
      <p class="muted" style="font-size:13px;margin:14px 0 0">Dúvidas? <a href="<?= e(whatsapp_link('Olá! Tenho uma dúvida sobre: ' . $product['name'])) ?>" target="_blank" rel="noopener" style="color:var(--flame-dark);font-weight:600">Chame no WhatsApp</a></p>
    </div>

    <?php if ($product['description']): ?>
      <div class="rich"><?= nl2br(e($product['description'])) ?></div>
    <?php endif; ?>

    <table class="spec-table">
      <tbody>
        <?php if ($product['sku']): ?><tr><th>Código</th><td><?= e($product['sku']) ?></td></tr><?php endif; ?>
        <tr><th>Material</th><td><?= e($product['material'] ?: 'PLA') ?></td></tr>
        <?php if ($product['color']): ?><tr><th>Cor</th><td><?= e($product['color']) ?></td></tr><?php endif; ?>
        <?php if ($product['dimensions']): ?><tr><th>Dimensões</th><td><?= e($product['dimensions']) ?></td></tr><?php endif; ?>
        <?php if ($product['weight_grams']): ?><tr><th>Peso</th><td><?= (int) $product['weight_grams'] ?> g</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($related): ?>
<section class="section alt">
  <div class="wrap">
    <div class="section-head"><div><span class="kicker">Você também pode gostar</span><h2>Produtos relacionados</h2></div></div>
    <div class="product-grid">
      <?php foreach ($related as $p) { echo View::partial('partials/product-card', ['p' => $p]); } ?>
    </div>
  </div>
</section>
<?php endif; ?>
