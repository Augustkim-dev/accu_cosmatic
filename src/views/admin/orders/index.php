<?php
use App\Helpers;
use App\Lang;
use App\Rbac;
$statuses = ['' => '전체', 'pending' => '입금대기', 'paid' => '결제완료', 'shipping' => '배송중', 'done' => '배송완료', 'cancelled' => '취소'];
?>
<div class="toolbar">
  <div class="filters">
    <?php foreach ($statuses as $k => $label): ?>
      <a class="chip <?= $status === $k ? 'on' : '' ?>" href="/admin/orders<?= $k ? '?status=' . $k : '' ?>"><?= Helpers::e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <?php if (Rbac::can('orders.export')): ?>
    <a class="btn" href="/admin/orders/export">CSV 내보내기</a>
  <?php endif; ?>
</div>

<div class="panel">
  <table class="tbl">
    <thead><tr><th>주문번호</th><th>받는분</th><th>회원</th><th>금액</th><th>상태</th><th>주문일</th><th class="ta-r"></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="7" class="empty">주문이 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $o): ?>
      <tr>
        <td><code><?= Helpers::e($o['order_no']) ?></code></td>
        <td><?= Helpers::e($o['receiver_name']) ?></td>
        <td><?= Helpers::e($o['member_email'] ?? '비회원') ?></td>
        <td><?= number_format((float)$o['total']) ?></td>
        <td><span class="ostat s-<?= Helpers::e($o['status']) ?>"><?= Helpers::e(Lang::t('st_' . $o['status'])) ?></span></td>
        <td><?= Helpers::e(substr($o['created_at'], 0, 16)) ?></td>
        <td class="ta-r"><a class="btn-sm" href="/admin/orders/<?= (int)$o['id'] ?>">상세</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
