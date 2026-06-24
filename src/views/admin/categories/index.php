<?php
use App\Helpers;
use App\Rbac;
use App\Csrf;
?>
<div class="toolbar">
  <div class="muted"><?= count($cats) ?>개 카테고리</div>
  <?php if (Rbac::can('categories.create')): ?>
    <a class="btn" href="/admin/categories/create">+ 카테고리 등록</a>
  <?php endif; ?>
</div>

<div class="panel">
  <table class="tbl">
    <thead><tr><th>정렬</th><th>이름(KO)</th><th>상위</th><th>노출</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php if (!$cats): ?><tr><td colspan="5" class="empty">등록된 카테고리가 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($cats as $c): ?>
      <tr>
        <td><?= (int)$c['sort'] ?></td>
        <td><?= Helpers::e($c['name_ko']) ?></td>
        <td><?= Helpers::e($c['parent_name'] ?? '—') ?></td>
        <td><?= $c['is_active'] ? '<span class="badge on">노출</span>' : '<span class="badge off">숨김</span>' ?></td>
        <td class="ta-r">
          <?php if (Rbac::can('categories.edit')): ?>
            <a class="btn-sm" href="/admin/categories/<?= (int)$c['id'] ?>/edit">수정</a>
          <?php endif; ?>
          <?php if (Rbac::can('categories.delete')): ?>
            <form class="inline" method="post" action="/admin/categories/<?= (int)$c['id'] ?>/delete" onsubmit="return confirm('삭제하시겠습니까?');">
              <?= Csrf::field() ?><button class="btn-sm danger">삭제</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
