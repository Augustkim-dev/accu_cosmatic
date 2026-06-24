<?php
use App\Helpers;
use App\Lang;
use App\Settings;

/** 제품 카드 출력 헬퍼 */
$card = function (array $p) {
    $name = Lang::pick($p, 'name');
    $brand = Lang::pick(['name_ko'=>$p['brand_name_ko']??'','name_vi'=>$p['brand_name_vi']??'','name_en'=>$p['brand_name_en']??''], 'name');
    $thumb = $p['thumb'] ?: '/assets/img/placeholder.svg';
    $cur = Settings::get('currency', 'VND');
    ob_start(); ?>
    <a class="pcard" href="/products/<?= (int)$p['id'] ?>">
      <div class="pcard-img" style="background-image:url('<?= Helpers::e($thumb) ?>')"></div>
      <div class="pcard-body">
        <?php if ($brand): ?><div class="pcard-brand"><?= Helpers::e($brand) ?></div><?php endif; ?>
        <div class="pcard-name"><?= Helpers::e($name) ?></div>
        <?php if ((float)$p['price'] > 0): ?>
          <div class="pcard-price"><?= number_format((float)$p['price']) ?> <?= Helpers::e($cur) ?></div>
        <?php endif; ?>
      </div>
    </a>
    <?php return ob_get_clean();
};
?>
<section class="hero">
  <div class="wrap">
    <h1><?= Helpers::e(Settings::get('site_name', 'ACCU Cosmetic')) ?></h1>
    <p>K-Beauty · <?= Helpers::e(Lang::t('all_products')) ?></p>
    <a class="hero-btn" href="/products"><?= Helpers::e(Lang::t('all_products')) ?> →</a>
  </div>
</section>

<?php if ($brands): ?>
<section class="wrap block">
  <h2><?= Helpers::e(Lang::t('brands')) ?></h2>
  <div class="brand-strip">
    <?php foreach ($brands as $b): ?>
      <a class="brand-chip" href="/products?brand=<?= (int)$b['id'] ?>">
        <?php if ($b['logo_path']): ?><img src="<?= Helpers::e($b['logo_path']) ?>" alt=""><?php endif; ?>
        <span><?= Helpers::e(Lang::pick($b, 'name')) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($best): ?>
<section class="wrap block">
  <h2><?= Helpers::e(Lang::t('best_sellers')) ?></h2>
  <div class="grid"><?php foreach ($best as $p) echo $card($p); ?></div>
</section>
<?php endif; ?>

<?php if ($latest): ?>
<section class="wrap block">
  <h2><?= Helpers::e(Lang::t('new_arrivals')) ?></h2>
  <div class="grid"><?php foreach ($latest as $p) echo $card($p); ?></div>
</section>
<?php endif; ?>

<?php if (!$brands && !$best && !$latest): ?>
<section class="wrap block"><p class="muted"><?= Helpers::e(Lang::t('no_products')) ?></p></section>
<?php endif; ?>
