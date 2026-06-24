<?php
use App\Helpers;
use App\Csrf;
use App\Lang;
use App\Settings;
$cur = Settings::get('currency', 'VND');
?>
<section class="wrap block">
  <h1 class="page-h"><?= Helpers::e(Lang::t('checkout')) ?></h1>
  <div class="checkout">
    <form method="post" action="/checkout" class="fform co-form">
      <?= Csrf::field() ?>
      <label><?= Helpers::e(Lang::t('receiver')) ?> *
        <input type="text" name="receiver_name" value="<?= Helpers::e($member['name'] ?? '') ?>" required>
      </label>
      <label><?= Helpers::e(Lang::t('phone')) ?> *
        <input type="text" name="phone" required>
      </label>
      <label><?= Helpers::e(Lang::t('address')) ?>
        <input type="text" name="address">
      </label>
      <label><?= Helpers::e(Lang::t('depositor')) ?>
        <input type="text" name="depositor_name">
      </label>
      <?php if ($banks): ?>
      <label><?= Helpers::e(Lang::t('bank_account')) ?>
        <select name="bank_account_id">
          <?php foreach ($banks as $b): ?>
            <option value="<?= (int)$b['id'] ?>"><?= Helpers::e($b['bank_name'] . ' ' . $b['account_no'] . ' (' . $b['holder'] . ')') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php endif; ?>
      <button class="hero-btn" type="submit"><?= Helpers::e(Lang::t('checkout')) ?></button>
    </form>

    <aside class="co-summary">
      <h3><?= Helpers::e(Lang::t('cart')) ?></h3>
      <?php foreach ($items as $it): ?>
        <div class="co-row">
          <span><?= Helpers::e(Lang::pick($it, 'name')) ?> × <?= (int)$it['qty'] ?></span>
          <span><?= number_format((float)$it['line']) ?></span>
        </div>
      <?php endforeach; ?>
      <hr>
      <div class="co-row total"><span><?= Helpers::e(Lang::t('total')) ?></span><strong><?= number_format((float)$subtotal) ?> <?= Helpers::e($cur) ?></strong></div>
      <p class="muted sm"><?= Helpers::e(Lang::t('bank_guide')) ?></p>
    </aside>
  </div>
</section>
