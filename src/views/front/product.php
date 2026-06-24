<?php
use App\Helpers;
use App\Lang;
use App\Settings;

$name  = Lang::pick($p, 'name');
$summary = Lang::pick($p, 'summary');
$desc  = Lang::pick($p, 'description');
$brand = Lang::pick(['name_ko'=>$p['brand_name_ko']??'','name_vi'=>$p['brand_name_vi']??'','name_en'=>$p['brand_name_en']??''], 'name');
$cur   = Settings::get('currency', 'VND');
$main  = $images[0]['path'] ?? '/assets/img/placeholder.svg';
$email = Settings::get('contact_email', '');
$phone = Settings::get('contact_phone', '');
?>
<section class="wrap block">
  <nav class="crumb">
    <a href="/"><?= Helpers::e(Lang::t('home')) ?></a> ›
    <a href="/products"><?= Helpers::e(Lang::t('products')) ?></a>
    <?php if ($brand): ?> › <a href="/products?brand=<?= (int)$p['brand_id'] ?>"><?= Helpers::e($brand) ?></a><?php endif; ?>
  </nav>

  <div class="detail">
    <div class="detail-gallery">
      <div class="big" id="mainImg" style="background-image:url('<?= Helpers::e($main) ?>')"></div>
      <?php if (count($images) > 1): ?>
        <div class="thumbs">
          <?php foreach ($images as $img): ?>
            <button type="button" class="t" data-src="<?= Helpers::e($img['path']) ?>"
              style="background-image:url('<?= Helpers::e($img['path']) ?>')"></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="detail-info">
      <?php if ($brand): ?><div class="d-brand"><?= Helpers::e($brand) ?></div><?php endif; ?>
      <h1 class="d-name"><?= Helpers::e($name) ?></h1>
      <?php if ($summary): ?><p class="d-summary"><?= Helpers::e($summary) ?></p><?php endif; ?>
      <?php if ((float)$p['price'] > 0): ?>
        <div class="d-price"><?= number_format((float)$p['price']) ?> <?= Helpers::e($cur) ?></div>
      <?php endif; ?>

      <div class="d-buy">
        <form method="post" action="/cart/add" class="addcart">
          <?= \App\Csrf::field() ?>
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <input type="number" name="qty" value="1" min="1" class="qty-in">
          <button type="submit" class="hero-btn"><?= Helpers::e(Lang::t('add_to_cart')) ?></button>
        </form>
        <?php if ($email): ?>
          <a class="inq-link" href="mailto:<?= Helpers::e($email) ?>?subject=<?= rawurlencode(Lang::t('inquiry') . ': ' . $name) ?>"><?= Helpers::e(Lang::t('inquiry')) ?></a>
        <?php endif; ?>
        <div class="d-contact muted">
          <?php if ($phone): ?><?= Helpers::e(Lang::t('contact')) ?>: <?= Helpers::e($phone) ?><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($stores)): ?>
    <div class="d-desc">
      <h2><?= Helpers::e(Lang::t('where_to_buy')) ?></h2>
      <div class="store-list">
        <?php foreach ($stores as $s): ?>
          <?php if ($s['url']): ?>
            <a class="store-item" href="<?= Helpers::e($s['url']) ?>" target="_blank" rel="noopener">
              <span class="store-type s-<?= Helpers::e($s['type']) ?>"><?= Helpers::e(strtoupper($s['type'])) ?></span>
              <span><?= Helpers::e($s['name']) ?><?php if ($s['region']): ?> · <?= Helpers::e($s['region']) ?><?php endif; ?></span>
              <span class="go"><?= Helpers::e(Lang::t('visit')) ?> →</span>
            </a>
          <?php else: ?>
            <div class="store-item">
              <span class="store-type s-<?= Helpers::e($s['type']) ?>"><?= Helpers::e(strtoupper($s['type'])) ?></span>
              <span><?= Helpers::e($s['name']) ?><?php if ($s['region']): ?> · <?= Helpers::e($s['region']) ?><?php endif; ?></span>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($desc): ?>
    <div class="d-desc">
      <h2><?= Helpers::e(Lang::t('description')) ?></h2>
      <div class="d-desc-body"><?= nl2br(Helpers::e($desc)) ?></div>
    </div>
  <?php endif; ?>

  <?php if ($related): ?>
    <h2 class="rel-h"><?= Helpers::e($brand) ?></h2>
    <div class="grid">
      <?php foreach ($related as $r):
        $rt = $r['thumb'] ?: '/assets/img/placeholder.svg'; ?>
        <a class="pcard" href="/products/<?= (int)$r['id'] ?>">
          <div class="pcard-img" style="background-image:url('<?= Helpers::e($rt) ?>')"></div>
          <div class="pcard-body"><div class="pcard-name"><?= Helpers::e(Lang::pick($r, 'name')) ?></div></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script>
  // 썸네일 클릭 → 메인 이미지 교체
  document.querySelectorAll('.thumbs .t').forEach(function (b) {
    b.addEventListener('click', function () {
      document.getElementById('mainImg').style.backgroundImage = "url('" + b.dataset.src + "')";
    });
  });
</script>
