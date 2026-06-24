<?php
use App\Helpers;
use App\Csrf;
?>
<div class="panel" style="max-width:520px">
  <div class="panel-head">회원 수정</div>
  <div class="panel-body">
    <form method="post" action="/admin/members/<?= (int)$m['id'] ?>" class="form">
      <?= Csrf::field() ?>
      <label class="fld">이메일<input type="text" value="<?= Helpers::e($m['email']) ?>" disabled></label>
      <label class="fld">이름<input type="text" name="name" value="<?= Helpers::e($m['name']) ?>"></label>
      <label class="fld">연락처<input type="text" name="phone" value="<?= Helpers::e($m['phone'] ?? '') ?>"></label>
      <label class="fld">상태
        <select name="status">
          <option value="active" <?= $m['status']==='active'?'selected':'' ?>>활성</option>
          <option value="suspended" <?= $m['status']==='suspended'?'selected':'' ?>>정지</option>
        </select>
      </label>
      <div class="form-actions">
        <button class="btn" type="submit">저장</button>
        <a class="btn ghost" href="/admin/members">취소</a>
      </div>
    </form>
  </div>
</div>
