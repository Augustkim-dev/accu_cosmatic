<?php use App\Helpers; ?>
<div class="panel center-panel">
  <h1 class="big">403</h1>
  <p>이 작업에 대한 권한이 없습니다.</p>
  <p class="muted">필요 권한: <code><?= Helpers::e($permission ?? '') ?></code></p>
  <p><a class="btn" href="/admin">대시보드로 돌아가기</a></p>
</div>
