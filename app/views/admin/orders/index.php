<?php /** @var array $orders @var array $filters @var array $statuses */ ?>
<form class="filters-bar" method="get">
  <input type="search" name="q" placeholder="Buscar por código, cliente ou telefone" value="<?= e($filters['search']) ?>">
  <select name="status" onchange="this.form.submit()">
    <option value="">Todos os status</option>
    <?php foreach ($statuses as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn sm" type="submit">Filtrar</button>
</form>

<div class="table-wrap">
  <table class="data">
    <thead><tr><th>Pedido</th><th>Data</th><th>Cliente</th><th>Pagamento</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$orders): ?>
      <tr><td colspan="7" class="empty-state">Nenhum pedido encontrado.</td></tr>
    <?php endif; ?>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><strong style="color:var(--flame-dark)"><?= e($o['reference']) ?></strong></td>
        <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
        <td><?= e($o['customer_name']) ?><br><span class="muted" style="font-size:11px"><?= e($o['customer_phone']) ?></span></td>
        <td>
          <?= e($o['payment_method'] ?: '—') ?><br>
          <span class="pill <?= e($o['payment_status']) ?>" style="font-size:10px"><?= e($o['payment_status']) ?></span>
        </td>
        <td><?= money($o['total']) ?></td>
        <td><span class="pill <?= e($o['status']) ?>"><?= str_replace('_', ' ', e($o['status'])) ?></span></td>
        <td><a class="btn ghost sm" href="<?= url('admin/pedidos/' . $o['id']) ?>">Abrir</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
