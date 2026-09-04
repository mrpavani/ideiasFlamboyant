<?php /** @var array $stats @var array $recent @var array $lowStock */ ?>
<div class="stats">
  <div class="stat accent">
    <div class="label">Receita do mês</div>
    <div class="value"><?= money($stats['revenue_month']) ?></div>
  </div>
  <div class="stat">
    <div class="label">Recebido hoje</div>
    <div class="value"><?= money($stats['paid_today']) ?></div>
  </div>
  <div class="stat">
    <div class="label">Pedidos pendentes</div>
    <div class="value"><?= $stats['pending_orders'] ?></div>
  </div>
  <div class="stat">
    <div class="label">Total de pedidos</div>
    <div class="value"><?= $stats['total_orders'] ?></div>
  </div>
  <div class="stat">
    <div class="label">Produtos ativos</div>
    <div class="value"><?= $stats['active_products'] ?></div>
  </div>
  <div class="stat">
    <div class="label">Alertas de estoque</div>
    <div class="value" style="color:<?= $stats['low_stock'] ? 'var(--flame-dark)' : 'inherit' ?>"><?= $stats['low_stock'] ?></div>
  </div>
</div>

<div class="grid2" style="align-items:start">
  <div class="panel">
    <div class="panel-head"><h3>Pedidos recentes</h3><a class="btn ghost sm" href="<?= url('admin/pedidos') ?>">Ver todos</a></div>
    <?php if (!$recent): ?>
      <p class="empty-state">Nenhum pedido ainda.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Pedido</th><th>Cliente</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($recent as $o): ?>
            <tr>
              <td><a href="<?= url('admin/pedidos/' . $o['id']) ?>" style="font-weight:600;color:var(--flame-dark)"><?= e($o['reference']) ?></a><br><span class="muted" style="font-size:11px"><?= date('d/m H:i', strtotime($o['created_at'])) ?></span></td>
              <td><?= e($o['customer_name']) ?></td>
              <td><?= money($o['total']) ?></td>
              <td><span class="pill <?= e($o['status']) ?>"><?= str_replace('_', ' ', e($o['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head"><h3>Estoque baixo</h3><a class="btn ghost sm" href="<?= url('admin/estoque') ?>">Gerenciar</a></div>
    <?php if (!$lowStock): ?>
      <p class="empty-state">Tudo certo! Nenhum produto abaixo do mínimo.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Produto</th><th>Estoque</th><th>Mínimo</th></tr></thead>
          <tbody>
          <?php foreach ($lowStock as $p): ?>
            <tr>
              <td><a href="<?= url('admin/produtos/' . $p['id'] . '/editar') ?>"><?= e($p['name']) ?></a></td>
              <td><span class="pill low"><?= (int) $p['stock_quantity'] ?></span></td>
              <td><?= (int) $p['low_stock_alert'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
