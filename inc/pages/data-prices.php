<?php
/** @var array $page */
$bs = stat_val('booking_sites') ?? [];
$dv = stat_val('direct_vs_ota') ?? [];
$ex = data_json('rates_example');
$types = ['ota' => 'Online travel agencies (OTAs)', 'metasearch' => 'Metasearch sites', 'other' => 'Other booking sites'];
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">For every hotel, the nightly offer on each booking site that lists it, captured every month: online travel agencies, metasearch sites, other booking sites and the hotel's own website. You can see which sites sell a hotel, at what price, and how the direct price compares with the lowest OTA offer.</p>
<p class="note"><?= e(RATES_CAVEAT) ?></p>

<h2 id="fields">Fields</h2>
<?= fields_table(['hotel_rate_latest', 'hotel_rates_by_site', 'booking_sites_latest', 'booking_otas_latest', 'booking_metasearch_latest',
    'booking_other_sites_latest', 'lowest_offer_latest', 'direct_offer_latest', 'direct_vs_lowest_ota_pct', 'booking_sites_months_seen',
    'rate_history_by_month', 'hotel_avg_rate_2025', 'hotel_avg_rate_2026'], 'Price and booking-site columns') ?>

<h2 id="sample-figures">What the sample shows</h2>
<ul>
  <li><?= n($bs['distinct'] ?? null) ?> distinct booking sites across the <?= n(stat_val('sheets.Hotels.rows')) ?> hotels.</li>
  <li>A median of <?= n($bs['median_per_hotel'] ?? null) ?> sites per hotel, with a maximum of <?= n($bs['max_per_hotel'] ?? null) ?>.</li>
  <li>Of the <?= n($dv['hotels_with_direct'] ?? null) ?> hotels with an official-site (direct) offer, <?= n($dv['above_lowest_ota_pct'] ?? null) ?>% had a direct price above the lowest OTA offer; the median gap was <?= ($dv['median_gap_pct'] ?? 0) >= 0 ? '+' : '' ?><?= n($dv['median_gap_pct'] ?? null) ?>%.</li>
</ul>
<p>The guide <a href="/guides/hotel-direct-vs-ota-prices/">Hotel direct vs OTA prices</a> explains how to read <?= col('direct_vs_lowest_ota_pct') ?>.</p>

<h3 id="sites-by-type">Booking sites seen in the sample, by type</h3>
<?php foreach ($types as $k => $label): $t = $bs['by_type'][$k] ?? null; if (!$t || empty($t['sites'])) { continue; } ?>
<h4><?= e($label) ?><?= !empty($t['count']) ? ' (' . n($t['count']) . ')' : '' ?></h4>
<p class="site-list"><?= e(implode(', ', array_map(fn($s) => $s[0], $t['sites']))) ?><?= !empty($t['partial']) ? ', and others' : '' ?>.</p>
<?php endforeach; ?>
<p class="small">Most frequent first. <?= e(TRADEMARK_LINE) ?></p>

<?php if ($ex): ?>
<h2 id="example">One hotel's rates by site</h2>
<?= partial('rate-chart', ['ex' => $ex]) ?>
<?php if (!empty($ex['history'])): ?>
<div class="table-wrap">
<table class="num narrow">
  <caption>Monthly rate history for the same hotel, <?= e(HISTORY_START) ?> to <?= e(LATEST_RELEASE) ?> (<?= col('rate_history_by_month', false) ?>)</caption>
  <thead><tr><th scope="col">Month</th><th scope="col">Nightly rate</th></tr></thead>
  <tbody>
<?php foreach ($ex['history'] as [$month, $price]): ?>
    <tr><th scope="row"><?= e($month) ?></th><td><?= e(n($price)) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php endif; ?>

<h2 id="caveats">Caveats</h2>
<ul>
  <li><?= e(RATES_CAVEAT) ?></li>
  <li>Offers come from a fixed list of known booking sites. A site that is not on the list is not captured, so "not listed" means "not seen on the sites we check".</li>
  <li>Booking links are not exported; the data names the site and its offer.</li>
  <li><?= e(TRADEMARK_LINE) ?></li>
</ul>

<h2 id="uses">Uses</h2>
<ul>
  <li><strong>Rate parity monitoring:</strong> find hotels whose own website is more expensive than an OTA.</li>
  <li><strong>Revenue-management benchmarks:</strong> compare a hotel's rates with its competitive set (<a href="/data/hotel-location-data/#neighbourhood">nearby lodging</a>).</li>
  <li><strong>Distribution mapping:</strong> see which sites list which hotels, by country and chain.</li>
  <li><strong>City price levels:</strong> aggregate the latest and average rates by city and hotel class.</li>
</ul>
<p>More on <a href="/use-cases/#revenue-management">revenue management and rate intelligence</a>.</p>

<?= partial('data-closing', ['source' => '/data/hotel-prices-by-booking-site/']) ?>
