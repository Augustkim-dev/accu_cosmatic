<?php
use App\Helpers;
use App\Csrf;
use App\Lang;
use App\Rbac;
?>
<a class="back" href="/admin/orders">← 목록</a>

<div class="panel">
  <div class="panel-head">
    주문 <code><?= Helpers::e($o['order_no']) ?></code>
    <span class="ostat s-<?= Helpers::e($o['status']) ?>"><?= Helpers::e(Lang::t('st_' . $o['status'])) ?></span>
  </div>
  <div class="panel-body">
    <table class="tbl">
      <thead><tr><th>제품</th><th>단가</th><th>수량</th><th class="ta-r">금액</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr><td><?= Helpers::e($it['product_name']) ?></td><td><?= number_format((float)$it['price']) ?></td><td><?= (int)$it['qty'] ?></td><td class="ta-r"><?= number_format((float)$it['price'] * (int)$it['qty']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div class="sum-line">합계 <strong><?= number_format((float)$o['total']) ?></strong></div>
  </div>
</div>

<div class="cols">
  <div class="panel">
    <div class="panel-head">배송/주문 정보</div>
    <div class="panel-body">
      <p>받는분: <strong><?= Helpers::e($o['receiver_name']) ?></strong></p>
      <p>연락처: <?= Helpers::e($o['phone']) ?></p>
      <?php if ($o['address']): ?><p>주소: <?= Helpers::e($o['address']) ?></p><?php endif; ?>
      <p>입금자명: <?= Helpers::e($o['depositor_name'] ?: '—') ?></p>
      <?php if ($bank): ?><p>입금계좌: <?= Helpers::e($bank['bank_name'] . ' ' . $bank['account_no']) ?></p><?php endif; ?>
      <p>회원: <?= Helpers::e($member['email'] ?? '비회원') ?></p>
      <?php if ($o['paid_at']): ?><p>입금확인: <?= Helpers::e($o['paid_at']) ?></p><?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">처리</div>
    <div class="panel-body actions-col">
      <?php if ($o['status'] === 'pending' && Rbac::can('orders.confirm')): ?>
        <form method="post" action="/admin/orders/<?= (int)$o['id'] ?>/confirm">
          <?= Csrf::field() ?><button class="btn" type="submit">입금확인 (→ 결제완료)</button>
        </form>
      <?php endif; ?>

      <?php if (Rbac::can('orders.edit')): ?>
        <form method="post" action="/admin/orders/<?= (int)$o['id'] ?>/status" class="row-form">
          <?= Csrf::field() ?>
          <select name="status">
            <option value="paid" <?= $o['status']==='paid'?'selected':'' ?>>결제완료</option>
            <option value="shipping" <?= $o['status']==='shipping'?'selected':'' ?>>배송중</option>
            <option value="done" <?= $o['status']==='done'?'selected':'' ?>>배송완료</option>
          </select>
          <button class="btn ghost" type="submit">상태 변경</button>
        </form>
      <?php endif; ?>

      <?php if ($o['status'] !== 'cancelled' && Rbac::can('orders.cancel')): ?>
        <form method="post" action="/admin/orders/<?= (int)$o['id'] ?>/cancel" onsubmit="return confirm('주문을 취소하시겠습니까?');">
          <?= Csrf::field() ?><button class="btn-sm danger" type="submit">주문 취소</button>
        </form>
      <?php endif; ?>

      <?php if (!Rbac::can('orders.confirm') && !Rbac::can('orders.cancel') && !Rbac::can('orders.edit')): ?>
        <p class="muted">조회 권한만 있습니다.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
