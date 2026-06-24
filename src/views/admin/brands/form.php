<?php
use App\Helpers;
use App\Csrf;
$v = fn(string $k) => Helpers::e($item[$k] ?? '');
$checked = (!($item ?? []) || !empty($item['is_active'])) ? 'checked' : '';  // 신규는 기본 노출
?>
<div class="panel">
  <div class="panel-head"><?= $isEdit ? '브랜드 수정' : '브랜드 등록' ?></div>
  <div class="panel-body">
    <form method="post" action="<?= Helpers::e($action) ?>" enctype="multipart/form-data" class="form">
      <?= Csrf::field() ?>

      <div class="row">
        <label class="fld">코드 <span class="req">*</span>
          <input type="text" name="code" value="<?= $v('code') ?>" required placeholder="bowdon">
        </label>
        <label class="fld">정렬
          <input type="number" name="sort" value="<?= $v('sort') ?: '0' ?>">
        </label>
      </div>

      <div class="row">
        <label class="fld">이름 (KO) <span class="req">*</span><input type="text" name="name_ko" value="<?= $v('name_ko') ?>" required></label>
        <label class="fld">이름 (VI)<input type="text" name="name_vi" value="<?= $v('name_vi') ?>"></label>
        <label class="fld">이름 (EN)<input type="text" name="name_en" value="<?= $v('name_en') ?>"></label>
      </div>

      <label class="fld">브랜드 스토리 (KO)<textarea name="story_ko" rows="3"><?= $v('story_ko') ?></textarea></label>
      <div class="row">
        <label class="fld">스토리 (VI)<textarea name="story_vi" rows="3"><?= $v('story_vi') ?></textarea></label>
        <label class="fld">스토리 (EN)<textarea name="story_en" rows="3"><?= $v('story_en') ?></textarea></label>
      </div>

      <label class="fld">로고 이미지
        <input type="file" name="logo" accept="image/*">
        <?php if (!empty($item['logo_path'])): ?>
          <span class="hint">현재: <img class="thumb" src="<?= Helpers::e($item['logo_path']) ?>" alt=""> (새로 올리면 교체)</span>
        <?php endif; ?>
      </label>

      <label class="chk"><input type="checkbox" name="is_active" <?= $checked ?>> 노출</label>

      <div class="form-actions">
        <button class="btn" type="submit"><?= $isEdit ? '수정 저장' : '등록' ?></button>
        <a class="btn ghost" href="/admin/brands">취소</a>
      </div>
    </form>
  </div>
</div>
