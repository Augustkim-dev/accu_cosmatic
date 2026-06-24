<?php
use App\Helpers;
use App\Lang;
use App\Settings;
use App\Cart;
use App\MemberAuth;

$site = Settings::get('site_name', 'ACCU Cosmetic');
$cur  = Lang::current();
$pageTitle = ($title ?? '') !== '' ? (Helpers::e($title) . ' · ' . Helpers::e($site)) : Helpers::e($site);
$desc = $metaDescription ?? ($site . ' — Korean cosmetics');
$cartCount = Cart::count();
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
$canonical = $scheme . '://' . $host . (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$ogImg = $ogImage ?? '';
?><!doctype html>
<html lang="<?= $cur ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= Helpers::e($desc) ?>">
  <link rel="canonical" href="<?= Helpers::e($canonical) ?>">
  <meta property="og:title" content="<?= $pageTitle ?>">
  <meta property="og:description" content="<?= Helpers::e($desc) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= Helpers::e($canonical) ?>">
  <?php if ($ogImg): ?><meta property="og:image" content="<?= Helpers::e($scheme . '://' . $host . $ogImg) ?>"><?php endif; ?>
  <link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>
<header class="site-head">
  <div class="wrap head-inner">
    <a class="logo" href="/"><?= Helpers::e($site) ?></a>
    <div class="head-collapse" id="headMenu">
      <nav class="nav">
        <a href="/"><?= Helpers::e(Lang::t('home')) ?></a>
        <a href="/products"><?= Helpers::e(Lang::t('products')) ?></a>
        <a href="/brands"><?= Helpers::e(Lang::t('brands')) ?></a>
        <a href="/news"><?= Helpers::e(Lang::t('news')) ?></a>
        <a href="/contact"><?= Helpers::e(Lang::t('contact')) ?></a>
      </nav>
      <div class="head-account">
        <?php if (MemberAuth::check()): ?>
          <a href="/mypage"><?= Helpers::e(Lang::t('mypage')) ?></a>
          <a href="/logout"><?= Helpers::e(Lang::t('logout')) ?></a>
        <?php else: ?>
          <a href="/login"><?= Helpers::e(Lang::t('login')) ?></a>
        <?php endif; ?>
      </div>
      <span class="langs">
        <?php foreach (Lang::SUPPORTED as $l): ?>
          <a class="<?= $l === $cur ? 'on' : '' ?>" href="/lang/<?= $l ?>"><?= strtoupper($l) ?></a>
        <?php endforeach; ?>
      </span>
    </div>
    <div class="head-fixed">
      <a class="cart-link" href="/cart" aria-label="cart">
        <span class="cart-ico">🛒</span><span class="cart-txt"><?= Helpers::e(Lang::t('cart')) ?></span><?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <button class="menu-toggle" id="menuToggle" type="button" aria-label="menu" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<main class="site-main">
  <?php foreach (\App\Flash::pull() as $f): ?>
    <div class="wrap"><div class="fl fl-<?= Helpers::e($f['type']) ?>"><?= Helpers::e($f['msg']) ?></div></div>
  <?php endforeach; ?>
  <?= $content ?>
</main>

<footer class="site-foot">
  <div class="wrap foot-inner">
    <div><strong><?= Helpers::e($site) ?></strong></div>
    <div class="muted">
      <?php if (Settings::get('contact_phone')): ?>Tel <?= Helpers::e(Settings::get('contact_phone')) ?> · <?php endif; ?>
      <?php if (Settings::get('contact_email')): ?><a href="mailto:<?= Helpers::e(Settings::get('contact_email')) ?>"><?= Helpers::e(Settings::get('contact_email')) ?></a><?php endif; ?>
    </div>
    <div class="muted">© <?= date('Y') ?> <?= Helpers::e($site) ?></div>
  </div>
</footer>
<script>
(function(){
  var t=document.getElementById('menuToggle'), m=document.getElementById('headMenu');
  if(t&&m){ t.addEventListener('click', function(){
    var open=m.classList.toggle('open'); t.classList.toggle('on');
    t.setAttribute('aria-expanded', open?'true':'false');
  }); }
})();
</script>
</body>
</html>
