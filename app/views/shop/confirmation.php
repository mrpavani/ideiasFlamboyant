<?php
/** @var array $order @var string $whatsappUrl @var string $paymentState */
$paid = $order['payment_status'] === 'aprovado';
$stateMsg = match ($paymentState) {
    'sucesso' => ['ok', 'Pagamento confirmado! Já estamos preparando seu pedido.'],
    'pendente' => ['', 'Pagamento em processamento. Avisaremos assim que for aprovado.'],
    'falha' => ['err', 'O pagamento não foi concluído. Você pode tentar novamente ou falar conosco.'],
    default => null,
};
?>
<div class="wrap confirm-page">
  <div class="confirm-hero">
    <div class="check"><svg viewBox="0 0 24 24" width="40" height="40"><path fill="currentColor" d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg></div>
    <h1>Pedido <?= e($order['reference']) ?> recebido!</h1>
    <p class="muted">Obrigado, <?= e($order['customer_name']) ?>. Guardamos os detalhes abaixo.</p>
    <p><span class="status-pill"><?= str_replace('_', ' ', e($order['status'])) ?></span></p>
  </div>

  <?php if ($stateMsg): ?>
    <div class="alert <?= $stateMsg[0] ?: 'ok' ?>" style="max-width:640px;margin:0 auto 20px"><?= e($stateMsg[1]) ?></div>
  <?php endif; ?>

  <div class="order-box">
    <?php foreach ($order['items'] as $it): ?>
      <div class="oi"><span><?= $it['quantity'] ?>× <?= e($it['product_name']) ?></span><span><?= money($it['line_total']) ?></span></div>
    <?php endforeach; ?>
    <div class="oi"><span>Frete</span><span><?= $order['shipping_cost'] > 0 ? money($order['shipping_cost']) : 'Grátis' ?></span></div>
    <div class="oi" style="font-weight:700;border-bottom:0"><span>Total</span><span><?= money($order['total']) ?></span></div>

    <p style="margin-top:18px" class="muted">
      Entrega em: <?= e(trim($order['shipping_street'] . ', ' . $order['shipping_number'] . ' — ' . $order['shipping_city'] . '/' . $order['shipping_state'], ' —/,')) ?>
    </p>

    <?php if (!$paid && $order['payment_link']): ?>
      <a class="btn block lg" href="<?= e($order['payment_link']) ?>" style="margin-top:12px">Pagar agora</a>
    <?php endif; ?>

    <a class="btn wa block lg" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener" style="margin-top:12px">
      Enviar pedido no WhatsApp
    </a>
    <a class="btn ghost block" href="<?= url('produtos') ?>" style="margin-top:10px">Continuar comprando</a>
  </div>

  <p class="muted" style="text-align:center;margin-top:24px;font-size:13px">
    Precisa de ajuda? Fale com a gente pelo WhatsApp informando o código <strong><?= e($order['reference']) ?></strong>.
  </p>
</div>
