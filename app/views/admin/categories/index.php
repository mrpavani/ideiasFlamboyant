<?php use Core\Csrf; /** @var array $categories */ ?>
<div class="grid2" style="align-items:start">
  <div class="panel">
    <h3>Nova categoria</h3>
    <form method="post" action="<?= url('admin/categorias') ?>">
      <?= Csrf::field() ?>
      <div class="field" style="margin-bottom:12px"><label>Nome *</label><input name="name" required></div>
      <div class="field" style="margin-bottom:12px"><label>Slug (opcional)</label><input name="slug" placeholder="gerado do nome"></div>
      <div class="field" style="margin-bottom:12px"><label>Descrição</label><input name="description"></div>
      <div class="grid2" style="margin-bottom:12px">
        <div class="field"><label>Posição</label><input name="position" type="number" value="0"></div>
      </div>
      <label class="check-row" style="margin-bottom:14px"><input type="checkbox" name="is_active" value="1" checked> Ativa</label>
      <button class="btn block" type="submit">Criar categoria</button>
    </form>
  </div>

  <div class="panel">
    <div class="panel-head"><h3><?= count($categories) ?> categoria(s)</h3></div>
    <?php if (!$categories): ?>
      <p class="empty-state">Nenhuma categoria.</p>
    <?php endif; ?>
    <?php foreach ($categories as $c): ?>
      <form method="post" action="<?= url('admin/categorias/' . $c['id']) ?>" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:12px">
        <?= Csrf::field() ?>
        <div class="grid2" style="margin-bottom:10px">
          <div class="field"><label>Nome</label><input name="name" value="<?= e($c['name']) ?>"></div>
          <div class="field"><label>Slug</label><input name="slug" value="<?= e($c['slug']) ?>"></div>
        </div>
        <div class="field" style="margin-bottom:10px"><label>Descrição</label><input name="description" value="<?= e($c['description']) ?>"></div>
        <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap">
          <div class="field" style="width:90px"><label>Posição</label><input name="position" type="number" value="<?= (int) $c['position'] ?>"></div>
          <label class="check-row"><input type="checkbox" name="is_active" value="1" <?= $c['is_active'] ? 'checked' : '' ?>> Ativa</label>
          <div style="margin-left:auto;display:flex;gap:8px">
            <button class="btn sm" type="submit">Salvar</button>
        </div>
        </div>
      </form>
      <form method="post" action="<?= url('admin/categorias/' . $c['id'] . '/excluir') ?>" data-confirm="Excluir a categoria &quot;<?= e($c['name']) ?>&quot;?" style="margin:-6px 0 18px">
        <?= Csrf::field() ?>
        <button class="btn danger sm" type="submit">Excluir categoria</button>
      </form>
    <?php endforeach; ?>
  </div>
</div>
