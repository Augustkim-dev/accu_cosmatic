<?php
use App\Helpers;
use App\Csrf;
$v = fn(string $k) => Helpers::e($item[$k] ?? '');
$checked = (!($item ?? []) || !empty($item['is_active'])) ? 'checked' : '';
?>
<div class="panel" style="max-width:520px">
  <div class="panel-head"><?= $isEdit ? '계좌 수정' : '계좌 등록' ?></div>
  <div class="panel-body">
    <form method="post" action="<?= Helpers::e($action) ?>" class="form">
      <?= Csrf::field() ?>
      <label class="fld">은행명 <span class="req">*</span><input type="text" name="bank_name" value="<?= $v('bank_name') ?>" required></label>
      <label class="fld">계좌번호 <span class="req">*</span><input type="text" name="account_no" value="<?= $v('account_no') ?>" required></label>
      <label class="fld">예금주<input type="text" name="holder" value="<?= $v('holder') ?>"></label>
      <label class="fld">정렬<input type="number" name="sort" value="<?= $v('sort') ?: '0' ?>"></label>
      <label class="chk"><input type="checkbox" name="is_active" <?= $checked ?>> 활성</label>
      <div class="form-actions">
        <button class="btn" type="submit"><?= $isEdit ? '저장' : '등록' ?></button>
        <a class="btn ghost" href="/admin/bank-accounts">취소</a>
      </div>
    </form>
  </div>
</div>
