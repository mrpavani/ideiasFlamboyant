<div class="wrap prose">
  <span class="kicker">Atendimento</span>
  <h1>Fale com a Idéias Flamboyant</h1>
  <p>Nosso canal principal é o WhatsApp — respondemos pedidos, dúvidas e orçamentos por lá.</p>

  <p>
    <a class="btn wa lg" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">Abrir conversa no WhatsApp</a>
  </p>

  <table class="spec-table" style="margin-top:30px">
    <tbody>
      <tr><th>WhatsApp</th><td>+<?= e(setting('whatsapp_number', '')) ?></td></tr>
      <?php if (setting('store_email')): ?><tr><th>E-mail</th><td><a href="mailto:<?= e(setting('store_email')) ?>"><?= e(setting('store_email')) ?></a></td></tr><?php endif; ?>
      <?php if (setting('instagram_url')): ?><tr><th>Instagram</th><td><a href="<?= e(setting('instagram_url')) ?>" target="_blank" rel="noopener"><?= e(setting('instagram_url')) ?></a></td></tr><?php endif; ?>
      <?php if (setting('store_city')): ?><tr><th>Localização</th><td><?= e(setting('store_city')) ?></td></tr><?php endif; ?>
      <tr><th>O que fazemos</th><td>Materiais terapêuticos, brinquedos e decoração em impressão 3D (PLA)</td></tr>
      <tr><th>Personalização</th><td>Cores, tamanhos e projetos sob medida</td></tr>
    </tbody>
  </table>
</div>
