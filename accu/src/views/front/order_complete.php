<?php
use App\Helpers;
use App\Lang;
use App\Settings;
$cur = Settings::get('currency', 'VND');
?>
<section class="wrap block narrow">
  <div class="done-badge">✓</div>
  <h1 class="page-h ta-c"><?= Helpers::e(Lang::t('order_done')) ?></h1>
  <p class="ta-c muted"><?= Helpers::e(Lang::t('order_no')) ?>: <strong><?= Helpers::e($o['order_no']) ?></strong></p>

  <div class="card-box">
    <?php foreach ($items as $it): ?>
      <div class="co-row"><span><?= Helpers::e($it['product_name']) ?> × <?= (int)$it['qty'] ?></span><span><?= number_format((float)$it['price'] * (int)$it['qty']) ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="co-row total"><span><?= Helpers::e(Lang::t('total')) ?></span><strong><?= number_format((float)$o['total']) ?> <?= Helpers::e($cur) ?></strong></div>
  </div>

  <?php if ($bank): ?>
    <div class="bank-box">
      <div class="muted sm"><?= Helpers::e(Lang::t('bank_guide')) ?></div>
      <div class="bank-line"><strong><?= Helpers::e($bank['bank_name']) ?></strong> <?= Helpers::e($bank['account_no']) ?> (<?= Helpers::e($bank['holder']) ?>)</div>
    </div>
  <?php endif; ?>

  <p class="ta-c" style="margin-top:22px">
    <a class="ghost-btn" href="/products"><?= Helpers::e(Lang::t('all_products')) ?></a>
    <?php if (\App\MemberAuth::check()): ?>
      <a class="hero-btn" href="/mypage"><?= Helpers::e(Lang::t('my_orders')) ?></a>
    <?php endif; ?>
  </p>
</section>
