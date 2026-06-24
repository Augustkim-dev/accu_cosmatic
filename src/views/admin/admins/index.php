<?php use App\Helpers; use App\Rbac; use App\Csrf; use App\Auth;
$meId = (int)Auth::user()['id'];
?>
<div class="toolbar">
  <div class="muted"><?= count($rows) ?>명</div>
  <?php if (Rbac::can('admins.create')): ?><a class="btn" href="/admin/admins/create">+ 관리자 등록</a><?php endif; ?>
</div>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>이메일</th><th>이름</th><th>역할</th><th>상태</th><th>최근 로그인</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $a): ?>
      <tr>
        <td><?= Helpers::e($a['email']) ?><?= $a['id']==$meId ? ' <span class="tag">나</span>' : '' ?></td>
        <td><?= Helpers::e($a['name']) ?></td>
        <td><span class="role role-<?= Helpers::e($a['role_code']) ?>"><?= Helpers::e($a['role_name']) ?></span></td>
        <td><?= $a['status']==='active' ? '<span class="badge on">활성</span>' : '<span class="badge off">정지</span>' ?></td>
        <td><?= Helpers::e($a['last_login_at'] ?? '—') ?></td>
        <td class="ta-r">
          <?php if (Rbac::can('admins.edit')): ?><a class="btn-sm" href="/admin/admins/<?= (int)$a['id'] ?>/edit">수정</a><?php endif; ?>
          <?php if (Rbac::can('admins.delete') && $a['id'] != $meId): ?>
            <form class="inline" method="post" action="/admin/admins/<?= (int)$a['id'] ?>/delete" onsubmit="return confirm('삭제하시겠습니까?');"><?= Csrf::field() ?><button class="btn-sm danger">삭제</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
