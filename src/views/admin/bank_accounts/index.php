<?php
use App\Helpers;
use App\Rbac;
use App\Csrf;
?>
<div class="toolbar">
  <div class="muted"><?= count($rows) ?>개 계좌</div>
  <?php if (Rbac::can('settings.edit')): ?><a class="btn" href="/admin/bank-accounts/create">+ 계좌 등록</a><?php endif; ?>
</div>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>정렬</th><th>은행</th><th>계좌번호</th><th>예금주</th><th>활성</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="6" class="empty">등록된 계좌가 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $b): ?>
      <tr>
        <td><?= (int)$b['sort'] ?></td>
        <td><?= Helpers::e($b['bank_name']) ?></td>
        <td><code><?= Helpers::e($b['account_no']) ?></code></td>
        <td><?= Helpers::e($b['holder']) ?></td>
        <td><?= $b['is_active'] ? '<span class="badge on">활성</span>' : '<span class="badge off">비활성</span>' ?></td>
        <td class="ta-r">
          <?php if (Rbac::can('settings.edit')): ?>
            <a class="btn-sm" href="/admin/bank-accounts/<?= (int)$b['id'] ?>/edit">수정</a>
            <form class="inline" method="post" action="/admin/bank-accounts/<?= (int)$b['id'] ?>/delete" onsubmit="return confirm('삭제하시겠습니까?');">
              <?= Csrf::field() ?><button class="btn-sm danger">삭제</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
