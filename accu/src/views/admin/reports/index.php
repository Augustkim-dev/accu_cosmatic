<?php use App\Helpers; ?>
<div class="cards">
  <div class="card"><div class="card-label">회원</div><div class="card-num"><?= (int)$stats['members'] ?></div></div>
  <div class="card"><div class="card-label">제품</div><div class="card-num"><?= (int)$stats['products'] ?></div></div>
  <div class="card"><div class="card-label">총 주문</div><div class="card-num"><?= (int)$stats['orders'] ?></div></div>
  <div class="card"><div class="card-label">입금대기</div><div class="card-num"><?= (int)$stats['pending'] ?></div></div>
</div>
<div class="cards">
  <div class="card"><div class="card-label">유효주문(결제+)</div><div class="card-num"><?= (int)$stats['orders_paid'] ?></div></div>
  <div class="card" style="grid-column:span 3"><div class="card-label">매출 합계 (결제완료 이상)</div><div class="card-num"><?= number_format((float)$stats['revenue']) ?></div></div>
</div>

<div class="panel">
  <div class="panel-head">최근 14일 주문</div>
  <div class="panel-body">
    <?php if (!$daily): ?><p class="muted">데이터 없음</p><?php else: ?>
    <table class="tbl"><thead><tr><th>일자</th><th>주문수</th><th class="ta-r">금액</th></tr></thead><tbody>
      <?php foreach ($daily as $d): ?>
        <tr><td><?= Helpers::e($d['d']) ?></td><td><?= (int)$d['c'] ?></td><td class="ta-r"><?= number_format((float)$d['amt']) ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
  </div>
</div>

<div class="panel">
  <div class="panel-head">인기 제품 (주문 수량)</div>
  <div class="panel-body">
    <?php if (!$top): ?><p class="muted">데이터 없음</p><?php else: ?>
    <table class="tbl"><thead><tr><th>제품</th><th class="ta-r">수량</th></tr></thead><tbody>
      <?php foreach ($top as $t): ?><tr><td><?= Helpers::e($t['product_name']) ?></td><td class="ta-r"><?= (int)$t['q'] ?></td></tr><?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
  </div>
</div>
