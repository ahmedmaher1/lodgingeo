<?php
/** @var array $page */
$ch = stat_val('changes') ?? [];
$types = $ch['types'] ?? [];
$total = (int) stat_val('sheets.Hotels.rows');
?>
<article>
<h1><?= e($page['h1']) ?></h1>
<?= partial('byline') ?>
<p class="lead">Every monthly release of the hotel data is compared with the one before. The result is a set of lifecycle columns on each hotel and a log of every change, which together let you list new hotels, closures and reopenings for any month since the history began in January 2025.</p>

<h2 id="principle">The rule behind the history</h2>
<p>Each release is matched place by place to the previous release, keeping each place's stable <?= col('ld_id') ?>. Two rules shape what you see:</p>
<ul>
  <li><strong>New means newly listed.</strong> A hotel is "first seen" in the month it first appears in a release. That is usually close to an opening, but it can also be an existing hotel that started being listed.</li>
  <li><strong>Gaps are gaps, not closures.</strong> A place missing from one release is treated as a data gap. A closure is recorded only when the place is marked closed. This keeps closure counts conservative.</li>
</ul>
<p>Because the history starts in <?= e(month_label(HISTORY_START)) ?>, every hotel already listed then has a first-seen month of <?= e(HISTORY_START) ?>. Count openings only from the following months.</p>

<h2 id="lifecycle">Lifecycle columns on each hotel</h2>
<?= fields_table(['first_seen_month', 'last_seen_month', 'last_confirmed_month', 'closed_month', 'continuity_months', 'lifecycle_stage',
    'status_history', 'name_history', 'oldest_seen_date'], 'Lifecycle columns') ?>
<p><?= col('oldest_seen_date') ?> helps separate the two meanings of "new": a hotel first seen in 2026 but with an oldest-seen date years earlier is a newly listed existing hotel, not an opening.</p>

<h2 id="change-log">The change log</h2>
<p>The Hotel changes sheet of the sample has one row per change, with its month in <code>cycle_month</code>. It holds <?= n($ch['total'] ?? null) ?> records for the <?= n($total) ?> sample hotels:</p>
<div class="table-wrap">
<table class="num narrow">
  <caption>Change records in the sample, by type</caption>
  <thead><tr><th scope="col">Change type</th><th scope="col">Records</th></tr></thead>
  <tbody>
<?php foreach ($types as $t => $cnt): ?>
    <tr><th scope="row"><code><?= e($t) ?></code></th><td><?= n($cnt) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<p>Most records are ordinary fact changes. The most-changed fields in the sample are <?= e(implode(', ', array_map(fn($f) => $f[0] . ' (' . n($f[1]) . ')', $ch['top_fields'] ?? []))) ?>. The lifecycle events are rarer: <?= n($types['first_listed'] ?? null) ?> first listings, <?= n($types['closed'] ?? null) ?> closures and <?= n($types['reopened'] ?? null) ?> reopenings across the sample's history.</p>

<h2 id="new-hotels">Recipe: new hotels this month</h2>
<ol>
  <li>In the Hotels sheet, filter <?= col('first_seen_month') ?> to the latest release, <?= e(LATEST_RELEASE) ?>.</li>
  <li>Check <?= col('oldest_seen_date') ?> to drop hotels that were listed long before.</li>
  <li>Filter by country, <?= col('hotel_class') ?> or <?= col('brand') ?> to your market.</li>
</ol>
<p>For a monthly series, build a pivot table with <code>first_seen_month</code> in rows and a count of <code>ld_id</code> as the value, leaving out <?= e(HISTORY_START) ?>.</p>

<h2 id="closures">Recipe: closures and reopenings</h2>
<ol>
  <li>Filter <?= col('closed_month') ?> to non-blank values for all recorded closures, or to one month.</li>
  <li>Compare <?= col('last_confirmed_month') ?> with <?= col('last_seen_month') ?>: a hotel still listed but not confirmed operating for several months deserves a check.</li>
  <li>For reopenings, filter the Hotel changes sheet on the <code>reopened</code> type; <?= col('status_history') ?> on the hotel shows the full sequence.</li>
</ol>

<h2 id="neighbourhood">Recipe: neighbourhood momentum</h2>
<p>For site selection and investment work, what happens around a hotel can matter as much as the hotel itself. Within 3 km of each hotel the data counts places that opened (<code>nearby_openings_*</code>) and closed (<code>nearby_closures_*</code>), and summarises the balance in <?= col('neighbourhood_momentum_12m') ?>. <?= col('possible_replacements') ?> flags places that may have taken over a closed place's spot.</p>
<p>Sort hotels by <code>neighbourhood_momentum_12m</code> within a city to see which areas are gaining places and which are losing them. Combined with <code>nearby_lodging_*</code>, it shows whether new competition is arriving next to a property.</p>

<h2 id="monthly-files">Keeping up month by month</h2>
<p>Monthly subscribers receive each release with a change file, so a tracking sheet only needs the new rows each month. Join them on <code>ld_id</code>; it does not change between releases. See <a href="/formats-and-delivery/#updates">monthly updates</a> and the <a href="/releases/">release log</a>.</p>

<h2 id="report">Example: a monthly openings report</h2>
<p>Putting the recipes together, a simple monthly report for one market takes four steps. Filter the Hotels sheet to your countries. Count hotels whose <code>first_seen_month</code> is the new release and whose <code>oldest_seen_date</code> is recent: these are your likely openings. Count hotels whose <code>closed_month</code> is the new release: these are the recorded closures. Then list the hotels with the strongest positive and negative <code>neighbourhood_momentum_12m</code> to show where activity is concentrated. Kept as one sheet per month, this gives a running series that is easy to chart, and every number in it can be traced back to individual hotel rows.</p>

<h2 id="caveats">Caveats</h2>
<ul>
  <li>First seen is not an opening date. Use <code>oldest_seen_date</code> and your own checks before calling a hotel new.</li>
  <li>Closures are recorded only when a place is marked closed, so a closure can appear in the data later than it happened.</li>
  <li>The sample covers <?= n($total) ?> hotels; its counts illustrate the columns rather than measure a market.</li>
</ul>

<h2 id="next">Where to go next</h2>
<p>The <a href="/data/hotel-history-and-changes/">history and changes page</a> lists every history column, and <a href="/use-cases/#real-estate">real estate and hotel development</a> describes the use case.</p>
</article>
<?= partial('cta') ?>
