<?php
use Core\Csrf;
/** @var ?array $product @var array $categories @var array $images */
$p = $product;
$isEdit = $p !== null;
$v = static fn (string $k, $d = '') => e((string) ($p[$k] ?? old($k, $d)));
$action = $isEdit ? url('admin/produtos/' . $p['id']) : url('admin/produtos');
$images = $images ?? [];
?>
<div class="panel-head">
  <h3><?= $isEdit ? 'Editar produto' : 'Novo produto' ?></h3>
  <a class="btn ghost sm" href="<?= url('admin/produtos') ?>">← Voltar</a>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>

  <div class="grid2" style="align-items:start">
    <div>
      <div class="panel">
        <h3>Informações</h3>
        <div class="field" style="margin-bottom:14px">
          <label>Nome *</label>
          <input name="name" value="<?= $v('name') ?>" required>
        </div>
        <div class="grid2" style="margin-bottom:14px">
          <div class="field"><label>SKU / código</label><input name="sku" value="<?= $v('sku') ?>"></div>
          <div class="field"><label>Slug (URL)</label><input name="slug" value="<?= $v('slug') ?>" placeholder="gerado automaticamente"></div>
        </div>
        <div class="field" style="margin-bottom:14px">
          <label>Descrição curta</label>
          <input name="short_description" value="<?= $v('short_description') ?>" maxlength="400">
        </div>
        <div class="field">
          <label>Descrição completa</label>
          <textarea name="description" rows="6"><?= e((string) ($p['description'] ?? old('description'))) ?></textarea>
        </div>
      </div>

      <div class="panel">
        <h3>Ficha técnica</h3>
        <div class="grid2">
          <div class="field"><label>Material</label><input name="material" value="<?= $v('material', 'PLA') ?>"><span class="hint">Padrão: PLA.</span></div>
          <div class="field"><label>Cor</label><input name="color" value="<?= $v('color') ?>" placeholder="Ex.: à escolha do cliente"></div>
          <div class="field"><label>Peso (g)</label><input name="weight_grams" type="number" value="<?= $v('weight_grams') ?>"></div>
          <div class="field"><label>Dimensões</label><input name="dimensions" value="<?= $v('dimensions') ?>" placeholder="10 x 8 x 5 cm"></div>
        </div>
      </div>

      <div class="panel">
        <h3>Imagens</h3>
        <?php if ($images): ?>
          <div class="img-grid" style="margin-bottom:16px">
            <?php foreach ($images as $img): ?>
              <div class="img-item <?= $img['is_primary'] ? 'primary' : '' ?>">
                <?php if ($img['is_primary']): ?><span class="star">principal</span><?php endif; ?>
                <img src="<?= upload_url($img['path']) ?>" alt="">
                <div class="ops">
                  <?php if (!$img['is_primary']): ?>
                    <form method="post" action="<?= url('admin/produtos/imagens/' . $img['id'] . '/principal') ?>"><?= Csrf::field() ?><button type="submit">★ principal</button></form>
                  <?php endif; ?>
                  <form method="post" action="<?= url('admin/produtos/imagens/' . $img['id'] . '/excluir') ?>" data-confirm="Remover esta imagem?"><?= Csrf::field() ?><button class="rm" type="submit">excluir</button></form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="field">
          <label>Adicionar imagens (JPG, PNG, WEBP — máx. 6 MB cada)</label>
          <input type="file" name="images[]" accept="image/*" multiple>
          <span class="hint">A primeira imagem enviada vira a principal se ainda não houver uma.</span>
        </div>
      </div>
    </div>

    <div>
      <div class="panel">
        <h3>Preço</h3>
        <div class="field" style="margin-bottom:12px"><label>Preço de venda (R$) *</label><input name="price" value="<?= $v('price', '0') ?>" required></div>
        <div class="field" style="margin-bottom:12px"><label>Preço "de" (riscado)</label><input name="compare_at_price" value="<?= $v('compare_at_price') ?>"></div>
        <div class="field"><label>Custo (uso interno)</label><input name="cost_price" value="<?= $v('cost_price') ?>"></div>
      </div>

      <div class="panel">
        <h3>Organização</h3>
        <div class="field" style="margin-bottom:12px">
          <label>Categoria</label>
          <select name="category_id">
            <option value="">— sem categoria —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (string) ($p['category_id'] ?? old('category_id')) === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label class="check-row" style="margin-bottom:10px"><input type="checkbox" name="is_active" value="1" <?= (!$isEdit || $p['is_active']) ? 'checked' : '' ?>> Produto ativo (visível na loja)</label>
        <label class="check-row"><input type="checkbox" name="is_featured" value="1" <?= ($p['is_featured'] ?? false) ? 'checked' : '' ?>> Destaque na home</label>
      </div>

      <div class="panel">
        <h3>Estoque</h3>
        <label class="check-row" style="margin-bottom:10px"><input type="checkbox" name="track_stock" value="1" <?= (!$isEdit || $p['track_stock']) ? 'checked' : '' ?>> Controlar estoque deste produto</label>
        <label class="check-row" style="margin-bottom:14px"><input type="checkbox" name="is_made_to_order" value="1" <?= ($p['is_made_to_order'] ?? false) ? 'checked' : '' ?>> Sob encomenda (sem estoque físico)</label>
        <div class="grid2">
          <div class="field">
            <label><?= $isEdit ? 'Estoque atual' : 'Estoque inicial' ?></label>
            <input name="stock_quantity" type="number" value="<?= $v('stock_quantity', '0') ?>">
            <?php if ($isEdit): ?><span class="hint">Alterar aqui gera uma movimentação de ajuste.</span><?php endif; ?>
          </div>
          <div class="field"><label>Alerta de estoque baixo</label><input name="low_stock_alert" type="number" value="<?= $v('low_stock_alert', '3') ?>"></div>
        </div>
      </div>

      <button class="btn block" type="submit" style="margin-bottom:10px"><?= $isEdit ? 'Salvar alterações' : 'Cadastrar produto' ?></button>
      <?php if ($isEdit): ?>
        <a class="btn ghost block" href="<?= url('produto/' . $p['slug']) ?>" target="_blank" rel="noopener">Ver na loja ↗</a>
      <?php endif; ?>
    </div>
  </div>
</form>
