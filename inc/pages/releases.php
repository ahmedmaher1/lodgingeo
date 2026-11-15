<?php
/** @var array $page */
$rel = data_json('releases')['releases'] ?? [];
$hasCounts = (bool) array_filter($rel, fn($r) => $r['sample_changes'] !== null);
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">The data has been updated monthly since January 2025.</p>
<p>Each release is compared with the previous one to produce the change history. Subscribers receive every release with its change file (<a href="/formats-and-delivery/#updates">monthly updates</a>).</p>
<div class="table-wrap">
<table class="num narrow">
  <caption>Monthly releases, newest first</caption>
  <thead><tr><th scope="col">Release</th><th scope="col">Month</th><?php if ($hasCounts): ?><th scope="col">Changes detected among the <?= n(stat_val('sheets.Hotels.rows')) ?> sample hotels</th><?php endif; ?></tr></thead>
  <tbody>
<?php foreach ($rel as $r): ?>
    <tr><th scope="row"><?= e($r['month']) ?><?= $r['month'] === LATEST_RELEASE ? ' (latest)' : '' ?></th><td class="text"><?= e(month_label($r['month'])) ?></td><?php if ($hasCounts): ?><td><?= $r['sample_changes'] === null ? 'first release' : n($r['sample_changes']) ?></td><?php endif; ?></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<p>The sample's Hotel changes sheet has <?= n(stat_val('changes.total')) ?> change records across these months. See <a href="/data/hotel-history-and-changes/">history and changes</a> and the <a href="/methodology/#monthly-cycle">monthly cycle</a>.</p>
<?= partial('cta') ?>
