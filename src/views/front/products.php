<?php
use App\Helpers;
use App\Lang;
use App\Settings;

$cur = Settings::get('currency', 'VND');
$card = function (array $p) use ($cur) {
    $name  = Lang::pick($p, 'name');
    $brand = Lang::pick(['name_ko'=>$p['brand_name_ko']??'','name_vi'=>$p['brand_name_vi']??'','name_en'=>$p['brand_name_en']??''], 'name');
    $thumb = $p['thumb'] ?: '/assets/img/placeholder.svg';
    ob_start(); ?>
    <a class="pcard" href="/products/<?= (int)$p['id'] ?>">
      <div class="pcard-img" style="background-image:url('<?= Helpers::e($thumb) ?>')"></div>
      <div class="pcard-body">
        <?php if ($brand): ?><div class="pcard-brand"><?= Helpers::e($brand) ?></div><?php endif; ?>
        <div class="pcard-name"><?= Helpers::e($name) ?></div>
        <?php if ((float)$p['price'] > 0): ?><div class="pcard-price"><?= number_format((float)$p['price']) ?> <?= Helpers::e($cur) ?></div><?php endif; ?>
      </div>
    </a>
    <?php return ob_get_clean();
};
?>
<section class="wrap block">
  <h1 class="page-h"><?= Helpers::e($heading) ?> <span class="muted">(<?= (int)$total ?>)</span></h1>

  <?php if (!$rows): ?>
    <p class="muted"><?= Helpers::e(Lang::t('no_products')) ?></p>
  <?php else: ?>
    <div class="grid"><?php foreach ($rows as $p) echo $card($p); ?></div>
  <?php endif; ?>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php
        $base = '/products?';
        $qs = [];
        if ($brandId) $qs[] = 'brand=' . $brandId;
        if ($catId)   $qs[] = 'category=' . $catId;
        $prefix = $base . ($qs ? implode('&', $qs) . '&' : '');
        for ($i = 1; $i <= $pages; $i++): ?>
        <a class="<?= $i === $page ? 'on' : '' ?>" href="<?= Helpers::e($prefix . 'page=' . $i) ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</section>
