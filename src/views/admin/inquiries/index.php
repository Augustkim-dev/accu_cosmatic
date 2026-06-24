<?php use App\Helpers; ?>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>이름</th><th>연락처</th><th>내용</th><th>상태</th><th>접수일</th><th class="ta-r"></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="6" class="empty">문의가 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $q):
      $st = ['received'=>'접수','processing'=>'처리중','done'=>'완료'][$q['status']] ?? $q['status']; ?>
      <tr>
        <td><?= Helpers::e($q['name']) ?></td>
        <td><?= Helpers::e($q['contact']) ?></td>
        <td><?= Helpers::e(mb_strimwidth($q['message'], 0, 40, '…')) ?></td>
        <td><span class="badge <?= $q['status']==='done'?'on':'off' ?>"><?= Helpers::e($st) ?></span></td>
        <td><?= Helpers::e(substr($q['created_at'],0,16)) ?></td>
        <td class="ta-r"><a class="btn-sm" href="/admin/inquiries/<?= (int)$q['id'] ?>">상세</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
