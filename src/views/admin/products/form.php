<?php
use App\Helpers;
use App\Csrf;
use App\Rbac;
$v = fn(string $k) => Helpers::e($item[$k] ?? '');
$sel = fn($a, $b) => ((string)$a === (string)$b) ? 'selected' : '';
$newItem = empty($item);
$activeChk = ($newItem || !empty($item['is_active'])) ? 'checked' : '';
$bestChk   = (!$newItem && !empty($item['is_best'])) ? 'checked' : '';
?>
<div class="panel">
  <div class="panel-head"><?= $isEdit ? '제품 수정' : '제품 등록' ?></div>
  <div class="panel-body">
    <form method="post" action="<?= Helpers::e($action) ?>" enctype="multipart/form-data" class="form">
      <?= Csrf::field() ?>

      <div class="row">
        <label class="fld">브랜드
          <select name="brand_id">
            <option value="">— 선택 —</option>
            <?php foreach ($brands as $b): ?>
              <option value="<?= (int)$b['id'] ?>" <?= $sel($item['brand_id'] ?? '', $b['id']) ?>><?= Helpers::e($b['name_ko']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="fld">카테고리
          <select name="category_id">
            <option value="">— 선택 —</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= $sel($item['category_id'] ?? '', $c['id']) ?>><?= Helpers::e($c['name_ko']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <div class="row">
        <label class="fld">제품명 (KO) <span class="req">*</span><input type="text" name="name_ko" value="<?= $v('name_ko') ?>" required></label>
        <label class="fld">제품명 (VI)<input type="text" name="name_vi" value="<?= $v('name_vi') ?>"></label>
        <label class="fld">제품명 (EN)<input type="text" name="name_en" value="<?= $v('name_en') ?>"></label>
      </div>

      <div class="row">
        <label class="fld">요약 (KO)<input type="text" name="summary_ko" value="<?= $v('summary_ko') ?>"></label>
        <label class="fld">요약 (VI)<input type="text" name="summary_vi" value="<?= $v('summary_vi') ?>"></label>
        <label class="fld">요약 (EN)<input type="text" name="summary_en" value="<?= $v('summary_en') ?>"></label>
      </div>

      <label class="fld">상세설명 (KO)<textarea name="description_ko" rows="4"><?= $v('description_ko') ?></textarea></label>
      <div class="row">
        <label class="fld">상세 (VI)<textarea name="description_vi" rows="4"><?= $v('description_vi') ?></textarea></label>
        <label class="fld">상세 (EN)<textarea name="description_en" rows="4"><?= $v('description_en') ?></textarea></label>
      </div>

      <div class="row">
        <label class="fld">가격<input type="number" step="0.01" name="price" value="<?= $v('price') ?: '0' ?>"></label>
        <label class="fld">정렬<input type="number" name="sort" value="<?= $v('sort') ?: '0' ?>"></label>
      </div>

      <div class="checks">
        <label class="chk"><input type="checkbox" name="is_active" <?= $activeChk ?>> 노출</label>
        <label class="chk"><input type="checkbox" name="is_best" <?= $bestChk ?>> 베스트셀러</label>
      </div>

      <?php if (!empty($stores)): ?>
      <div class="fld">구매처 연결
        <div class="store-checks">
          <?php foreach ($stores as $s): ?>
            <label class="chk"><input type="checkbox" name="stores[]" value="<?= (int)$s['id'] ?>" <?= in_array($s['id'], $linkedStores ?? []) ? 'checked' : '' ?>>
              <?= Helpers::e($s['name']) ?> <span class="muted">(<?= Helpers::e($s['type']) ?>)</span></label>
          <?php endforeach; ?>
        </div>
        <span class="hint">제품 상세 페이지의 "구매처"에 표시됩니다.</span>
      </div>
      <?php endif; ?>

      <label class="fld">이미지 추가 (여러 장 선택 가능)
        <input type="file" name="images[]" accept="image/*" multiple>
        <span class="hint">jpg/png/gif/webp · 저장 후 아래에 표시됩니다.</span>
      </label>

      <div class="form-actions">
        <button class="btn" type="submit"><?= $isEdit ? '수정 저장' : '등록' ?></button>
        <a class="btn ghost" href="/admin/products">목록</a>
      </div>
    </form>
  </div>
</div>

<?php if ($isEdit): ?>
<div class="panel">
  <div class="panel-head">등록된 이미지 (<?= count($images) ?>)</div>
  <div class="panel-body">
    <?php if (!$images): ?>
      <p class="muted">아직 이미지가 없습니다. 위에서 추가하세요.</p>
    <?php else: ?>
      <div class="gallery">
        <?php foreach ($images as $img): ?>
          <figure class="gitem">
            <img src="<?= Helpers::e($img['path']) ?>" alt="">
            <?php if (Rbac::can('products.edit')): ?>
              <form method="post" action="/admin/products/<?= (int)$item['id'] ?>/images/<?= (int)$img['id'] ?>/delete" onsubmit="return confirm('이미지를 삭제하시겠습니까?');">
                <?= Csrf::field() ?><button class="btn-sm danger" type="submit">삭제</button>
              </form>
            <?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
