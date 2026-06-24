<?php
use App\Helpers;
use App\Csrf;
use App\Lang;
use App\Settings;
$cur = Settings::get('currency', 'VND');
?>
<section class="wrap block">
  <h1 class="page-h"><?= Helpers::e(Lang::t('cart')) ?></h1>

  <?php if (!$items): ?>
    <p class="muted"><?= Helpers::e(Lang::t('empty_cart')) ?></p>
    <p><a class="hero-btn" href="/products"><?= Helpers::e(Lang::t('all_products')) ?> →</a></p>
  <?php else: ?>
    <form method="post" action="/cart/update">
      <?= Csrf::field() ?>
      <table class="carttbl">
        <thead><tr><th></th><th><?= Helpers::e(Lang::t('products')) ?></th><th><?= Helpers::e(Lang::t('quantity')) ?></th><th class="ta-r"><?= Helpers::e(Lang::t('subtotal')) ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $it):
          $thumb = $it['thumb'] ?: '/assets/img/placeholder.svg'; ?>
          <tr>
            <td><div class="cthumb" style="background-image:url('<?= Helpers::e($thumb) ?>')"></div></td>
            <td data-label="<?= Helpers::e(Lang::t('products')) ?>"><a href="/products/<?= (int)$it['id'] ?>"><?= Helpers::e(Lang::pick($it, 'name')) ?></a></td>
            <td data-label="<?= Helpers::e(Lang::t('quantity')) ?>"><input class="qty-in" type="number" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>" min="0"></td>
            <td class="ta-r" data-label="<?= Helpers::e(Lang::t('subtotal')) ?>"><?= number_format((float)$it['line']) ?> <?= Helpers::e($cur) ?></td>
            <td class="ta-r">
              <button class="btn-x" formaction="/cart/remove" name="product_id" value="<?= (int)$it['id'] ?>" type="submit">×</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div class="cart-foot">
        <button class="ghost-btn" type="submit"><?= Helpers::e(Lang::t('quantity')) ?> 변경</button>
        <div class="cart-total"><?= Helpers::e(Lang::t('total')) ?>: <strong><?= number_format((float)$subtotal) ?> <?= Helpers::e($cur) ?></strong></div>
      </div>
    </form>
    <div class="ta-r" style="margin-top:18px">
      <a class="hero-btn" href="/checkout"><?= Helpers::e(Lang::t('checkout')) ?> →</a>
    </div>
  <?php endif; ?>
</section>
