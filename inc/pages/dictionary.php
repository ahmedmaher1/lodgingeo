<?php
/** @var array $page */
$cols = data_json('columns') ?? ['sheets' => []];
$full = data_json('full_fields') ?? ['groups' => []];
$seen = [];
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Every column of the free sample, with its field group, what it means and an example value, followed by the full dataset field reference. Each column has its own anchor, for example <a href="#hotel_rates_by_site"><code>#hotel_rates_by_site</code></a>, so you can link straight to it.</p>

<h2 id="sheets">Sheets in the sample</h2>
<div class="table-wrap">
<table class="num">
  <caption>Sheets of the <?= e(SAMPLE_RELEASE) ?> sample workbook</caption>
  <thead><tr><th scope="col">Sheet</th><th scope="col">Rows</th><th scope="col">Columns</th><th scope="col">Content</th></tr></thead>
  <tbody>
<?php foreach ($cols['sheets'] as $s): ?>
    <tr><th scope="row"><?= e($s['name']) ?></th><td><?= n($s['rows']) ?></td><td><?= n($s['columns_total']) ?></td><td class="text"><?= e($s['description'] ?? '') ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<h2 id="sample-columns">Sample columns</h2>
<p>Columns are listed in file order. Patterned names such as <code>nearest_&lt;service&gt;</code> stand for a family of columns, one per service, group or radius.</p>
<p class="filter" hidden><label for="dict-filter">Filter columns</label> <input type="search" id="dict-filter" data-filter="#dictionary" placeholder="e.g. rate, building, nearest"></p>
<div class="table-wrap">
<table id="dictionary" class="dict">
  <caption>Columns of the sample workbook</caption>
  <thead><tr><th scope="col">Column</th><th scope="col">Sheet · group</th><th scope="col">Meaning</th><th scope="col">Example</th><th scope="col">In sample</th></tr></thead>
  <tbody>
<?php foreach ($cols['sheets'] as $s): foreach ($s['columns'] ?? [] as $c):
    $anchor = col_anchor($c['name']);
    if (isset($seen[$anchor])) { continue; }
    $seen[$anchor] = true;
?>
    <tr id="<?= e($anchor) ?>"><th scope="row"><code><?= e($c['name']) ?></code></th><td><?= e($s['name']) ?> · <?= e($c['group']) ?></td><td><?= e($c['meaning'] ?? '') ?></td><td><?= !empty($c['private']) ? '<span class="small">delivered file only</span>' : (isset($c['example']) && $c['example'] !== null ? '<code>' . e($c['example']) . '</code>' : '') ?></td><td>✓</td></tr>
<?php endforeach; endforeach; ?>
  </tbody>
</table>
</div>
<?php if (!empty($cols['interim'])): ?>
<p class="small">The Hotels sheet has <?= n($cols['sheets'][0]['columns_total'] ?? null) ?> columns in all; columns not named above belong to the families shown by pattern.</p>
<?php endif; ?>

<h2 id="full-dataset-fields">Full dataset fields</h2>
<p>The data you order uses the Location Drive field reference: <?= n($full['total'] ?? null) ?> fields in four groups. These are groupings, not tiers or plans. Lodgingeo adds the contact enrichment and the derived hotel columns described on the <a href="/data/">data pages</a>. Fields the current sample does not contain are delivered with the full data.</p>
<div class="table-wrap">
<table id="full-fields">
  <caption>Full dataset fields and whether the sample contains them</caption>
  <thead><tr><th scope="col">Field</th><th scope="col">Group</th><th scope="col">In sample</th></tr></thead>
  <tbody>
<?php foreach ($full['groups'] as $g): foreach ($g['fields'] as $f): ?>
    <tr id="full-<?= e(col_anchor($f)) ?>"><th scope="row"><code><?= e($f) ?></code></th><td><?= e($g['name']) ?></td><td><?= in_sample($f) ? '<a href="#' . e(col_anchor($f)) . '">✓</a>' : '<span class="tag tag-muted">full dataset</span>' ?></td></tr>
<?php endforeach; endforeach; ?>
  </tbody>
</table>
</div>

<p>See the fields in context on the <a href="/data/">data overview</a>, or <a href="/hotel-database-sample/">download the sample</a> to see them with real values.</p>
<?= partial('cta') ?>
