<?php use App\Helpers; ?>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>시각</th><th>관리자</th><th>동작</th><th>대상</th><th>IP</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="5" class="empty">로그가 없습니다.</td></tr><?php endif; ?>
    <?php foreach ($rows as $l): ?>
      <tr>
        <td><?= Helpers::e($l['created_at']) ?></td>
        <td><?= Helpers::e($l['email'] ?? '-') ?></td>
        <td><span class="tag"><?= Helpers::e($l['action']) ?></span></td>
        <td><?= Helpers::e($l['target'] ?? '-') ?></td>
        <td><?= Helpers::e($l['ip'] ?? '-') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php if ($pages > 1): ?>
<div class="pager">
  <?php for ($i=1;$i<=$pages;$i++): ?><a class="<?= $i===$page?'on':'' ?>" href="/admin/logs?page=<?= $i ?>"><?= $i ?></a><?php endfor; ?>
</div>
<?php endif; ?>
