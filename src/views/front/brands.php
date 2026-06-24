<?php
use App\Helpers;
use App\Lang;
?>
<section class="wrap block">
  <h1 class="page-h"><?= Helpers::e(Lang::t('brands')) ?></h1>
  <?php if (!$brands): ?>
    <p class="muted"><?= Helpers::e(Lang::t('no_products')) ?></p>
  <?php else: ?>
    <div class="brand-grid">
      <?php foreach ($brands as $b): ?>
        <a class="brand-card" href="/products?brand=<?= (int)$b['id'] ?>">
          <div class="brand-logo">
            <?php if ($b['logo_path']): ?><img src="<?= Helpers::e($b['logo_path']) ?>" alt=""><?php else: ?><span><?= Helpers::e($b['code']) ?></span><?php endif; ?>
          </div>
          <div class="brand-name"><?= Helpers::e(Lang::pick($b, 'name')) ?></div>
          <div class="muted sm"><?= (int)$b['cnt'] ?> <?= Helpers::e(Lang::t('products')) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
