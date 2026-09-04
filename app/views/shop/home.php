<?php
use Core\View;
/** @var array $featured @var array $categories @var array $novidades */
$petalSvg = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c4 4 8 6 8 11a8 8 0 1 1-16 0c0-5 4-7 8-11z"/></svg>';
?>
<section class="hero">
  <div class="wrap">
    <div class="hero-copy">
      <span class="kicker">Impressão 3D com propósito</span>
      <h1>Peças que <span>acolhem</span>, divertem e decoram.</h1>
      <p class="lead">A Idéias Flamboyant cria materiais terapêuticos, brinquedos e objetos de decoração impressos em 3D — pensados com carinho, feitos sob medida para o seu dia a dia.</p>
      <div class="hero-cta">
        <a class="btn primary lg" href="<?= url('produtos') ?>">Ver a loja</a>
        <a class="btn ghost lg" href="<?= e(whatsapp_link('Olá! Quero conversar sobre uma peça personalizada em 3D.')) ?>" target="_blank" rel="noopener">Falar no WhatsApp</a>
      </div>
      <div class="hero-badges">
        <div><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5zm0 7L2 14l10 5 10-5z"/></svg> PLA de qualidade</div>
        <div><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-8-4.5-8-11a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 6.5-8 11-8 11z"/></svg> Feito com cuidado</div>
        <div><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h13V4H3zm14 0h4l-3-4h-1zM6 18a2 2 0 1 0 4 0 2 2 0 0 0-4 0m9 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/></svg> Envio para todo o Brasil</div>
      </div>
    </div>

    <div class="hero-logo">
      <span class="petal p1"><?= $petalSvg ?></span>
      <span class="petal p2"><?= $petalSvg ?></span>
      <span class="petal p3"><?= $petalSvg ?></span>
      <span class="petal p4"><?= $petalSvg ?></span>
      <img src="<?= asset('img/logo.png') ?>" alt="Idéias Flamboyant" width="430" height="430">
    </div>
  </div>
</section>

<?php if ($categories): ?>
<section class="section">
  <div class="wrap">
    <div class="section-head">
      <div><span class="kicker">Categorias</span><h2>Escolha por necessidade</h2></div>
      <a class="btn ghost sm" href="<?= url('produtos') ?>">Ver tudo</a>
    </div>
    <div class="cat-strip">
      <?php foreach ($categories as $c): ?>
        <a class="cat-chip" href="<?= url('categoria/' . $c['slug']) ?>">
          <?= e($c['name']) ?>
          <span><?= (int) $c['product_count'] ?> produto<?= $c['product_count'] == 1 ? '' : 's' ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($featured): ?>
<section class="section alt">
  <div class="wrap">
    <div class="section-head">
      <div><span class="kicker">Seleção</span><h2>Destaques da loja</h2></div>
      <a class="btn ghost sm" href="<?= url('produtos?destaque=1') ?>">Ver destaques</a>
    </div>
    <div class="product-grid">
      <?php foreach ($featured as $p) { echo View::partial('partials/product-card', ['p' => $p]); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="wrap">
    <div class="features">
      <div class="feature">
        <div class="ic"><svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 21s-8-4.5-8-11a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 6.5-8 11-8 11z"/></svg></div>
        <h3>Foco terapêutico</h3>
        <p class="muted">Recursos pensados para acolhimento, foco, estímulo sensorial e desenvolvimento — sozinhos ou como apoio a profissionais.</p>
      </div>
      <div class="feature">
        <div class="ic"><svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 2 2 7l10 5 10-5zm0 7L2 14l10 5 10-5z"/></svg></div>
        <h3>PLA de qualidade</h3>
        <p class="muted">Todas as peças são impressas em PLA, um material de origem vegetal, resistente e com bom acabamento.</p>
      </div>
      <div class="feature">
        <div class="ic"><svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M20 6h-3V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2M9 4h6v2H9z"/></svg></div>
        <h3>Personalização</h3>
        <p class="muted">Cores, tamanhos e projetos sob medida. Tem uma ideia? A gente modela e imprime para você.</p>
      </div>
      <div class="feature">
        <div class="ic"><svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M21 7 9 19l-5.5-5.5 1.4-1.4L9 16.2 19.6 5.6z"/></svg></div>
        <h3>Compra tranquila</h3>
        <p class="muted">Cartão, Pix e boleto via Mercado Pago e InfinitePay — ou combine tudo direto pelo WhatsApp.</p>
      </div>
    </div>
  </div>
</section>

<?php if ($novidades): ?>
<section class="section alt">
  <div class="wrap">
    <div class="section-head"><div><span class="kicker">Novidades</span><h2>Chegou agora</h2></div></div>
    <div class="product-grid">
      <?php foreach ($novidades as $p) { echo View::partial('partials/product-card', ['p' => $p]); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="wrap">
    <div class="cta-band">
      <h2>Tem uma ideia em mente?</h2>
      <p>Da modelagem à peça final. Conte o que você precisa e receba um orçamento sem compromisso.</p>
      <a class="btn lg" href="<?= e(whatsapp_link('Olá! Quero conversar sobre um projeto personalizado em impressão 3D.')) ?>" target="_blank" rel="noopener">Falar no WhatsApp</a>
    </div>
  </div>
</section>
