<?php
use Core\Csrf;
/** @var array $settings @var array $apiTokens @var ?string $newToken */
$s = static fn (string $k, $d = '') => e((string) ($settings[$k] ?? $d));
$on = static fn (string $k) => ($settings[$k] ?? '0') === '1' ? 'checked' : '';
?>
<div class="tabs">
  <a href="#geral" data-tab>Geral</a>
  <a href="#contato" data-tab>Contato / WhatsApp</a>
  <a href="#entrega" data-tab>Entrega</a>
  <a href="#pagamento" data-tab>Pagamentos</a>
  <a href="#api" data-tab>API / Integração</a>
</div>

<form method="post" action="<?= url('admin/configuracoes') ?>">
  <?= Csrf::field() ?>

  <section data-pane="geral">
    <div class="panel">
      <h3>Dados da loja</h3>
      <div class="grid2">
        <div class="field"><label>Nome da loja</label><input name="store_name" value="<?= $s('store_name') ?>"></div>
        <div class="field"><label>Slogan</label><input name="store_tagline" value="<?= $s('store_tagline') ?>"></div>
        <div class="field"><label>E-mail de contato</label><input name="store_email" value="<?= $s('store_email') ?>"></div>
        <div class="field"><label>Cidade / região</label><input name="store_city" value="<?= $s('store_city') ?>"></div>
      </div>
    </div>
  </section>

  <section data-pane="contato" hidden>
    <div class="panel">
      <h3>WhatsApp</h3>
      <div class="grid2">
        <div class="field">
          <label>Número (com DDI + DDD, só dígitos)</label>
          <input name="whatsapp_number" value="<?= $s('whatsapp_number') ?>" placeholder="5562999999999">
          <span class="hint">Ex.: 55 (Brasil) + 62 (DDD) + número. Usado no botão flutuante e nos pedidos.</span>
        </div>
        <div class="field"><label>Instagram (URL)</label><input name="instagram_url" value="<?= $s('instagram_url') ?>"></div>
        <div class="field" style="grid-column:1/-1"><label>Mensagem padrão do WhatsApp</label><input name="whatsapp_message" value="<?= $s('whatsapp_message') ?>"></div>
      </div>
    </div>
  </section>

  <section data-pane="entrega" hidden>
    <div class="panel">
      <h3>Frete</h3>
      <div class="grid2">
        <div class="field"><label>Frete fixo (R$)</label><input name="shipping_flat_rate" value="<?= $s('shipping_flat_rate') ?>"><span class="hint">0 = frete combinado pelo WhatsApp.</span></div>
        <div class="field"><label>Frete grátis acima de (R$)</label><input name="shipping_free_above" value="<?= $s('shipping_free_above') ?>"><span class="hint">0 = desativado.</span></div>
        <div class="field" style="grid-column:1/-1"><label>Aviso de entrega (exibido na loja)</label><input name="shipping_note" value="<?= $s('shipping_note') ?>"></div>
      </div>
    </div>
  </section>

  <section data-pane="pagamento" hidden>
    <div class="panel">
      <h3>Mercado Pago</h3>
      <label class="check-row" style="margin-bottom:10px"><input type="checkbox" name="mp_enabled" value="1" <?= $on('mp_enabled') ?>> Ativar Mercado Pago no checkout</label>
      <label class="check-row" style="margin-bottom:14px"><input type="checkbox" name="mp_sandbox" value="1" <?= $on('mp_sandbox') ?>> Modo sandbox (testes)</label>
      <div class="grid2">
        <div class="field"><label>Access Token</label><input name="mp_access_token" type="password" placeholder="<?= $settings['mp_access_token'] ? '•••••• (salvo)' : 'APP_USR-...' ?>"><span class="hint">Deixe em branco para manter o valor atual.</span></div>
        <div class="field"><label>Public Key</label><input name="mp_public_key" type="password" placeholder="<?= $settings['mp_public_key'] ? '•••••• (salvo)' : 'APP_USR-...' ?>"></div>
      </div>
      <p class="hint" style="margin-top:10px">Webhook (notification URL): <code><?= url('webhook/mercadopago') ?></code></p>
    </div>

    <div class="panel">
      <h3>InfinitePay</h3>
      <label class="check-row" style="margin-bottom:14px"><input type="checkbox" name="infinitepay_enabled" value="1" <?= $on('infinitepay_enabled') ?>> Ativar InfinitePay no checkout</label>
      <div class="grid2">
        <div class="field"><label>Handle (usuário InfinitePay)</label><input name="infinitepay_handle" value="<?= $s('infinitepay_handle') ?>" placeholder="sualoja"></div>
        <div class="field"><label>Token / API Key</label><input name="infinitepay_token" type="password" placeholder="<?= $settings['infinitepay_token'] ? '•••••• (salvo)' : 'opcional' ?>"><span class="hint">Deixe em branco para manter o valor atual.</span></div>
      </div>
      <p class="hint" style="margin-top:10px">URL de retorno / webhook: <code><?= url('webhook/infinitepay') ?></code></p>
    </div>

    <div class="panel">
      <h3>Fallback</h3>
      <label class="check-row"><input type="checkbox" name="payment_whatsapp_fallback" value="1" <?= $on('payment_whatsapp_fallback') ?>> Sempre oferecer "combinar pelo WhatsApp" no checkout</label>
    </div>
  </section>

  <div style="position:sticky;bottom:0;background:linear-gradient(transparent,var(--cream) 40%);padding:16px 0" data-save>
    <button class="btn" type="submit">Salvar configurações</button>
  </div>
</form>

<section data-pane="api" hidden>
  <div class="panel">
    <h3>Tokens de API</h3>
    <p class="muted">Para integrar um sistema de vendas / ERP. Base da API: <code><?= url('api/v1') ?></code> — autenticação por header <code>Authorization: Bearer &lt;token&gt;</code>.</p>

    <?php if ($newToken): ?>
      <div class="token-box">🔑 <?= e($newToken) ?></div>
      <p class="hint">Copie agora — este valor não será exibido novamente.</p>
    <?php endif; ?>

    <form method="post" action="<?= url('admin/configuracoes/api-token') ?>" class="inline-form" style="margin:16px 0">
      <?= Csrf::field() ?>
      <div class="field"><label>Nome da integração</label><input name="name" placeholder="ERP / Bling / Tiny..." required></div>
      <label class="check-row"><input type="checkbox" name="can_write" value="1"> Permitir escrita (status, estoque)</label>
      <button class="btn sm" type="submit">Gerar token</button>
    </form>

    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Nome</th><th>Prefixo</th><th>Permissões</th><th>Último uso</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (!$apiTokens): ?><tr><td colspan="6" class="empty-state">Nenhum token gerado.</td></tr><?php endif; ?>
        <?php foreach ($apiTokens as $t): ?>
          <tr>
            <td><?= e($t['name']) ?></td>
            <td class="muted"><?= e($t['token_preview']) ?>…</td>
            <td><?= e($t['scopes']) ?></td>
            <td class="muted"><?= $t['last_used_at'] ? date('d/m/Y H:i', strtotime($t['last_used_at'])) : 'nunca' ?></td>
            <td><span class="pill <?= $t['is_active'] ? 'pago' : 'cancelado' ?>"><?= $t['is_active'] ? 'ativo' : 'revogado' ?></span></td>
            <td>
              <?php if ($t['is_active']): ?>
                <form method="post" action="<?= url('admin/configuracoes/api-token/' . $t['id'] . '/revogar') ?>" data-confirm="Revogar este token? Integrações que o usam deixarão de funcionar.">
                  <?= Csrf::field() ?><button class="btn danger sm" type="submit">Revogar</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <h3 style="margin-top:24px">Endpoints disponíveis</h3>
    <div class="table-wrap">
      <table class="data">
        <tbody>
          <tr><td><code>GET</code></td><td><code>/api/v1/products</code></td><td>Lista produtos + estoque</td></tr>
          <tr><td><code>GET</code></td><td><code>/api/v1/products/{id}</code></td><td>Detalhe do produto</td></tr>
          <tr><td><code>PATCH</code></td><td><code>/api/v1/products/{id}/stock</code></td><td>Ajusta estoque <span class="muted">(write)</span></td></tr>
          <tr><td><code>GET</code></td><td><code>/api/v1/orders</code></td><td>Lista pedidos (<code>?status=</code>, <code>?since=</code>)</td></tr>
          <tr><td><code>GET</code></td><td><code>/api/v1/orders/{id}</code></td><td>Detalhe do pedido</td></tr>
          <tr><td><code>POST</code></td><td><code>/api/v1/orders/{id}/status</code></td><td>Atualiza status <span class="muted">(write)</span></td></tr>
          <tr><td><code>GET</code></td><td><code>/api/v1/stock/movements</code></td><td>Histórico de estoque</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
(function(){
  const tabs=[...document.querySelectorAll('[data-tab]')];
  const panes=[...document.querySelectorAll('[data-pane]')];
  const save=document.querySelector('[data-save]');
  function show(id){
    panes.forEach(p=>p.hidden=p.dataset.pane!==id);
    tabs.forEach(t=>t.classList.toggle('active',t.getAttribute('href')==='#'+id));
    if(save) save.style.display = id==='api' ? 'none' : '';
  }
  tabs.forEach(t=>t.addEventListener('click',e=>{e.preventDefault();const id=t.getAttribute('href').slice(1);history.replaceState(null,'','#'+id);show(id);}));
  show((location.hash||'#geral').slice(1));
})();
</script>
