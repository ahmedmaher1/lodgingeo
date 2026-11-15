<?php
/** @var array $page */
$near = data_json('sample_preview')['nearest_example'] ?? null;
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Coordinates and a full address for every hotel, plus what surrounds it within 3 km: the nearest place of each service type, how many places of each kind are nearby, which other places to stay compete with it, and which places opened or closed around it.</p>

<h2 id="coordinates">Coordinates and address</h2>
<p>Coordinates are WGS84 decimal degrees (EPSG:4326), the same system GPS and web maps use. The address is given in parts and on one line, so it can be used for mailing, display or geocoding checks.</p>
<?= fields_table([
    'latitude' => null, 'longitude' => null, 'street_address' => null, 'neighborhood' => null, 'ld_city' => 'City.',
    'state' => null, 'postal_code' => null, 'country_code' => null, 'country_name' => null, 'formatted_address' => null,
    'timezone' => null, 'ld_id' => null,
], 'Coordinate and address columns') ?>

<h2 id="neighbourhood">Neighbourhood within 3 km</h2>
<p>Each hotel is placed in context using the open places around it. Distances are straight-line metres from the hotel point.</p>
<?= fields_table([
    'nearest_<service>' => null,
    'count_<group>_3km' => null,
    'places_within_500m' => null,
    'nearby_lodging_*' => null,
    'nearby_openings_*' => null,
    'nearby_closures_*' => null,
    'stable_recommendations_*' => null,
], 'Neighbourhood columns') ?>
<p>The neighbourhood group also includes EV charging and fuel stations nearby, trending and seasonal places, and yearly counts of openings, closures and changes for 2025 and 2026. The <a href="/data-dictionary/">data dictionary</a> lists every column, and the Category counts sheet of the <a href="/hotel-database-sample/">sample</a> has <?= n(stat_val('category_counts.rows')) ?> rows of nearby-place counts per hotel and category.</p>

<?php if ($near && !empty($near['items'])): ?>
<h3 id="example">Nearest services for one sample hotel</h3>
<div class="table-wrap">
<table class="num narrow">
  <caption>Nearest services for <?= e($near['hotel']) ?>, <?= e($near['city']) ?> (sample <?= e(SAMPLE_RELEASE) ?>)</caption>
  <thead><tr><th scope="col">Service</th><th scope="col">Nearest place</th><th scope="col">Metres</th></tr></thead>
  <tbody>
<?php foreach ($near['items'] as $it): ?>
    <tr><th scope="row"><?= e($it['service']) ?></th><td class="text"><?= e($it['name']) ?></td><td><?= n($it['metres']) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>

<h2 id="uses">Uses</h2>
<ul>
  <li>Plot hotels on a map, or join them to your own geographic data by coordinates.</li>
  <li>Score locations: how many restaurants, transport stops or attractions are within 1 km.</li>
  <li>Define a competitive set from <code>nearby_lodging_*</code> for <a href="/data/hotel-prices-by-booking-site/">rate comparisons</a>.</li>
  <li>Watch neighbourhood change with openings and closures (<a href="/data/hotel-history-and-changes/">history and changes</a>).</li>
</ul>

<?= partial('data-closing', ['source' => '/data/hotel-location-data/']) ?>
