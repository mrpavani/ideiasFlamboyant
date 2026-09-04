<?php use Core\Csrf; $flash = \Core\Flash::all(); ?>
<form class="login-card" method="post" action="<?= url('admin/login') ?>">
  <?= Csrf::field() ?>
  <img src="<?= asset('img/logo.png') ?>" alt="Idéias Flamboyant">
  <h1>Painel Idéias Flamboyant</h1>
  <p class="sub">Acesse com suas credenciais</p>

  <?php if (!empty($flash['error'])): ?><div class="a-alert err" style="border-radius:10px;margin-bottom:16px;padding:10px 14px"><?= e($flash['error']) ?></div><?php endif; ?>

  <div class="field">
    <label>E-mail</label>
    <input type="email" name="email" value="<?= e(old('email')) ?>" required autofocus>
  </div>
  <div class="field">
    <label>Senha</label>
    <input type="password" name="password" required>
  </div>
  <button class="btn block" type="submit" style="margin-top:8px">Entrar</button>
  <p class="muted" style="text-align:center;margin:18px 0 0;font-size:12px">
    <a href="<?= url('/') ?>">← Voltar para a loja</a>
  </p>
</form>
