<?php use App\Helpers; use App\Lang; ?>
<section class="wrap block narrow">
  <nav class="crumb"><a href="/news"><?= Helpers::e(Lang::t('news')) ?></a> › <?= Helpers::e(Lang::pick($p,'title')) ?></nav>
  <h1 class="page-h"><?= Helpers::e(Lang::pick($p, 'title')) ?></h1>
  <div class="muted sm" style="margin-bottom:18px"><?= Helpers::e(substr($p['published_at'] ?? $p['created_at'], 0, 10)) ?></div>
  <?php if ($p['thumbnail']): ?><img src="<?= Helpers::e($p['thumbnail']) ?>" alt="" style="max-width:100%;border-radius:12px;margin-bottom:18px"><?php endif; ?>
  <div class="cms-body"><?= nl2br(Helpers::e(Lang::pick($p, 'body'))) ?></div>
</section>
