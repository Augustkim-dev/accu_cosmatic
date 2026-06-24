<?php
use App\Helpers;
use App\Rbac;
use App\Csrf;
?>
<div class="toolbar">
  <div class="muted"><?= count($brands) ?>개 브랜드</div>
  <?php if (Rbac::can('brands.create')): ?>
    <a class="btn" href="/admin/brands/create">+ 브랜드 등록</a>
  <?php endif; ?>
</div>

<div class="panel">
  <table class="tbl">
    <thead><tr><th>정렬</th><th>로고</th><th>코드</th><th>이름(KO)</th><th>노출</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php if (!$brands): ?>
      <tr><td colspan="6" class="empty">등록된 브랜드가 없습니다.</td></tr>
    <?php endif; ?>
    <?php foreach ($brands as $b): ?>
      <tr>
        <td><?= (int)$b['sort'] ?></td>
        <td><?php if ($b['logo_path']): ?><img class="thumb" src="<?= Helpers::e($b['logo_path']) ?>" alt=""><?php else: ?><span class="muted">—</span><?php endif; ?></td>
        <td><code><?= Helpers::e($b['code']) ?></code></td>
        <td><?= Helpers::e($b['name_ko']) ?></td>
        <td><?= $b['is_active'] ? '<span class="badge on">노출</span>' : '<span class="badge off">숨김</span>' ?></td>
        <td class="ta-r">
          <?php if (Rbac::can('brands.edit')): ?>
            <a class="btn-sm" href="/admin/brands/<?= (int)$b['id'] ?>/edit">수정</a>
          <?php endif; ?>
          <?php if (Rbac::can('brands.delete')): ?>
            <form class="inline" method="post" action="/admin/brands/<?= (int)$b['id'] ?>/delete" onsubmit="return confirm('삭제하시겠습니까?');">
              <?= Csrf::field() ?>
              <button class="btn-sm danger" type="submit">삭제</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
