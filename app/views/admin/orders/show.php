<?php
use Core\Csrf;
/** @var array $order @var array $statuses @var string $waCustomer */
?>
<div class="panel-head">
  <h3>Pedido <?= e($order['reference']) ?> <span class="pill <?= e($order['status']) ?>" style="margin-left:8px"><?= str_replace('_', ' ', e($order['status'])) ?></span></h3>
  <a class="btn ghost sm" href="<?= url('admin/pedidos') ?>">← Voltar</a>
</div>

<div class="grid2" style="align-items:start">
  <div>
    <div class="panel">
      <h3>Itens</h3>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Produto</th><th>Qtd</th><th>Unit.</th><th>Total</th></tr></thead>
          <tbody>
          <?php foreach ($order['items'] as $it): ?>
            <tr><td><?= e($it['product_name']) ?><br><span class="muted" style="font-size:11px"><?= e($it['sku'] ?: '') ?></span></td><td><?= (int) $it['quantity'] ?></td><td><?= money($it['unit_price']) ?></td><td><?= money($it['line_total']) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="3" style="text-align:right">Subtotal</td><td><?= money($order['subtotal']) ?></td></tr>
            <tr><td colspan="3" style="text-align:right">Frete (<?= e($order['shipping_method']) ?>)</td><td><?= money($order['shipping_cost']) ?></td></tr>
            <?php if ((float) $order['discount'] > 0): ?><tr><td colspan="3" style="text-align:right">Desconto</td><td>- <?= money($order['discount']) ?></td></tr><?php endif; ?>
            <tr><td colspan="3" style="text-align:right;font-weight:700">Total</td><td style="font-weight:700"><?= money($order['total']) ?></td></tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="panel">
      <h3>Histórico de pagamento</h3>
      <?php if (!$order['events']): ?>
        <p class="muted">Sem eventos registrados.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data">
            <thead><tr><th>Data</th><th>Gateway</th><th>Evento</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($order['events'] as $ev): ?>
              <tr><td><?= date('d/m H:i', strtotime($ev['created_at'])) ?></td><td><?= e($ev['gateway']) ?></td><td><?= e($ev['event_type']) ?></td><td><?= e((string) $ev['status']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div>
    <div class="panel">
      <h3>Alterar status</h3>
      <form method="post" action="<?= url('admin/pedidos/' . $order['id'] . '/status') ?>">
        <?= Csrf::field() ?>
        <div class="field" style="margin-bottom:10px">
          <label>Status do pedido</label>
          <select name="status">
            <?php foreach ($statuses as $s): ?>
              <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field" style="margin-bottom:12px"><label>Observação (interna)</label><input name="note"></div>
        <button class="btn block" type="submit">Atualizar status</button>
      </form>
      <p class="hint" style="margin-top:8px">Marcar como pago/produção/enviado/concluído baixa o estoque automaticamente. Cancelar devolve ao estoque.</p>
      <?php if ($order['payment_status'] !== 'aprovado'): ?>
        <form method="post" action="<?= url('admin/pedidos/' . $order['id'] . '/pago') ?>" style="margin-top:10px" data-confirm="Confirmar recebimento do pagamento deste pedido?">
          <?= Csrf::field() ?>
          <button class="btn dark block" type="submit">✓ Registrar pagamento manual</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h3>Cliente</h3>
      <p style="margin:0 0 4px"><strong><?= e($order['customer_name']) ?></strong></p>
      <p class="muted" style="margin:0 0 4px"><?= e($order['customer_phone']) ?></p>
      <?php if ($order['customer_email']): ?><p class="muted" style="margin:0 0 4px"><?= e($order['customer_email']) ?></p><?php endif; ?>
      <?php if ($order['customer_document']): ?><p class="muted" style="margin:0 0 10px">Doc: <?= e($order['customer_document']) ?></p><?php endif; ?>
      <a class="btn wa sm block" style="background:#25D366" href="<?= e($waCustomer) ?>" target="_blank" rel="noopener">Falar com o cliente no WhatsApp</a>
    </div>

    <div class="panel">
      <h3>Entrega</h3>
      <p class="muted" style="margin:0">
        <?= e(trim(($order['shipping_street'] ?? '') . ', ' . ($order['shipping_number'] ?? ''), ', ')) ?><br>
        <?= e(trim(($order['shipping_complement'] ? $order['shipping_complement'] . ' — ' : '') . ($order['shipping_district'] ?? ''), ' —')) ?><br>
        <?= e(trim(($order['shipping_city'] ?? '') . '/' . ($order['shipping_state'] ?? ''), '/')) ?>
        <?= $order['shipping_zip'] ? ' — CEP ' . e($order['shipping_zip']) : '' ?>
      </p>
      <?php if ($order['notes']): ?><p style="margin-top:12px"><strong>Obs. do cliente:</strong><br><?= nl2br(e($order['notes'])) ?></p><?php endif; ?>
    </div>
  </div>
</div>
