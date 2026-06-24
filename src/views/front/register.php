<?php
use App\Helpers;
use App\Csrf;
use App\Lang;
$old = $old ?? [];
?>
<section class="wrap block narrow">
  <h1 class="page-h"><?= Helpers::e(Lang::t('register')) ?></h1>
  <form method="post" action="/register" class="fform">
    <?= Csrf::field() ?>
    <label><?= Helpers::e(Lang::t('email')) ?>
      <input type="email" name="email" value="<?= Helpers::e($old['email'] ?? '') ?>" required>
    </label>
    <label><?= Helpers::e(Lang::t('name')) ?>
      <input type="text" name="name" value="<?= Helpers::e($old['name'] ?? '') ?>" required>
    </label>
    <label><?= Helpers::e(Lang::t('phone')) ?>
      <input type="text" name="phone" value="<?= Helpers::e($old['phone'] ?? '') ?>">
    </label>
    <label><?= Helpers::e(Lang::t('password')) ?>
      <input type="password" name="password" required>
      <span class="hint">8자 이상 / 8+ chars</span>
    </label>
    <button class="hero-btn" type="submit"><?= Helpers::e(Lang::t('register')) ?></button>
    <p class="muted"><a href="/login"><?= Helpers::e(Lang::t('login')) ?> →</a></p>
  </form>
</section>
