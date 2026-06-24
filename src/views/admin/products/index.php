<?php
use App\Helpers;
use App\Rbac;
use App\Csrf;
?>
<div class="toolbar">
  <div class="muted">총 <?= (int)$total ?>개 제품</div>
  <?php if (Rbac::can('products.create')): ?>
    <a class="btn" href="/admin/products/create">+ 제품 등록</a>
  <?php endif; ?>
</div>

<div class="panel">
  <table class="tbl">
    <thead><tr><th>이미지</th><th>제품명(KO)</th><th>브랜드</th><th>가격</th><th>노출</th><th>베스트</th><th class="ta-r">관리</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="7" class="empty">등록된 제품이 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $p): ?>
      <tr>
        <td><?php if (!empty($p['thumb'])): ?><img class="thumb" src="<?= Helpers::e($p['thumb']) ?>" alt=""><?php else: ?><span class="muted">—</span><?php endif; ?></td>
        <td><?= Helpers::e($p['name_ko']) ?></td>
        <td><?= Helpers::e($p['brand_name'] ?? '—') ?></td>
        <td><?= number_format((float)$p['price']) ?></td>
        <td><?= $p['is_active'] ? '<span class="badge on">노출</span>' : '<span class="badge off">숨김</span>' ?></td>
        <td><?= $p['is_best'] ? '<span class="badge best">BEST</span>' : '' ?></td>
        <td class="ta-r">
          <?php if (Rbac::can('products.edit')): ?>
            <a class="btn-sm" href="/admin/products/<?= (int)$p['id'] ?>/edit">수정</a>
          <?php endif; ?>
          <?php if (Rbac::can('products.delete')): ?>
            <form class="inline" method="post" action="/admin/products/<?= (int)$p['id'] ?>/delete" onsubmit="return confirm('삭제하시겠습니까?');">
              <?= Csrf::field() ?><button class="btn-sm danger">삭제</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($pages > 1): ?>
<div class="pager">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a class="<?= $i === $page ? 'on' : '' ?>" href="/admin/products?page=<?= $i ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
