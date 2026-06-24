<?php use App\Helpers; use App\Csrf;
$v = fn(string $k) => Helpers::e($item[$k] ?? '');
$checked = (!($item ?? []) || !empty($item['is_active'])) ? 'checked' : '';
?>
<div class="panel">
  <div class="panel-head"><?= $isEdit ? '페이지 수정' : '페이지 등록' ?></div>
  <div class="panel-body">
    <form method="post" action="<?= Helpers::e($action) ?>" class="form">
      <?= Csrf::field() ?>
      <label class="fld">slug <span class="req">*</span>
        <input type="text" name="slug" value="<?= $v('slug') ?>" <?= $isEdit ? 'readonly' : '' ?> placeholder="about" required>
      </label>
      <div class="row">
        <label class="fld">제목 (KO) <span class="req">*</span><input type="text" name="title_ko" value="<?= $v('title_ko') ?>" required></label>
        <label class="fld">제목 (VI)<input type="text" name="title_vi" value="<?= $v('title_vi') ?>"></label>
        <label class="fld">제목 (EN)<input type="text" name="title_en" value="<?= $v('title_en') ?>"></label>
      </div>
      <label class="fld">본문 (KO)<textarea name="body_ko" rows="5"><?= $v('body_ko') ?></textarea></label>
      <div class="row">
        <label class="fld">본문 (VI)<textarea name="body_vi" rows="5"><?= $v('body_vi') ?></textarea></label>
        <label class="fld">본문 (EN)<textarea name="body_en" rows="5"><?= $v('body_en') ?></textarea></label>
      </div>
      <label class="chk"><input type="checkbox" name="is_active" <?= $checked ?>> 노출</label>
      <div class="form-actions"><button class="btn" type="submit"><?= $isEdit ? '저장' : '등록' ?></button><a class="btn ghost" href="/admin/pages">취소</a></div>
    </form>
  </div>
</div>
