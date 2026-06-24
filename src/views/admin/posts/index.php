<?php use App\Helpers; use App\Rbac; use App\Csrf;
$permBase = str_contains($base, 'projects') ? 'projects' : 'posts';
?>
<div class="toolbar">
  <div class="muted"><?= count($rows) ?>개</div>
  <?php if (Rbac::can($permBase.'.create')): ?><a class="btn" href="<?= Helpers::e($base) ?>/create">+ 등록</a><?php endif; ?>
</div>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>제목(KO)</th><th>노출</th><th>게시일</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="4" class="empty">항목이 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $p): ?>
      <tr>
        <td><?= Helpers::e($p['title_ko']) ?></td>
        <td><?= $p['is_active'] ? '<span class="badge on">노출</span>' : '<span class="badge off">숨김</span>' ?></td>
        <td><?= Helpers::e(substr($p['published_at'] ?? $p['created_at'],0,10)) ?></td>
        <td class="ta-r">
          <?php if (Rbac::can($permBase.'.edit')): ?><a class="btn-sm" href="<?= Helpers::e($base) ?>/<?= (int)$p['id'] ?>/edit">수정</a><?php endif; ?>
          <?php if (Rbac::can($permBase.'.delete')): ?>
            <form class="inline" method="post" action="<?= Helpers::e($base) ?>/<?= (int)$p['id'] ?>/delete" onsubmit="return confirm('삭제?');"><?= Csrf::field() ?><button class="btn-sm danger">삭제</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
