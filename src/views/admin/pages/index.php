<?php use App\Helpers; use App\Rbac; use App\Csrf; ?>
<div class="toolbar">
  <div class="muted"><?= count($rows) ?>개 페이지</div>
  <?php if (Rbac::can('pages.edit')): ?><a class="btn" href="/admin/pages/create">+ 페이지 등록</a><?php endif; ?>
</div>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>slug</th><th>제목(KO)</th><th>노출</th><th>미리보기</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="5" class="empty">페이지가 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $p): ?>
      <tr>
        <td><code><?= Helpers::e($p['slug']) ?></code></td>
        <td><?= Helpers::e($p['title_ko']) ?></td>
        <td><?= $p['is_active'] ? '<span class="badge on">노출</span>' : '<span class="badge off">숨김</span>' ?></td>
        <td><a href="/page/<?= Helpers::e($p['slug']) ?>" target="_blank" rel="noopener">/page/<?= Helpers::e($p['slug']) ?></a></td>
        <td class="ta-r">
          <?php if (Rbac::can('pages.edit')): ?>
            <a class="btn-sm" href="/admin/pages/<?= (int)$p['id'] ?>/edit">수정</a>
            <form class="inline" method="post" action="/admin/pages/<?= (int)$p['id'] ?>/delete" onsubmit="return confirm('삭제?');"><?= Csrf::field() ?><button class="btn-sm danger">삭제</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
