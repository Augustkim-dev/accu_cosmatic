<?php use App\Helpers; use App\Csrf; use App\Lang; ?>
<section class="wrap block narrow">
  <h1 class="page-h"><?= Helpers::e(Lang::t('contact')) ?></h1>
  <form method="post" action="/contact" class="fform">
    <?= Csrf::field() ?>
    <label><?= Helpers::e(Lang::t('name')) ?> *
      <input type="text" name="name" value="<?= Helpers::e($member['name'] ?? '') ?>" required>
    </label>
    <label><?= Helpers::e(Lang::t('phone')) ?> / <?= Helpers::e(Lang::t('email')) ?> *
      <input type="text" name="contact" value="<?= Helpers::e($member['email'] ?? '') ?>" required>
    </label>
    <label><?= Helpers::e(Lang::t('message')) ?> *
      <textarea name="message" rows="5" required></textarea>
    </label>
    <button class="hero-btn" type="submit"><?= Helpers::e(Lang::t('send')) ?></button>
  </form>
</section>
