<?php
use App\Helpers;
use App\Rbac;
?>
<div class="toolbar">
  <form method="get" action="/admin/members" class="search">
    <input type="text" name="q" value="<?= Helpers::e($q) ?>" placeholder="이메일/이름 검색">
    <button class="btn-sm" type="submit">검색</button>
  </form>
  <?php if (Rbac::can('members.export')): ?>
    <a class="btn" href="/admin/members/export">CSV 내보내기</a>
  <?php endif; ?>
</div>

<div class="panel">
  <table class="tbl">
    <thead><tr><th>이메일</th><th>이름</th><th>연락처</th><th>주문</th><th>상태</th><th>가입일</th><th class="ta-r"></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="7" class="empty">회원이 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $m): ?>
      <tr>
        <td><?= Helpers::e($m['email']) ?></td>
        <td><?= Helpers::e($m['name']) ?></td>
        <td><?= Helpers::e($m['phone'] ?? '—') ?></td>
        <td><?= (int)$m['orders'] ?></td>
        <td><?= $m['status'] === 'active' ? '<span class="badge on">활성</span>' : '<span class="badge off">정지</span>' ?></td>
        <td><?= Helpers::e(substr($m['created_at'], 0, 10)) ?></td>
        <td class="ta-r"><?php if (Rbac::can('members.edit')): ?><a class="btn-sm" href="/admin/members/<?= (int)$m['id'] ?>/edit">수정</a><?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
