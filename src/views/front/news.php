<?php use App\Helpers; use App\Lang; ?>
<section class="wrap block">
  <h1 class="page-h"><?= Helpers::e(Lang::t('news')) ?></h1>
  <?php if (!$rows): ?>
    <p class="muted">—</p>
  <?php else: ?>
    <div class="news-grid">
      <?php foreach ($rows as $n):
        $thumb = $n['thumbnail'] ?: '/assets/img/placeholder.svg'; ?>
        <a class="news-card" href="/news/<?= (int)$n['id'] ?>">
          <div class="news-img" style="background-image:url('<?= Helpers::e($thumb) ?>')"></div>
          <div class="news-body">
            <div class="news-title"><?= Helpers::e(Lang::pick($n, 'title')) ?></div>
            <div class="muted sm"><?= Helpers::e(substr($n['published_at'] ?? $n['created_at'], 0, 10)) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
