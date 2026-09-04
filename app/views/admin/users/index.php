<?php use Core\Csrf; use Core\Auth; /** @var array $users */ ?>
<div class="grid2" style="align-items:start">
  <div class="panel">
    <h3>Novo usuário</h3>
    <form method="post" action="<?= url('admin/usuarios') ?>">
      <?= Csrf::field() ?>
      <div class="field" style="margin-bottom:12px"><label>Nome *</label><input name="name" required></div>
      <div class="field" style="margin-bottom:12px"><label>E-mail *</label><input type="email" name="email" required></div>
      <div class="field" style="margin-bottom:12px"><label>Senha * (mín. 6)</label><input type="password" name="password" required></div>
      <div class="field" style="margin-bottom:14px">
        <label>Perfil</label>
        <select name="role"><option value="admin">Administrador</option><option value="manager">Gerente</option></select>
      </div>
      <button class="btn block" type="submit">Criar usuário</button>
    </form>
  </div>

  <div class="panel">
    <div class="panel-head"><h3><?= count($users) ?> usuário(s)</h3></div>
    <?php foreach ($users as $u): ?>
      <form method="post" action="<?= url('admin/usuarios/' . $u['id']) ?>" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:12px">
        <?= Csrf::field() ?>
        <div class="grid2" style="margin-bottom:10px">
          <div class="field"><label>Nome</label><input name="name" value="<?= e($u['name']) ?>"></div>
          <div class="field"><label>E-mail</label><input name="email" value="<?= e($u['email']) ?>"></div>
          <div class="field"><label>Nova senha (opcional)</label><input type="password" name="password" placeholder="deixe em branco para manter"></div>
          <div class="field"><label>Perfil</label><select name="role"><option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option><option value="manager" <?= $u['role'] === 'manager' ? 'selected' : '' ?>>Gerente</option></select></div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
          <label class="check-row"><input type="checkbox" name="is_active" value="1" <?= $u['is_active'] ? 'checked' : '' ?>> Ativo</label>
          <span class="muted" style="font-size:11px">Último acesso: <?= $u['last_login_at'] ? date('d/m/Y H:i', strtotime($u['last_login_at'])) : 'nunca' ?></span>
          <div style="margin-left:auto;display:flex;gap:8px">
            <button class="btn sm" type="submit">Salvar</button>
          </div>
        </div>
      </form>
      <?php if ($u['id'] != Auth::id()): ?>
        <form method="post" action="<?= url('admin/usuarios/' . $u['id'] . '/excluir') ?>" data-confirm="Excluir o usuário <?= e($u['name']) ?>?" style="margin:-6px 0 18px">
          <?= Csrf::field() ?><button class="btn danger sm" type="submit">Excluir</button>
        </form>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
