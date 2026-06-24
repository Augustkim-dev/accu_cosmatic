<?php
use App\Helpers;
use App\Lang;
use App\Settings;
$cur = Settings::get('currency', 'VND');
?>
<section class="wrap block narrow">
  <nav class="crumb"><a href="/mypage"><?= Helpers::e(Lang::t('mypage')) ?></a> › <?= Helpers::e($o['order_no']) ?></nav>
  <h1 class="page-h"><?= Helpers::e(Lang::t('order_no')) ?> <?= Helpers::e($o['order_no']) ?></h1>
  <p><span class="ostat s-<?= Helpers::e($o['status']) ?>"><?= Helpers::e(Lang::t('st_' . $o['status'])) ?></span>
     <span class="muted">· <?= Helpers::e($o['created_at']) ?></span></p>

  <div class="card-box">
    <?php foreach ($items as $it): ?>
      <div class="co-row"><span><?= Helpers::e($it['product_name']) ?> × <?= (int)$it['qty'] ?></span><span><?= number_format((float)$it['price'] * (int)$it['qty']) ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="co-row"><span><?= Helpers::e(Lang::t('subtotal')) ?></span><span><?= number_format((float)$o['subtotal']) ?></span></div>
    <div class="co-row total"><span><?= Helpers::e(Lang::t('total')) ?></span><strong><?= number_format((float)$o['total']) ?> <?= Helpers::e($cur) ?></strong></div>
  </div>

  <div class="card-box">
    <div class="co-row"><span><?= Helpers::e(Lang::t('receiver')) ?></span><span><?= Helpers::e($o['receiver_name']) ?></span></div>
    <div class="co-row"><span><?= Helpers::e(Lang::t('phone')) ?></span><span><?= Helpers::e($o['phone']) ?></span></div>
    <?php if ($o['address']): ?><div class="co-row"><span><?= Helpers::e(Lang::t('address')) ?></span><span><?= Helpers::e($o['address']) ?></span></div><?php endif; ?>
    <?php if ($bank): ?>
      <div class="co-row"><span><?= Helpers::e(Lang::t('bank_account')) ?></span><span><?= Helpers::e($bank['bank_name'] . ' ' . $bank['account_no']) ?></span></div>
    <?php endif; ?>
  </div>
</section>
