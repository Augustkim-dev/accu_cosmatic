<?php use App\Helpers; ?>
<section class="wrap block narrow">
  <h1 class="page-h"><?= Helpers::e($pageTitle) ?></h1>
  <div class="cms-body"><?= nl2br(Helpers::e($body)) ?></div>
</section>
