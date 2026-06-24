<?php use App\Helpers; use App\Rbac; use App\Csrf; ?>
<div class="toolbar">
  <div class="muted"><?= count($rows) ?>개 구매처</div>
  <?php if (Rbac::can('stores.create')): ?><a class="btn" href="/admin/stores/create">+ 구매처 등록</a><?php endif; ?>
</div>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>정렬</th><th>유형</th><th>이름</th><th>지역</th><th>링크</th><th>활성</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="7" class="empty">등록된 구매처가 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $s): ?>
      <tr>
        <td><?= (int)$s['sort'] ?></td>
        <td><span class="tag"><?= Helpers::e($s['type']) ?></span></td>
        <td><?= Helpers::e($s['name']) ?></td>
        <td><?= Helpers::e($s['region'] ?? '—') ?></td>
        <td><?php if ($s['url']): ?><a href="<?= Helpers::e($s['url']) ?>" target="_blank" rel="noopener">링크</a><?php else: ?>—<?php endif; ?></td>
        <td><?= $s['is_active'] ? '<span class="badge on">활성</span>' : '<span class="badge off">비활성</span>' ?></td>
        <td class="ta-r">
          <?php if (Rbac::can('stores.edit')): ?><a class="btn-sm" href="/admin/stores/<?= (int)$s['id'] ?>/edit">수정</a><?php endif; ?>
          <?php if (Rbac::can('stores.delete')): ?>
            <form class="inline" method="post" action="/admin/stores/<?= (int)$s['id'] ?>/delete" onsubmit="return confirm('삭제?');"><?= Csrf::field() ?><button class="btn-sm danger">삭제</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
