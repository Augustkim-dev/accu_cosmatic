<?php
use App\Auth;
use App\Flash;
use App\Helpers;
use App\Menu;

$u = Auth::user();
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$flashes = Flash::pull();

$isActive = function (string $url) use ($current): bool {
    if ($url === '#') return false;
    if ($url === '/admin') return $current === '/admin' || $current === '/';
    return $current === $url || str_starts_with($current, $url . '/');
};
?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= Helpers::e($title ?? '관리자') ?> · ACCU Cosmetic</title>
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">ACCU<span>Admin</span></div>
    <nav class="menu">
      <?php foreach (Menu::visible() as $m): ?>
        <a href="<?= Helpers::e($m['url']) ?>"
           class="<?= ($isActive($m['url']) ? 'active' : '') ?> <?= ($m['url'] === '#' ? 'soon' : '') ?>">
          <?= Helpers::e($m['label']) ?>
          <?php if ($m['url'] === '#'): ?><em>준비중</em><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <div class="main">
    <header class="topbar">
      <div class="page-title"><?= Helpers::e($title ?? '') ?></div>
      <div class="userbox">
        <span class="role role-<?= Helpers::e($u['role_code']) ?>"><?= Helpers::e($u['role_name']) ?></span>
        <span class="uname"><?= Helpers::e($u['name']) ?></span>
        <a class="link-sm" href="/admin/account/password">비밀번호 변경</a>
        <a class="logout" href="/admin/logout">로그아웃</a>
      </div>
    </header>
    <main class="content">
      <?php foreach ($flashes as $f): ?>
        <div class="flash flash-<?= Helpers::e($f['type']) ?>"><?= Helpers::e($f['msg']) ?></div>
      <?php endforeach; ?>
      <?= $content ?>
    </main>
  </div>
</div>
</body>
</html>
