<?php
use App\Csrf;
use App\Helpers;
?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>관리자 로그인 · ACCU Cosmetic</title>
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="login-body">
  <div class="login-card">
    <div class="brand center">ACCU<span>Admin</span></div>
    <?php if (!empty($error)): ?>
      <div class="alert"><?= Helpers::e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/admin/login">
      <?= Csrf::field() ?>
      <label>이메일
        <input type="email" name="email" autocomplete="username" required autofocus>
      </label>
      <label>비밀번호
        <input type="password" name="password" autocomplete="current-password" required>
      </label>
      <button type="submit">로그인</button>
    </form>
  </div>
</body>
</html>
