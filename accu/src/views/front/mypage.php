<?php
use App\Helpers;
use App\Lang;
use App\Settings;
use App\MemberAuth;
$cur = Settings::get('currency', 'VND');
?>
<section class="wrap block">
  <h1 class="page-h"><?= Helpers::e(Lang::t('mypage')) ?></h1>
  <p class="muted"><?= Helpers::e(MemberAuth::user()['name']) ?> · <?= Helpers::e(MemberAuth::user()['email']) ?></p>

  <h2 style="margin-top:24px"><?= Helpers::e(Lang::t('my_orders')) ?></h2>
  <?php if (!$orders): ?>
    <p class="muted">—</p>
  <?php else: ?>
    <table class="carttbl">
      <thead><tr><th><?= Helpers::e(Lang::t('order_no')) ?></th><th><?= Helpers::e(Lang::t('order_date')) ?></th><th><?= Helpers::e(Lang::t('status')) ?></th><th class="ta-r"><?= Helpers::e(Lang::t('total')) ?></th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td data-label="<?= Helpers::e(Lang::t('order_no')) ?>"><a href="/mypage/orders/<?= (int)$o['id'] ?>"><?= Helpers::e($o['order_no']) ?></a></td>
          <td data-label="<?= Helpers::e(Lang::t('order_date')) ?>"><?= Helpers::e(substr($o['created_at'], 0, 10)) ?></td>
          <td data-label="<?= Helpers::e(Lang::t('status')) ?>"><span class="ostat s-<?= Helpers::e($o['status']) ?>"><?= Helpers::e(Lang::t('st_' . $o['status'])) ?></span></td>
          <td class="ta-r" data-label="<?= Helpers::e(Lang::t('total')) ?>"><?= number_format((float)$o['total']) ?> <?= Helpers::e($cur) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
