<?php
use App\Helpers;
use App\Csrf;
$v = fn(string $k) => Helpers::e($item[$k] ?? '');
$checked = (!($item ?? []) || !empty($item['is_active'])) ? 'checked' : '';
$curParent = $item['parent_id'] ?? '';
?>
<div class="panel">
  <div class="panel-head"><?= $isEdit ? '카테고리 수정' : '카테고리 등록' ?></div>
  <div class="panel-body">
    <form method="post" action="<?= Helpers::e($action) ?>" class="form">
      <?= Csrf::field() ?>
      <div class="row">
        <label class="fld">이름 (KO) <span class="req">*</span><input type="text" name="name_ko" value="<?= $v('name_ko') ?>" required></label>
        <label class="fld">정렬<input type="number" name="sort" value="<?= $v('sort') ?: '0' ?>"></label>
      </div>
      <div class="row">
        <label class="fld">이름 (VI)<input type="text" name="name_vi" value="<?= $v('name_vi') ?>"></label>
        <label class="fld">이름 (EN)<input type="text" name="name_en" value="<?= $v('name_en') ?>"></label>
      </div>
      <label class="fld">상위 카테고리
        <select name="parent_id">
          <option value="">— 없음 (최상위) —</option>
          <?php foreach ($parents as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= ((string)$curParent === (string)$p['id']) ? 'selected' : '' ?>><?= Helpers::e($p['name_ko']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="chk"><input type="checkbox" name="is_active" <?= $checked ?>> 노출</label>
      <div class="form-actions">
        <button class="btn" type="submit"><?= $isEdit ? '수정 저장' : '등록' ?></button>
        <a class="btn ghost" href="/admin/categories">취소</a>
      </div>
    </form>
  </div>
</div>
