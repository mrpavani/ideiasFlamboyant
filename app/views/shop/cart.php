<?php
use Core\Csrf;
/** @var array $totals */
$items = $totals['items'];
?>
<div class="wrap cart-page">
  <div class="section-head"><div><span class="kicker">Carrinho</span><h2>Meu carrinho</h2></div></div>

  <?php if (!$items): ?>
    <div class="empty">
      <svg viewBox="0 0 24 24"><path fill="currentColor" d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2M1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42a.25.25 0 0 1-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 20 4H5.21l-.94-2M17 18c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2"/></svg>
      <h3>Seu carrinho está vazio</h3>
      <p class="muted">Que tal dar uma olhada nos nossos produtos?</p>
      <a class="btn" href="<?= url('produtos') ?>">Ver produtos</a>
    </div>
  <?php else: ?>
    <form method="post" action="<?= url('carrinho/atualizar') ?>">
      <?= Csrf::field() ?>
      <div class="cart-grid">
        <div class="cart-items">
          <?php foreach ($items as $it): ?>
            <div class="cart-row">
              <?php if ($it['image']): ?>
                <img src="<?= upload_url($it['image']) ?>" alt="<?= e($it['name']) ?>">
              <?php else: ?>
                <div style="width:88px;height:88px;border-radius:10px;background:var(--cream)"></div>
              <?php endif; ?>
              <div>
                <a href="<?= url('produto/' . $it['slug']) ?>" style="font-weight:600"><?= e($it['name']) ?></a>
                <div class="muted" style="font-size:13px"><?= money($it['price']) ?> / un.<?= $it['made_to_order'] ? ' · sob encomenda' : '' ?></div>
                <button class="rm js-remove" type="button" data-id="<?= $it['id'] ?>">Remover</button>
              </div>
              <div class="qty">
                <button type="button" data-step="-1">−</button>
                <input type="text" name="qty[<?= $it['id'] ?>]" value="<?= $it['qty'] ?>" inputmode="numeric" data-max="<?= $it['max'] ?>">
                <button type="button" data-step="1">+</button>
              </div>
              <strong><?= money($it['line_total']) ?></strong>
            </div>
          <?php endforeach; ?>
        </div>

        <aside class="summary">
          <h3>Resumo</h3>
          <div class="line"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
          <div class="line"><span>Frete (<?= e($totals['shipping_label']) ?>)</span><span><?= $totals['shipping'] > 0 ? money($totals['shipping']) : ($totals['subtotal'] > 0 ? 'Grátis' : '—') ?></span></div>
          <div class="line total"><span>Total</span><span><?= money($totals['total']) ?></span></div>
          <button class="btn block" type="submit" style="margin-top:14px">Atualizar carrinho</button>
          <a class="btn block" href="<?= url('checkout') ?>" style="margin-top:10px">Finalizar compra</a>
          <a class="btn ghost block" href="<?= url('produtos') ?>" style="margin-top:10px">Continuar comprando</a>
          <p class="muted" style="font-size:12px;margin-top:14px"><?= e(setting('shipping_note', '')) ?></p>
        </aside>
      </div>
    </form>
  <?php endif; ?>
</div>
