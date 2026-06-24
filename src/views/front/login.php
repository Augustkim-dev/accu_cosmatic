<?php
use App\Helpers;
use App\Csrf;
use App\Lang;
?>
<section class="wrap block narrow">
  <h1 class="page-h"><?= Helpers::e(Lang::t('login')) ?></h1>
  <form method="post" action="/login" class="fform">
    <?= Csrf::field() ?>
    <label><?= Helpers::e(Lang::t('email')) ?><input type="email" name="email" required autofocus></label>
    <label><?= Helpers::e(Lang::t('password')) ?><input type="password" name="password" required></label>
    <button class="hero-btn" type="submit"><?= Helpers::e(Lang::t('login')) ?></button>
    <p class="muted"><a href="/register"><?= Helpers::e(Lang::t('register')) ?> →</a></p>
  </form>
</section>
