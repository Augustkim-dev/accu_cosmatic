<?php use App\Helpers; use App\Csrf;
$labels = [
  'site_name'=>'사이트명','contact_email'=>'연락 이메일','contact_phone'=>'연락 전화',
  'default_lang_front'=>'프론트 기본언어(ko/vi/en)','default_lang_admin'=>'관리자 입력 기본언어',
  'currency'=>'통화(예: VND)','sns_instagram'=>'Instagram URL','sns_tiktok'=>'TikTok URL','sns_zalo'=>'Zalo URL',
];
?>
<div class="panel" style="max-width:640px">
  <div class="panel-head">시스템 설정</div>
  <div class="panel-body">
    <form method="post" action="/admin/settings" class="form">
      <?= Csrf::field() ?>
      <?php foreach ($keys as $k): ?>
        <label class="fld"><?= Helpers::e($labels[$k] ?? $k) ?>
          <input type="text" name="<?= Helpers::e($k) ?>" value="<?= Helpers::e($map[$k] ?? '') ?>">
        </label>
      <?php endforeach; ?>
      <div class="form-actions"><button class="btn" type="submit">저장</button></div>
    </form>
  </div>
</div>
