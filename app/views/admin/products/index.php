<?php use Core\Csrf; /** @var array $products @var array $filters */ ?>
<div class="panel-head">
  <h3><?= count($products) ?> produto(s)</h3>
  <a class="btn" href="<?= url('admin/produtos/novo') ?>">+ Novo produto</a>
</div>

<form class="filters-bar" method="get">
  <input type="search" name="q" placeholder="Buscar por nome ou SKU" value="<?= e($filters['search']) ?>">
  <select name="status" onchange="this.form.submit()">
    <option value="">Todos os status</option>
    <option value="ativos" <?= $filters['status'] === 'ativos' ? 'selected' : '' ?>>Ativos</option>
    <option value="inativos" <?= $filters['status'] === 'inativos' ? 'selected' : '' ?>>Inativos</option>
    <option value="baixo_estoque" <?= $filters['status'] === 'baixo_estoque' ? 'selected' : '' ?>>Estoque baixo</option>
  </select>
  <button class="btn sm" type="submit">Filtrar</button>
</form>

<div class="table-wrap">
  <table class="data">
    <thead><tr><th></th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$products): ?>
      <tr><td colspan="7" class="empty-state">Nenhum produto encontrado. <a href="<?= url('admin/produtos/novo') ?>">Cadastrar o primeiro</a>.</td></tr>
    <?php endif; ?>
    <?php foreach ($products as $p): ?>
      <?php $tracks = $p['track_stock'] && !$p['is_made_to_order']; ?>
      <tr>
        <td><?php if ($p['image']): ?><img class="thumb" src="<?= upload_url($p['image']) ?>" alt=""><?php else: ?><div class="thumb"></div><?php endif; ?></td>
        <td><strong><?= e($p['name']) ?></strong><br><span class="muted" style="font-size:11px"><?= e($p['sku'] ?: '—') ?></span></td>
        <td><?= e($p['category_name'] ?: '—') ?></td>
        <td><?= (float) $p['price'] > 0 ? money($p['price']) : '<span class="muted">sob consulta</span>' ?></td>
        <td>
          <?php if ($p['is_made_to_order']): ?><span class="muted">sob encomenda</span>
          <?php elseif (!$tracks): ?><span class="muted">não controla</span>
          <?php else: ?>
            <span class="pill <?= $p['stock_quantity'] <= $p['low_stock_alert'] ? 'low' : 'pago' ?>"><?= (int) $p['stock_quantity'] ?></span>
          <?php endif; ?>
        </td>
        <td><span class="pill <?= $p['is_active'] ? 'pago' : 'cancelado' ?>"><?= $p['is_active'] ? 'ativo' : 'inativo' ?></span></td>
        <td style="white-space:nowrap">
          <a class="btn ghost sm" href="<?= url('admin/produtos/' . $p['id'] . '/editar') ?>">Editar</a>
          <form method="post" action="<?= url('admin/produtos/' . $p['id'] . '/excluir') ?>" style="display:inline" data-confirm="Excluir &quot;<?= e($p['name']) ?>&quot;? Esta ação não pode ser desfeita.">
            <?= Csrf::field() ?>
            <button class="btn danger sm" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
