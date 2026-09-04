<?php use Core\Csrf; /** @var array $products @var array $lowStock @var array $movements */ ?>
<div class="grid2" style="align-items:start">
  <div class="panel">
    <h3>Registrar movimentação</h3>
    <form method="post" action="<?= url('admin/estoque/movimentar') ?>">
      <?= Csrf::field() ?>
      <div class="field" style="margin-bottom:12px">
        <label>Produto</label>
        <select name="product_id" required>
          <option value="">— selecione —</option>
          <?php foreach ($products as $p): ?>
            <?php if ($p['is_made_to_order']) continue; ?>
            <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (atual: <?= (int) $p['stock_quantity'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="grid2" style="margin-bottom:12px">
        <div class="field">
          <label>Tipo</label>
          <select name="type">
            <option value="entrada">Entrada (+)</option>
            <option value="saida">Saída (−)</option>
            <option value="ajuste">Ajuste (definir saldo)</option>
          </select>
        </div>
        <div class="field"><label>Quantidade</label><input name="quantity" type="number" min="0" value="1"></div>
      </div>
      <div class="field" style="margin-bottom:14px"><label>Motivo</label><input name="reason" placeholder="Compra de filamento, perda, contagem..."></div>
      <button class="btn block" type="submit">Registrar</button>
    </form>
  </div>

  <div class="panel">
    <div class="panel-head"><h3>Estoque baixo</h3></div>
    <?php if (!$lowStock): ?>
      <p class="empty-state">Nenhum produto abaixo do mínimo. 🎉</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Produto</th><th>Atual</th><th>Mínimo</th></tr></thead>
          <tbody>
          <?php foreach ($lowStock as $p): ?>
            <tr><td><a href="<?= url('admin/produtos/' . $p['id'] . '/editar') ?>"><?= e($p['name']) ?></a></td><td><span class="pill low"><?= (int) $p['stock_quantity'] ?></span></td><td><?= (int) $p['low_stock_alert'] ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3>Movimentações recentes</h3></div>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Data</th><th>Produto</th><th>Tipo</th><th>Qtd</th><th>Saldo</th><th>Motivo</th><th>Ref.</th><th>Por</th></tr></thead>
      <tbody>
      <?php if (!$movements): ?><tr><td colspan="8" class="empty-state">Nenhuma movimentação.</td></tr><?php endif; ?>
      <?php foreach ($movements as $m): ?>
        <tr>
          <td><?= date('d/m H:i', strtotime($m['created_at'])) ?></td>
          <td><?= e($m['product_name']) ?></td>
          <td><span class="pill <?= $m['type'] === 'entrada' ? 'pago' : ($m['type'] === 'saida' ? 'cancelado' : 'em_producao') ?>"><?= e($m['type']) ?></span></td>
          <td><?= (int) $m['quantity'] ?></td>
          <td><strong><?= (int) $m['balance_after'] ?></strong></td>
          <td><?= e($m['reason'] ?: '—') ?></td>
          <td class="muted" style="font-size:11px"><?= e($m['reference'] ?: '—') ?></td>
          <td class="muted"><?= e($m['user_name'] ?: 'sistema') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
