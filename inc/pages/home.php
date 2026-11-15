<?php
/** @var array $page */
$preview = data_json('sample_preview');
$polys = data_json('polygon_preview')['polygons'] ?? [];
$hotels = stat_val('sheets.Hotels.rows');
$countries = stat_val('countries.count');
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead"><?= e(PLACES_TO_STAY_TOTAL) ?> places to stay in <?= e(COUNTRIES_TOTAL) ?>, including <?= e(HOTELS_TOTAL) ?> hotels: coordinates and addresses; phone, email, website, WhatsApp and social contacts where publicly listed; nightly rates from up to 40+ booking sites per hotel; building polygons; and monthly change history since January 2025. Delivered as CSV, Excel or Google Sheets, by country or worldwide.</p>

<?= partial('fact-box') ?>
<p class="buttons"><a class="btn btn-primary" href="#sample">Download free sample</a> <a class="btn" href="/contact/">Contact us</a></p>

<h2 id="whats-in-the-data">What's in the data</h2>
<p>Every hotel row carries the columns below. The <a href="/data-dictionary/">data dictionary</a> lists every field, and the <a href="/data/">data overview</a> explains each group.</p>
<?= partial('field-groups') ?>

<h2 id="sample-at-a-glance">The sample at a glance</h2>
<p>The free sample is one Excel file with <?= n($hotels) ?> hotels from <?= n($countries) ?> countries and <?= n(stat_val('cities.count')) ?> cities, <?= n(stat_val('sheets.Hotels.columns')) ?> columns per hotel, a column guide, <?= n(stat_val('changes.total')) ?> monthly change records and <?= n(stat_val('sheets.Buildings.rows')) ?> building polygons.</p>
<?php if ($preview && !empty($preview['rows'])): ?>
<div class="table-wrap">
<table class="num">
  <caption>From the <?= e(SAMPLE_RELEASE) ?> sample · <?= n($hotels) ?> hotels · <?= n($countries) ?> countries</caption>
  <thead><tr><th scope="col">Hotel</th><th scope="col">City</th><th scope="col">Country</th><th scope="col">Lat</th><th scope="col">Lon</th><th scope="col">Class</th><th scope="col">Rating</th><th scope="col">Booking sites</th><th scope="col">Lowest offer</th><th scope="col">Building m²</th><th scope="col">Polygon grade</th></tr></thead>
  <tbody>
<?php foreach ($preview['rows'] as $r): ?>
    <tr><th scope="row"><?= e($r['hotel']) ?></th><td><?= e($r['city']) ?></td><td><?= e($r['country']) ?></td><td><?= e($r['lat']) ?></td><td><?= e($r['lon']) ?></td><td><?= e($r['class']) ?></td><td><?= e($r['rating']) ?></td><td><?= e($r['booking_sites']) ?></td><td><?= e($r['lowest_offer']) ?></td><td><?= e($r['building_m2'] !== null ? n($r['building_m2']) : '') ?></td><td><?= e($r['grade']) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<p class="small"><?= e(RATES_CAVEAT) ?></p>
<?php endif; ?>
<?php if ($polys): ?>
<?= partial('polygon-figure', ['poly' => $polys[1] ?? $polys[0]]) ?>
<p>Each hotel is matched to its building footprint where one exists: <?= n(stat_val('polygon_grades.A') + stat_val('polygon_grades.B') + stat_val('polygon_grades.C') + stat_val('polygon_grades.D') + stat_val('polygon_grades.E')) ?> of the <?= n($hotels) ?> sample hotels have a polygon. Read more about <a href="/data/hotel-building-polygons/">building polygons</a>.</p>
<?php endif; ?>

<section class="block" id="sample" aria-labelledby="sample-h">
  <h2 id="sample-h">Download the free sample</h2>
  <p>Enter your name and work email and we send the download link straight away.</p>
  <?= partial('sample-form', ['variant' => 'full', 'source' => '/']) ?>
</section>

<h2 id="who-uses-it">Who uses it</h2>
<ul>
  <li><a href="/use-cases/#sales-prospecting">Hotel suppliers</a> build prospect lists by country, class, rating and chain.</li>
  <li><a href="/use-cases/#content-mapping">OTAs, bedbanks and travel tech</a> map their hotel content and find supply gaps.</li>
  <li><a href="/use-cases/#revenue-management">Revenue managers</a> compare rates across booking sites and against the hotel's own site.</li>
  <li><a href="/use-cases/#real-estate">Real estate and investment teams</a> follow openings, closures and neighbourhood momentum.</li>
  <li><a href="/use-cases/#gis-geofencing">GIS and mobility teams</a> geofence hotels with their building polygons.</li>
  <li><a href="/use-cases/#ai-travel">AI travel assistants</a> ground answers in checked hotel facts.</li>
</ul>

<h2 id="coverage">Coverage</h2>
<p>The data covers <?= e(PLACES_TO_STAY_TOTAL) ?> places to stay in <?= e(COUNTRIES_TOTAL) ?> and is sold as one global dataset, worldwide or for the countries you choose. See <a href="/coverage/">worldwide coverage</a>.</p>

<h2 id="faq">Questions</h2>
<dl class="faq">
<?php foreach (faq_items(true) as $f): ?>
  <dt><?= e($f['q']) ?></dt>
  <dd><?= $f['a'] ?></dd>
<?php endforeach; ?>
</dl>
<p><a href="/faq/">All questions and answers</a></p>

<section class="block" id="contact" aria-labelledby="contact-h">
  <h2 id="contact-h">Ask a question</h2>
  <p>Tell us what you need. <?= e(REPLY_PROMISE) ?> Prefer chat? <?= whatsapp_link() ?></p>
  <?= partial('contact-form', ['variant' => 'short', 'source' => '/']) ?>
</section>

<?= partial('cta') ?>
