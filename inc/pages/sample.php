<?php
/** @var array $page */
$sheets = stat_val('sheets') ?? [];
$descr = [];
foreach (data_json('columns')['sheets'] ?? [] as $s) {
    $descr[$s['name']] = $s['description'] ?? '';
}
$labels = [
    'Hotels' => [n($sheets['Hotels']['rows'] ?? null) . ' rows × ' . n($sheets['Hotels']['columns'] ?? null) . ' columns'],
    'Column guide' => [n($sheets['Column guide']['rows'] ?? null) . ' definitions'],
    'Hotel changes' => [n($sheets['Hotel changes']['rows'] ?? null) . ' monthly change records'],
    'Category counts' => [n($sheets['Category counts']['rows'] ?? null) . ' rows' . (stat_val('category_counts.distinct_categories') ? ' (' . n(stat_val('category_counts.distinct_categories')) . ' distinct categories)' : '')],
    'Buildings' => [n($sheets['Buildings']['rows'] ?? null) . ' polygons'],
];
$total = (int) stat_val('fill.total');
$fillLabels = [
    'phone' => 'Phone', 'email' => 'Email', 'whatsapp' => 'WhatsApp', 'website' => 'Website', 'rating' => 'Rating',
    'rate_history_by_month' => 'Monthly rate history', 'building_polygon_wkt' => 'Building polygon', 'amenities' => 'Amenities',
    'hotel_rates_by_site' => 'Rates by booking site', 'lowest_offer_latest' => 'Lowest offer', 'hotel_class' => 'Hotel class',
    'check_in_time' => 'Check-in time', 'direct_offer_latest' => 'Direct (official-site) offer', 'brand' => 'Brand',
    'facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'linkedin_url' => 'LinkedIn', 'x_url' => 'X', 'tiktok_url' => 'TikTok', 'youtube_url' => 'YouTube',
];
$fill = stat_val('fill.fields') ?? [];
arsort($fill);
$size = stat_val('file_size_bytes');
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">One Excel workbook with <?= n($sheets['Hotels']['rows'] ?? null) ?> real hotels from the <?= e(SAMPLE_RELEASE) ?> release, every column of the hotel data, and the column guide that explains them. It is the quickest way to judge the data before you ask for a quote.</p>

<?= partial('sample-form', ['variant' => 'full', 'source' => '/hotel-database-sample/']) ?>

<h2 id="whats-in-the-file">What's in the file</h2>
<div class="table-wrap">
<table>
  <caption>Sheets in the sample workbook</caption>
  <thead><tr><th scope="col">Sheet</th><th scope="col">Size</th><th scope="col">Content</th></tr></thead>
  <tbody>
<?php foreach ($labels as $sheet => [$size_label]): ?>
    <tr><th scope="row"><?= e($sheet) ?></th><td><?= e($size_label) ?></td><td><?= e($descr[$sheet] ?? '') ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<dl class="facts facts-small">
  <div><dt>Format</dt><dd>Excel (.xlsx)</dd></div>
  <div><dt>Release</dt><dd><?= e(SAMPLE_RELEASE) ?></dd></div>
<?php if ($size): ?>
  <div><dt>File size</dt><dd><?= e(number_format($size / 1048576, 1)) ?> MB</dd></div>
<?php endif; ?>
</dl>
<p>Every column is described in the <a href="/data-dictionary/">data dictionary</a>. The full data has the same columns, for every hotel in the countries you order.</p>

<h2 id="sample-coverage">Sample coverage</h2>
<p>The sample hotels are in <?= n(stat_val('countries.count')) ?> countries and <?= n(stat_val('cities.count')) ?> cities. The full database covers <?= e(COUNTRIES_TOTAL) ?> (<a href="/coverage/">coverage</a>).</p>
<div class="table-wrap">
<table class="num narrow">
  <caption>Top countries in the sample</caption>
  <thead><tr><th scope="col">Country</th><th scope="col">Hotels</th></tr></thead>
  <tbody>
<?php foreach (stat_val('countries.top') ?? [] as [$c, $cnt]): ?>
    <tr><th scope="row"><?= e($c) ?></th><td><?= n($cnt) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<h2 id="fill-rates">Field fill rates in the sample</h2>
<p>How many of the <?= n($total) ?> sample hotels have a value in each key field. Empty means the property does not publish it or no source lists it.</p>
<div class="table-wrap">
<table class="num narrow">
  <caption>Fill rates of key fields (of <?= n($total) ?> hotels)</caption>
  <thead><tr><th scope="col">Field</th><th scope="col">Column</th><th scope="col">Hotels</th><th scope="col">Share</th></tr></thead>
  <tbody>
<?php foreach ($fill as $col => $cnt): if (!isset($fillLabels[$col])) { continue; } ?>
    <tr><th scope="row"><?= e($fillLabels[$col]) ?></th><td><?= col($col) ?></td><td><?= n($cnt) ?></td><td><?= pct($cnt, $total) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<p>Need a specific country or field? <a href="/contact/">Tell us</a> and we will send counts for it.</p>

<?= partial('cta') ?>
