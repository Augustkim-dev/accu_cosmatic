<?php use App\Helpers; use App\Csrf;
$moduleLabels = [
  'dashboard'=>'대시보드','products'=>'제품','brands'=>'브랜드','categories'=>'카테고리','stores'=>'구매처',
  'orders'=>'주문','inquiries'=>'문의','pages'=>'페이지','posts'=>'소식','projects'=>'프로젝트',
  'members'=>'회원','reports'=>'통계','admins'=>'관리자계정','roles'=>'권한','settings'=>'설정','logs'=>'감사로그',
];
?>
<div class="panel">
  <div class="panel-body">
    <p class="muted">역할별로 모듈·동작 권한을 체크하세요. <strong>최종관리자</strong>는 항상 전체 권한이라 잠겨 있습니다.
    저장 후 해당 역할 관리자는 <strong>다음 로그인 시</strong> 반영됩니다.</p>
  </div>
</div>

<form method="post" action="/admin/roles">
  <?= Csrf::field() ?>
  <div class="panel">
    <table class="tbl matrix">
      <thead>
        <tr>
          <th>권한</th>
          <?php foreach ($roles as $r): ?>
            <th class="ta-c">
              <span class="role role-<?= Helpers::e($r['code']) ?>"><?= Helpers::e($r['name_ko']) ?></span>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($grouped as $module => $perms): ?>
        <tr class="mod-row"><td colspan="<?= count($roles)+1 ?>"><?= Helpers::e($moduleLabels[$module] ?? $module) ?></td></tr>
        <?php foreach ($perms as $p): ?>
          <tr>
            <td class="perm-cell"><code><?= Helpers::e($p['module'] . '.' . $p['action']) ?></code> <span class="muted"><?= Helpers::e($p['label']) ?></span></td>
            <?php foreach ($roles as $r):
              $isSuper = $r['code'] === 'superadmin';
              $checked = $isSuper || !empty($map[$r['id']][$p['id']]); ?>
              <td class="ta-c">
                <input type="checkbox"
                  name="perm[<?= (int)$r['id'] ?>][]" value="<?= (int)$p['id'] ?>"
                  <?= $checked ? 'checked' : '' ?> <?= $isSuper ? 'disabled' : '' ?>>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="form-actions" style="margin-top:14px"><button class="btn" type="submit">권한 저장</button></div>
</form>
