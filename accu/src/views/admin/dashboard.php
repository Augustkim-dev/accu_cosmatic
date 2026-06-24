<?php
use App\Helpers;
?>
<div class="cards">
  <div class="card"><div class="card-label">제품</div><div class="card-num"><?= (int)$stats['products'] ?></div></div>
  <div class="card"><div class="card-label">회원</div><div class="card-num"><?= (int)$stats['members'] ?></div></div>
  <div class="card"><div class="card-label">주문</div><div class="card-num"><?= (int)$stats['orders'] ?></div></div>
  <div class="card"><div class="card-label">문의</div><div class="card-num"><?= (int)$stats['inquiries'] ?></div></div>
</div>

<div class="panel">
  <div class="panel-head">내 권한</div>
  <div class="panel-body">
    <p class="muted">현재 역할: <strong><?= Helpers::e(App\Auth::user()['role_name']) ?></strong>
      (level <?= (int)App\Auth::user()['level'] ?>) — 좌측 메뉴는 보유 권한에 따라 자동으로 노출됩니다.</p>
  </div>
</div>

<?php if (!empty($logs)): ?>
<div class="panel">
  <div class="panel-head">최근 활동 로그</div>
  <div class="panel-body">
    <table class="tbl">
      <thead><tr><th>시각</th><th>관리자</th><th>동작</th><th>대상</th><th>IP</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?= Helpers::e($l['created_at']) ?></td>
          <td><?= Helpers::e($l['email'] ?? '-') ?></td>
          <td><span class="tag"><?= Helpers::e($l['action']) ?></span></td>
          <td><?= Helpers::e($l['target'] ?? '-') ?></td>
          <td><?= Helpers::e($l['ip'] ?? '-') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
