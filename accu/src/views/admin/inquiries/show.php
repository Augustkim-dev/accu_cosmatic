<?php use App\Helpers; use App\Csrf; use App\Rbac; ?>
<a class="back" href="/admin/inquiries">← 목록</a>
<div class="panel">
  <div class="panel-head">문의 #<?= (int)$q['id'] ?></div>
  <div class="panel-body">
    <p><strong><?= Helpers::e($q['name']) ?></strong> · <?= Helpers::e($q['contact']) ?> · <span class="muted"><?= Helpers::e($q['created_at']) ?></span></p>
    <div class="card-q"><?= nl2br(Helpers::e($q['message'])) ?></div>
    <?php if (Rbac::can('inquiries.reply')): ?>
      <form method="post" action="/admin/inquiries/<?= (int)$q['id'] ?>/reply" class="form" style="margin-top:18px">
        <?= Csrf::field() ?>
        <label class="fld">답변/메모<textarea name="reply" rows="4"><?= Helpers::e($q['reply'] ?? '') ?></textarea></label>
        <label class="fld">상태
          <select name="status">
            <option value="received" <?= $q['status']==='received'?'selected':'' ?>>접수</option>
            <option value="processing" <?= $q['status']==='processing'?'selected':'' ?>>처리중</option>
            <option value="done" <?= $q['status']==='done'?'selected':'' ?>>완료</option>
          </select>
        </label>
        <div class="form-actions"><button class="btn" type="submit">저장</button></div>
      </form>
    <?php else: ?>
      <p class="muted" style="margin-top:14px"><?= $q['reply'] ? nl2br(Helpers::e($q['reply'])) : '답변 권한이 없습니다.' ?></p>
    <?php endif; ?>
    <?php if (Rbac::can('inquiries.delete')): ?>
      <form method="post" action="/admin/inquiries/<?= (int)$q['id'] ?>/delete" onsubmit="return confirm('삭제?');" style="margin-top:12px">
        <?= Csrf::field() ?><button class="btn-sm danger" type="submit">삭제</button>
      </form>
    <?php endif; ?>
  </div>
</div>
