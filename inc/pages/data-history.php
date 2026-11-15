<?php
/** @var array $page */
$ch = stat_val('changes') ?? [];
$typeText = [
    'field_changed' => 'A field value changed since the previous release.',
    'field_added' => 'A field gained a value it did not have before.',
    'first_listed' => 'The hotel appeared for the first time.',
    'reopened' => 'A hotel marked closed was operating again.',
    'closed' => 'The hotel was marked closed.',
];
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">The data has been released every month since January 2025, and each release is compared with the one before. That gives every hotel a history: when it was first seen, when it was last confirmed, whether and when it closed, and which of its facts changed in which month.</p>

<h2 id="background">How changes are detected</h2>
<p>Each monthly release is matched place by place to the previous release. A changed value becomes a change record with its month (<code>cycle_month</code>). A place missing from a single release is treated as a data gap, not a closure: a closure is recorded only when the place is marked closed. Read more in the <a href="/methodology/">methodology</a>.</p>

<h2 id="hotel-fields">Hotel-level fields</h2>
<?= fields_table(['first_seen_month', 'last_seen_month', 'last_confirmed_month', 'closed_month', 'continuity_months', 'lifecycle_stage',
    'status_history', 'name_history', 'amenities_added', 'facts_change_timeline', 'rating_trend_12m', 'reviews_gained_last_month',
    'reviews_gained_all_time'], 'History columns per hotel') ?>

<h2 id="neighbourhood-fields">Neighbourhood fields</h2>
<?= fields_table(['nearby_openings_*', 'nearby_closures_*', 'neighbourhood_momentum_12m', 'possible_replacements'], 'Neighbourhood change columns') ?>

<h2 id="sample-figures">What the sample shows</h2>
<p>The Hotel changes sheet of the sample holds <?= n($ch['total'] ?? null) ?> change records for the <?= n(stat_val('sheets.Hotels.rows')) ?> hotels.</p>
<div class="table-wrap">
<table class="num">
  <caption>Change records in the sample, by type</caption>
  <thead><tr><th scope="col">Change type</th><th scope="col">Meaning</th><th scope="col">Records</th></tr></thead>
  <tbody>
<?php foreach ($ch['types'] ?? [] as $t => $cnt): ?>
    <tr><th scope="row"><code><?= e($t) ?></code></th><td class="text"><?= e($typeText[$t] ?? '') ?></td><td><?= n($cnt) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<div class="table-wrap">
<table class="num narrow">
  <caption>Most-changed fields in the sample</caption>
  <thead><tr><th scope="col">Field</th><th scope="col">Changes</th></tr></thead>
  <tbody>
<?php foreach ($ch['top_fields'] ?? [] as [$f, $cnt]): ?>
    <tr><th scope="row"><code><?= e($f) ?></code></th><td><?= n($cnt) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<p>See the monthly <a href="/releases/">release log</a>, and the guide <a href="/guides/track-hotel-openings-and-closures/">Track hotel openings and closures</a>.</p>

<h2 id="uses">Uses</h2>
<ul>
  <li>Lists of new hotels by month, country or chain.</li>
  <li>Closures and reopenings for portfolio and market monitoring.</li>
  <li>Neighbourhood momentum for <a href="/use-cases/#real-estate">real estate and development</a> decisions.</li>
  <li>Keeping your own hotel database current with monthly change files (<a href="/formats-and-delivery/">delivery</a>).</li>
</ul>

<?= partial('data-closing', ['source' => '/data/hotel-history-and-changes/']) ?>
