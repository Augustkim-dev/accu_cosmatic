<?php use App\Helpers; use App\Csrf;
$v = fn(string $k) => Helpers::e($item[$k] ?? '');
$checked = (!($item ?? []) || !empty($item['is_active'])) ? 'checked' : '';
$t = $item['type'] ?? 'online';
?>
<div class="panel" style="max-width:620px">
  <div class="panel-head"><?= $isEdit ? '구매처 수정' : '구매처 등록' ?></div>
  <div class="panel-body">
    <form method="post" action="<?= Helpers::e($action) ?>" class="form">
      <?= Csrf::field() ?>
      <div class="row">
        <label class="fld">이름 <span class="req">*</span><input type="text" name="name" value="<?= $v('name') ?>" required></label>
        <label class="fld">유형
          <select name="type">
            <option value="online" <?= $t==='online'?'selected':'' ?>>온라인몰</option>
            <option value="offline" <?= $t==='offline'?'selected':'' ?>>오프라인 매장</option>
            <option value="sns" <?= $t==='sns'?'selected':'' ?>>SNS/메신저</option>
          </select>
        </label>
      </div>
      <label class="fld">링크 URL<input type="text" name="url" value="<?= $v('url') ?>" placeholder="https:// 또는 https://zalo.me/..."></label>
      <div class="row">
        <label class="fld">지역<input type="text" name="region" value="<?= $v('region') ?>"></label>
        <label class="fld">정렬<input type="number" name="sort" value="<?= $v('sort') ?: '0' ?>"></label>
      </div>
      <div class="row">
        <label class="fld">위도(lat)<input type="text" name="lat" value="<?= $v('lat') ?>"></label>
        <label class="fld">경도(lng)<input type="text" name="lng" value="<?= $v('lng') ?>"></label>
      </div>
      <label class="chk"><input type="checkbox" name="is_active" <?= $checked ?>> 활성</label>
      <div class="form-actions"><button class="btn" type="submit"><?= $isEdit ? '저장' : '등록' ?></button><a class="btn ghost" href="/admin/stores">취소</a></div>
    </form>
  </div>
</div>
