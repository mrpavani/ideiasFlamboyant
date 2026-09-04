<?php
use Core\Csrf;
/** @var array $totals @var array $gateways @var bool $whatsappFallback */
$items = $totals['items'];
?>
<div class="wrap checkout-page">
  <div class="section-head"><div><span class="kicker">Checkout</span><h2>Finalizar compra</h2></div></div>

  <form method="post" action="<?= url('checkout') ?>">
    <?= Csrf::field() ?>
    <div class="cart-grid">
      <div>
        <div class="card-block">
          <h3>Seus dados</h3>
          <div class="form-grid">
            <div class="field"><label>Nome completo *</label><input name="name" value="<?= e(old('name')) ?>" required></div>
            <div class="field"><label>WhatsApp / telefone *</label><input name="phone" value="<?= e(old('phone')) ?>" placeholder="(62) 99999-9999" required></div>
            <div class="field"><label>E-mail</label><input type="email" name="email" value="<?= e(old('email')) ?>"></div>
            <div class="field"><label>CPF / CNPJ</label><input name="document" value="<?= e(old('document')) ?>"></div>
          </div>
        </div>

        <div class="card-block">
          <h3>Endereço de entrega</h3>
          <div class="form-grid">
            <div class="field"><label>CEP</label><input name="zip" value="<?= e(old('zip')) ?>"></div>
            <div class="field"><label>Cidade *</label><input name="city" value="<?= e(old('city')) ?>" required></div>
            <div class="field full"><label>Rua / logradouro *</label><input name="street" value="<?= e(old('street')) ?>" required></div>
            <div class="field"><label>Número *</label><input name="number" value="<?= e(old('number')) ?>" required></div>
            <div class="field"><label>Complemento</label><input name="complement" value="<?= e(old('complement')) ?>"></div>
            <div class="field"><label>Bairro</label><input name="district" value="<?= e(old('district')) ?>"></div>
            <div class="field"><label>Estado (UF) *</label><input name="state" maxlength="2" value="<?= e(old('state')) ?>" placeholder="GO" required></div>
            <div class="field full"><label>Observações do pedido</label><textarea name="notes" rows="2" placeholder="Cor desejada, texto para personalização, etc."><?= e(old('notes')) ?></textarea></div>
          </div>
        </div>

        <div class="card-block">
          <h3>Forma de pagamento</h3>
          <div class="pay-options">
            <?php foreach ($gateways as $g): ?>
              <label class="pay-option">
                <input type="radio" name="payment_method" value="<?= e($g->key()) ?>">
                <span><strong><?= e($g->label()) ?></strong><small>Você será redirecionado para concluir o pagamento com segurança.</small></span>
              </label>
            <?php endforeach; ?>
            <?php if ($whatsappFallback || !$gateways): ?>
              <label class="pay-option">
                <input type="radio" name="payment_method" value="whatsapp" <?= !$gateways ? 'checked' : '' ?>>
                <span><strong>Combinar pelo WhatsApp</strong><small>Fechamos o pedido e você paga via Pix ou link enviado no chat.</small></span>
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <aside class="summary">
        <h3>Seu pedido</h3>
        <?php foreach ($items as $it): ?>
          <div class="line"><span><?= $it['qty'] ?>× <?= e($it['name']) ?></span><span><?= money($it['line_total']) ?></span></div>
        <?php endforeach; ?>
        <div class="line"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
        <div class="line"><span>Frete</span><span><?= $totals['shipping'] > 0 ? money($totals['shipping']) : 'Grátis' ?></span></div>
        <div class="line total"><span>Total</span><span><?= money($totals['total']) ?></span></div>
        <button class="btn block lg" type="submit" style="margin-top:14px">Concluir pedido</button>
        <a class="btn ghost block" href="<?= url('carrinho') ?>" style="margin-top:10px">Voltar ao carrinho</a>
        <p class="muted" style="font-size:12px;margin-top:14px">Ao concluir, você concorda com nossas condições de produção e envio.</p>
      </aside>
    </div>
  </form>
</div>
