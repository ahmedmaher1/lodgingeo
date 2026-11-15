<?php
/** @var array $page */
$polys = data_json('polygon_preview')['polygons'] ?? [];
$g = stat_val('polygon_grades') ?? [];
$src = stat_val('polygon_sources') ?? [];
$total = (int) stat_val('sheets.Hotels.rows');
$with = array_sum(array_intersect_key($g, array_flip(['A', 'B', 'C', 'D', 'E'])));
$sourceText = [
    'overture_contains' => 'The hotel point lies inside an Overture building.',
    'osm_name' => 'An OpenStreetMap building whose name matches the hotel.',
    'overture_nearest' => 'The nearest Overture building to the hotel point.',
];
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">For each hotel, the footprint of the building it occupies, as a WKT MULTIPOLYGON in WGS84 (EPSG:4326), with its area in square metres, where the footprint came from, and a grade from A to E for how confidently the building matches the hotel.</p>

<?php if ($polys): ?>
<?= partial('polygon-figure', ['poly' => $polys[0], 'named' => true]) ?>
<?php endif; ?>

<h2 id="fields">Fields</h2>
<?= fields_table([
    'building_polygon_wkt' => 'A MULTIPOLYGON in WGS84 (EPSG:4326).',
    'building_area_m2' => null, 'building_id' => null, 'building_name' => null,
    'polygon_source' => 'overture_contains, osm_name or overture_nearest (see below).',
    'building_match_grade' => null, 'building_match_score' => null, 'building_dist_m' => null,
    'building_osm_id' => null, 'building_osm_name' => null, 'places_inside_building' => null, 'contained_places_categories' => null,
], 'Building polygon columns') ?>

<h3 id="sources">Polygon sources</h3>
<div class="table-wrap">
<table class="num">
  <caption>How footprints were matched in the sample</caption>
  <thead><tr><th scope="col"><code>polygon_source</code></th><th scope="col">Meaning</th><th scope="col">Hotels</th></tr></thead>
  <tbody>
<?php foreach ($src as $k => $cnt): ?>
    <tr><th scope="row"><code><?= e($k) ?></code></th><td class="text"><?= e($sourceText[$k] ?? '') ?></td><td><?= n($cnt) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<h2 id="grades">Match grades in the sample</h2>
<p><?= n($with) ?> of the <?= n($total) ?> sample hotels (<?= pct($with, $total) ?>) have a polygon<?= stat_val('polygon_median_area_m2') ? '; the median footprint is ' . n(stat_val('polygon_median_area_m2')) . ' m²' : '' ?>. Grades are match confidence: A is the highest confidence and E the lowest. We recommend grades A–B for automated use and a review for C–E.</p>
<div class="table-wrap">
<table class="num narrow">
  <caption>Polygon match grades (<?= col('building_match_grade', false) ?>), of <?= n($total) ?> hotels</caption>
  <thead><tr><th scope="col">Grade</th><th scope="col">Hotels</th><th scope="col">Share</th></tr></thead>
  <tbody>
<?php foreach ($g as $grade => $cnt): ?>
    <tr><th scope="row"><?= $grade === 'none' ? 'No polygon' : e($grade) ?></th><td><?= n($cnt) ?></td><td><?= pct($cnt, $total) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if (count($polys) > 1): ?>
<h2 id="examples">Footprints from the sample</h2>
<div class="poly-grid">
<?php foreach (array_slice($polys, 1, 3) as $p): ?>
<?= partial('polygon-figure', ['poly' => $p, 'named' => true]) ?>
<?php endforeach; ?>
</div>
<p>The WKT of the first footprint, as it appears in <?= col('building_polygon_wkt') ?>:</p>
<pre><code><?= e(mb_strimwidth($polys[0]['wkt'], 0, 330, ' …')) ?></code></pre>
<?php endif; ?>

<h2 id="how-to-load">How to load it</h2>
<p>The polygons are plain WKT text in a CSV or Excel column, so any GIS tool reads them. The guide <a href="/guides/load-hotel-polygons-wkt/">Load hotel polygons (WKT)</a> covers each tool step by step.</p>
<h3>GeoPandas</h3>
<pre><code>import pandas as pd, geopandas as gpd
from shapely import wkt

df = pd.read_csv("hotels.csv")
df = df[df["building_polygon_wkt"].notna()]
gdf = gpd.GeoDataFrame(df, geometry=df["building_polygon_wkt"].apply(wkt.loads), crs="EPSG:4326")</code></pre>
<h3>PostGIS</h3>
<pre><code>ALTER TABLE hotels ADD COLUMN geom geometry(MultiPolygon, 4326);
UPDATE hotels SET geom = ST_GeomFromText(building_polygon_wkt, 4326)
WHERE building_polygon_wkt IS NOT NULL;</code></pre>
<h3>QGIS</h3>
<p>Layer → Add Layer → Add Delimited Text Layer, choose the CSV, then under Geometry definition select <strong>Well known text (WKT)</strong>, pick <code>building_polygon_wkt</code> as the geometry field and EPSG:4326 as the CRS.</p>
<h3>Converting to GeoJSON</h3>
<pre><code>gdf.to_file("hotel_buildings.geojson", driver="GeoJSON")</code></pre>
<p>Or with GDAL: <code>ogr2ogr -f GeoJSON hotel_buildings.geojson hotels.csv -oo GEOM_POSSIBLE_NAMES=building_polygon_wkt -a_srs EPSG:4326</code></p>

<h2 id="uses">Uses</h2>
<ul>
  <li><strong>Geofencing and footfall attribution:</strong> a building outline is a tighter fence than a radius around a point.</li>
  <li><strong>Property size estimates:</strong> <?= col('building_area_m2') ?> gives the footprint area of each hotel.</li>
  <li><strong>Mapping and GIS:</strong> draw hotels as buildings, not dots.</li>
  <li><strong>Insurance and real estate:</strong> link a hotel to its building and to the other places inside it (<?= col('places_inside_building') ?>).</li>
</ul>
<p>See <a href="/use-cases/#gis-geofencing">mapping, GIS and geofencing</a>.</p>

<h2 id="attribution">Attribution</h2>
<p>Building footprints are derived from OpenStreetMap (© OpenStreetMap contributors, ODbL) and Overture Maps Foundation data, and keep their original licences. Details are on the <a href="/methodology/#attribution">methodology page</a>.</p>

<?= partial('data-closing', ['source' => '/data/hotel-building-polygons/']) ?>
