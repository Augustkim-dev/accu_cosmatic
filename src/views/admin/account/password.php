<?php
use App\Helpers;
use App\Csrf;
?>
<div class="panel" style="max-width:480px">
  <div class="panel-head">비밀번호 변경</div>
  <div class="panel-body">
    <form method="post" action="/admin/account/password" class="form">
      <?= Csrf::field() ?>
      <label class="fld">현재 비밀번호 <span class="req">*</span>
        <input type="password" name="current" autocomplete="current-password" required>
      </label>
      <label class="fld">새 비밀번호 <span class="req">*</span>
        <input type="password" name="new" autocomplete="new-password" required>
        <span class="hint">10자 이상 권장</span>
      </label>
      <label class="fld">새 비밀번호 확인 <span class="req">*</span>
        <input type="password" name="confirm" autocomplete="new-password" required>
      </label>
      <div class="form-actions">
        <button class="btn" type="submit">변경</button>
        <a class="btn ghost" href="/admin">취소</a>
      </div>
    </form>
  </div>
</div>
