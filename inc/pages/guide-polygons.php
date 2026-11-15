<?php
/** @var array $page */
$g = stat_val('polygon_grades') ?? [];
$with = array_sum(array_intersect_key($g, array_flip(['A', 'B', 'C', 'D', 'E'])));
$total = (int) stat_val('sheets.Hotels.rows');
$ab = ($g['A'] ?? 0) + ($g['B'] ?? 0);
?>
<article>
<h1><?= e($page['h1']) ?></h1>
<?= partial('byline') ?>
<p class="lead">Each hotel in the data can carry the outline of the building it occupies, stored as plain WKT text. This guide shows how to load those footprints into GeoPandas, PostGIS and QGIS, which rows to trust, and how to convert them to GeoJSON.</p>

<h2 id="columns">What you are loading</h2>
<p>The footprint lives in <?= col('building_polygon_wkt') ?>: a <code>MULTIPOLYGON</code> in WGS84 longitude/latitude (EPSG:4326), the same coordinate system as the hotel's <?= col('latitude') ?> and <?= col('longitude') ?>. A multipolygon rather than a polygon, because some buildings are mapped as several parts. Next to it sit the columns that tell you how far to trust it:</p>
<ul>
  <li><?= col('building_match_grade') ?>: A (highest confidence) to E (lowest);</li>
  <li><?= col('polygon_source') ?>: <code>overture_contains</code> when the hotel point lies inside an Overture building, <code>osm_name</code> when an OpenStreetMap building's name matches the hotel, <code>overture_nearest</code> when the nearest Overture building was taken;</li>
  <li><?= col('building_dist_m') ?>: distance from the hotel point to the building;</li>
  <li><?= col('building_area_m2') ?>: footprint area in square metres.</li>
</ul>
<p>In the free sample, <?= n($with) ?> of the <?= n($total) ?> hotels (<?= pct($with, $total) ?>) have a polygon. The grades break down as A <?= n($g['A'] ?? null) ?>, B <?= n($g['B'] ?? null) ?>, C <?= n($g['C'] ?? null) ?>, D <?= n($g['D'] ?? null) ?> and E <?= n($g['E'] ?? null) ?>, and <?= n($g['none'] ?? null) ?> hotels have none. The median footprint is <?= n(stat_val('polygon_median_area_m2')) ?> m².</p>

<h2 id="choose">Step 1: decide which polygons to use</h2>
<p>For anything automated, such as geofences, point-in-polygon joins or area statistics, keep grades A and B. That is <?= n($ab) ?> of the <?= n($with) ?> sample polygons. Grades C to E are still useful, but look at them on a map before relying on them: a nearest-building match in a dense old town can pick the neighbour. If you only need the hotel's location, the point coordinates are always there, polygon or not.</p>
<p>A WKT value is text, so the first job in every tool is the same: drop empty rows, parse the text into a geometry, and declare EPSG:4326.</p>

<h2 id="geopandas">Step 2a: GeoPandas</h2>
<p>Read the Hotels sheet (or the CSV you received), parse the WKT with Shapely and build a GeoDataFrame:</p>
<pre><code>import pandas as pd
import geopandas as gpd
from shapely import wkt

df = pd.read_excel("<?= e(SAMPLE_FILENAME) ?>", sheet_name="Hotels")
# or: df = pd.read_csv("hotels.csv")
df = df[df["building_polygon_wkt"].notna()]
df = df[df["building_match_grade"].isin(["A", "B"])]

gdf = gpd.GeoDataFrame(
    df,
    geometry=df["building_polygon_wkt"].apply(wkt.loads),
    crs="EPSG:4326",
)
print(len(gdf), gdf.geometry.geom_type.unique())</code></pre>
<p>Two checks are worth running once. First, validity: <code>gdf.geometry.is_valid.all()</code>; if it is not, <code>gdf.geometry = gdf.geometry.make_valid()</code> repairs self-intersections. Second, area: degrees are not metres, so project before measuring and compare with the delivered column:</p>
<pre><code>metric = gdf.to_crs(gdf.estimate_utm_crs())
gdf["area_check_m2"] = metric.area
print(gdf[["building_area_m2", "area_check_m2"]].head())</code></pre>
<p><code>estimate_utm_crs()</code> picks one UTM zone for the whole frame, which is fine for a city or a country but not for a worldwide file; for that, project per country or use an equal-area projection.</p>

<h2 id="postgis">Step 2b: PostGIS</h2>
<p>Load the CSV into a table with the WKT as text, then add a geometry column:</p>
<pre><code>CREATE TABLE hotels (
  ld_id text PRIMARY KEY,
  name text,
  latitude double precision,
  longitude double precision,
  building_polygon_wkt text,
  building_match_grade text,
  building_area_m2 double precision
);
-- psql: \copy hotels FROM 'hotels.csv' WITH (FORMAT csv, HEADER true)

ALTER TABLE hotels ADD COLUMN geom geometry(MultiPolygon, 4326);
UPDATE hotels
SET geom = ST_Multi(ST_GeomFromText(building_polygon_wkt, 4326))
WHERE building_polygon_wkt IS NOT NULL;

CREATE INDEX hotels_geom_gix ON hotels USING gist (geom);</code></pre>
<p>If your CSV has more columns than the table, list the ones you want in the <code>\copy</code> statement or load into a wide staging table first. <code>ST_Multi</code> keeps the column type consistent even if a footprint arrives as a single polygon. To measure area in square metres, cast to geography: <code>ST_Area(geom::geography)</code>. A typical next step is a point-in-polygon join, for example counting your own location events inside each hotel building with <code>ST_Contains(h.geom, e.geom)</code>, filtered to <code>building_match_grade IN ('A', 'B')</code>.</p>

<h2 id="qgis">Step 2c: QGIS</h2>
<ol>
  <li>Save the Hotels sheet as CSV (or use the CSV you received).</li>
  <li>Open <strong>Layer → Add Layer → Add Delimited Text Layer</strong> and choose the file.</li>
  <li>Under <strong>Geometry definition</strong>, select <strong>Well known text (WKT)</strong> and set the geometry field to <code>building_polygon_wkt</code>.</li>
  <li>Set the geometry CRS to <strong>EPSG:4326 – WGS 84</strong> and click Add.</li>
</ol>
<p>Rows without a polygon are skipped for that layer. To see the points too, add the same file a second time with <strong>Point coordinates</strong>, using <code>longitude</code> as X and <code>latitude</code> as Y. Styling the polygon layer by <code>building_match_grade</code> (categorised) is the fastest way to review the C to E matches against a basemap.</p>

<h2 id="geojson">Step 3: convert to GeoJSON</h2>
<p>GeoJSON is expected to be in WGS84 longitude/latitude, which is what the data already uses, so no reprojection is needed. From GeoPandas:</p>
<pre><code>gdf[["ld_id", "name", "building_area_m2", "building_match_grade", "geometry"]] \
    .to_file("hotel_buildings.geojson", driver="GeoJSON")</code></pre>
<p>Or straight from the CSV with GDAL:</p>
<pre><code>ogr2ogr -f GeoJSON hotel_buildings.geojson hotels.csv \
  -oo GEOM_POSSIBLE_NAMES=building_polygon_wkt -a_srs EPSG:4326</code></pre>
<p>Hotels without a footprint come out as features with a null geometry; filter them out first if your viewer objects.</p>
<p>Keep the attribute list short for web maps: a worldwide file with every column is large, and most map viewers only need an ID, a name and the grade.</p>

<h2 id="pitfalls">Common pitfalls</h2>
<ul>
  <li><strong>Axis order.</strong> WKT here is longitude first, then latitude. Swapping them puts Madrid in the Indian Ocean.</li>
  <li><strong>Area in degrees.</strong> <code>.area</code> on an EPSG:4326 geometry returns square degrees. Project first, or use <?= col('building_area_m2') ?>.</li>
  <li><strong>Excel cell length.</strong> An Excel cell holds at most 32,767 characters. Very large footprints are safer in the CSV delivery.</li>
  <li><strong>One building, several hotels.</strong> Hotels that share a building share a footprint; <?= col('places_inside_building') ?> tells you how many places sit inside it.</li>
</ul>

<h2 id="next">Where to go next</h2>
<p>The <a href="/data/hotel-building-polygons/">building polygons page</a> shows example footprints from the sample, and the <a href="/data-dictionary/#building_polygon_wkt">data dictionary</a> lists every building column. Attribution for OpenStreetMap and Overture data is on the <a href="/methodology/#attribution">methodology page</a>.</p>
</article>
<?= partial('cta') ?>
