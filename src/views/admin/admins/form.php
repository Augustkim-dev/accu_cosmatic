<?php use App\Helpers; use App\Csrf;
$v = fn(string $k) => Helpers::e($item[$k] ?? '');
$curRole = $item['role_id'] ?? '';
?>
<div class="panel" style="max-width:560px">
  <div class="panel-head"><?= $isEdit ? '관리자 수정' : '관리자 등록' ?></div>
  <div class="panel-body">
    <form method="post" action="<?= Helpers::e($action) ?>" class="form">
      <?= Csrf::field() ?>
      <label class="fld">이메일 <span class="req">*</span>
        <input type="email" name="email" value="<?= $v('email') ?>" <?= $isEdit ? 'readonly' : '' ?> required>
      </label>
      <label class="fld">이름 <span class="req">*</span><input type="text" name="name" value="<?= $v('name') ?>" required></label>
      <label class="fld">역할 <span class="req">*</span>
        <select name="role_id">
          <?php foreach ($roles as $r): ?>
            <option value="<?= (int)$r['id'] ?>" <?= ((string)$curRole === (string)$r['id']) ? 'selected' : '' ?>><?= Helpers::e($r['name_ko']) ?> (lv.<?= (int)$r['level'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php if ($isEdit): ?>
        <label class="fld">상태
          <select name="status">
            <option value="active" <?= ($item['status']??'')==='active'?'selected':'' ?>>활성</option>
            <option value="suspended" <?= ($item['status']??'')==='suspended'?'selected':'' ?>>정지</option>
          </select>
        </label>
        <label class="fld">새 비밀번호 <span class="muted">(변경 시에만 입력)</span>
          <input type="password" name="password" autocomplete="new-password">
          <span class="hint">10자 이상</span>
        </label>
      <?php else: ?>
        <label class="fld">비밀번호 <span class="req">*</span>
          <input type="password" name="password" autocomplete="new-password" required>
          <span class="hint">10자 이상</span>
        </label>
      <?php endif; ?>
      <div class="form-actions"><button class="btn" type="submit"><?= $isEdit ? '저장' : '등록' ?></button><a class="btn ghost" href="/admin/admins">취소</a></div>
    </form>
  </div>
</div>
