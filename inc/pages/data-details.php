<?php
/** @var array $page */
$cls = stat_val('hotel_class') ?? [];
$total = (int) stat_val('sheets.Hotels.rows');
uksort($cls, fn($a, $b) => ($a === 'none') <=> ($b === 'none') ?: strcmp((string) $b, (string) $a));
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">The facts a traveller checks before booking, for every hotel: star class, guest rating and number of reviews, check-in and check-out times, amenities that are available and those that are not, other attributes and the languages spoken.</p>

<h2 id="fields">Fields</h2>
<?= fields_table(['hotel_class', 'rating', 'review_count', 'rating_distribution', 'price_level', 'check_in_time', 'check_out_time',
    'amenities', 'amenities_unavailable', 'attributes', 'languages', 'category', 'categories', 'status'], 'Hotel detail columns') ?>

<h2 id="hotel-class">Hotel class in the sample</h2>
<div class="table-wrap">
<table class="num narrow">
  <caption>Hotel class (<?= col('hotel_class', false) ?>) of the <?= n($total) ?> sample hotels</caption>
  <thead><tr><th scope="col">Class</th><th scope="col">Hotels</th><th scope="col">Share</th></tr></thead>
  <tbody>
<?php foreach ($cls as $k => $cnt): ?>
    <tr><th scope="row"><?= $k === 'none' ? 'Unclassified' : e($k) . '★' ?></th><td><?= n($cnt) ?></td><td><?= pct($cnt, $total) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<p>Unclassified means no star class is listed for the property. Of the other key fields, <?= col('rating') ?> is filled for <?= n(stat_val('fill.fields.rating')) ?>, <?= col('amenities') ?> for <?= n(stat_val('fill.fields.amenities')) ?> and <?= col('check_in_time') ?> for <?= n(stat_val('fill.fields.check_in_time')) ?> of the <?= n($total) ?> sample hotels.</p>

<h2 id="amenities">Amenities</h2>
<p><?= col('amenities') ?> lists what the property says it offers, such as Wi-Fi, parking, pool or airport shuttle. <?= col('amenities_unavailable') ?> lists what it says it does not offer, which is just as useful when filtering: "no parking" is different from "parking unknown". Amenity changes are tracked every month in the <a href="/data/hotel-history-and-changes/">change history</a>; amenities are the most frequently changed field in the sample.</p>

<h2 id="uses">Uses</h2>
<ul>
  <li>Segment prospects by class and rating (<a href="/guides/build-a-hotel-prospect-list/">prospect list guide</a>).</li>
  <li>Fill gaps in hotel content for booking and travel products (<a href="/use-cases/#content-mapping">content mapping</a>).</li>
  <li>Give AI assistants checked facts such as check-in times (<a href="/use-cases/#ai-travel">AI travel assistants</a>).</li>
</ul>

<?= partial('data-closing', ['source' => '/data/hotel-details-and-amenities/']) ?>
