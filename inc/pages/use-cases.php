<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Six ways teams use the hotel data, with the columns each one relies on. Each is a starting point: tell us yours and we will say which fields and countries fit.</p>
<ul class="toc">
  <li><a href="#sales-prospecting">Sales prospecting for hotel suppliers</a></li>
  <li><a href="#content-mapping">OTAs, bedbanks and travel tech</a></li>
  <li><a href="#revenue-management">Revenue management and rate intelligence</a></li>
  <li><a href="#real-estate">Real estate, investors and hotel development</a></li>
  <li><a href="#gis-geofencing">Mapping, GIS and geofencing</a></li>
  <li><a href="#ai-travel">AI travel assistants and search</a></li>
</ul>

<section id="sales-prospecting" aria-labelledby="uc1">
<h2 id="uc1">Sales prospecting for hotel suppliers</h2>
<p>Companies that sell to hotels need to know which hotels exist in their territory, how big and how upscale they are, who they belong to and how to reach them. Linen and amenity suppliers, food and beverage distributors, property management and channel-manager vendors, and revenue-management consultancies all start from the same list.</p>
<p>Filter by country and city, then by <?= col('hotel_class') ?> and <?= col('rating') ?> to match your product to the right segment. Use <?= col('brand') ?> and <?= col('chain_from_website') ?> to separate independents from chain hotels, since they buy differently. <?= col('review_count') ?> is a rough proxy for size and activity, and <?= col('building_area_m2') ?> gives the footprint of the building. Contact fields give the route in: <?= col('phone') ?>, <?= col('website') ?> and, in the full dataset, publicly listed email, WhatsApp and social profiles.</p>
<p>Monthly releases keep the list current: new hotels appear with their <?= col('first_seen_month') ?>, closed ones drop out of active segments. The guide <a href="/guides/build-a-hotel-prospect-list/">How to build a hotel prospect list in Excel</a> walks through the filters.</p>
<p><a class="btn" href="/data/hotel-contact-data/">Contact details</a></p>
</section>

<section id="content-mapping" aria-labelledby="uc2">
<h2 id="uc2">OTAs, bedbanks and travel tech: content mapping and supply gaps</h2>
<p>Online travel agencies, bedbanks and travel-technology firms hold hotel inventory from several suppliers, each with its own IDs and spellings. Mapping those records to one reference, and seeing which hotels are missing, is a constant job.</p>
<p>Send your hotel list and we match it to our places: each row comes back with our stable <?= col('ld_id') ?>, coordinates, <?= col('name_score') ?>, <?= col('city_score') ?> and a <?= col('match_tier') ?>, so high-confidence matches can be accepted automatically and the rest reviewed. Because <code>ld_id</code> does not change between releases, later monthly files join on it directly.</p>
<p>To find supply gaps, compare your matched hotels with all hotels in a city or country. <?= col('booking_sites_latest') ?> shows which sites already sell each hotel, and the detail columns (<?= col('amenities') ?>, <?= col('check_in_time') ?>, <?= col('hotel_class') ?>) fill missing content. See <a href="/hotel-matching/">hotel list matching</a>.</p>
<p><a class="btn" href="/hotel-matching/">List matching</a></p>
</section>

<section id="revenue-management" aria-labelledby="uc3">
<h2 id="uc3">Revenue management and rate intelligence</h2>
<p>Revenue managers and rate-intelligence teams want to know what a hotel charges, where, and how that compares with the hotel down the road. The price columns capture each hotel's nightly offer on every booking site that lists it, every month.</p>
<p><?= col('hotel_rates_by_site') ?> gives the offer per site, <?= col('lowest_offer_latest') ?> the lowest, and <?= col('direct_offer_latest') ?> the price on the hotel's own website. <?= col('direct_vs_lowest_ota_pct') ?> shows rate parity at a glance: in the sample, <?= n(stat_val('direct_vs_ota.above_lowest_ota_pct')) ?>% of hotels with a direct offer were priced above the lowest OTA. <?= col('rate_history_by_month') ?> and the yearly averages give the trend, and <code>nearby_lodging_*</code> defines a competitive set from the neighbourhood.</p>
<p><?= e(RATES_CAVEAT) ?> The guide <a href="/guides/hotel-direct-vs-ota-prices/">Hotel direct vs OTA prices</a> explains how to read the gap.</p>
<p><a class="btn" href="/data/hotel-prices-by-booking-site/">Prices by booking site</a></p>
</section>

<section id="real-estate" aria-labelledby="uc4">
<h2 id="uc4">Real estate, investors and hotel development</h2>
<p>Investors, developers and real-estate analysts look at where hotels are opening and closing, and at how a neighbourhood is changing around a site. Because every release since January 2025 is compared with the previous one, the data records those events month by month.</p>
<p><?= col('first_seen_month') ?>, <?= col('closed_month') ?> and <?= col('lifecycle_stage') ?> describe each hotel's life, and <?= col('status_history') ?> shows how its status moved. Around each hotel, <code>nearby_openings_*</code>, <code>nearby_closures_*</code> and <?= col('neighbourhood_momentum_12m') ?> summarise what is opening and closing within 3 km, and <?= col('possible_replacements') ?> flags places that may have taken over a closed site. <?= col('building_area_m2') ?> and <?= col('places_inside_building') ?> describe the building itself.</p>
<p>A place missing from one release is treated as a gap, not a closure, so the closure counts are conservative. See <a href="/guides/track-hotel-openings-and-closures/">Track hotel openings and closures</a>.</p>
<p><a class="btn" href="/data/hotel-history-and-changes/">History and changes</a></p>
</section>

<section id="gis-geofencing" aria-labelledby="uc5">
<h2 id="uc5">Mapping, GIS and geofencing</h2>
<p>GIS analysts, mobility and location-analytics teams need more than a point: they need the outline of the building. Each hotel is matched to its building footprint from OpenStreetMap or Overture where one exists, and delivered as a WKT MULTIPOLYGON in WGS84 in <?= col('building_polygon_wkt') ?>.</p>
<p>A footprint makes a tight geofence for footfall attribution: a device inside the polygon is at the hotel, not at the café next door. <?= col('building_match_grade') ?> (A to E) and <?= col('polygon_source') ?> tell you how much to trust each match; grades A and B suit automated use. In the sample, <?= n(stat_val('polygon_grades.A')) ?> hotels have a grade-A footprint. The neighbourhood counts (<code>count_&lt;group&gt;_3km</code>) add context for location scoring.</p>
<p>The polygons load straight into GeoPandas, PostGIS and QGIS: see <a href="/guides/load-hotel-polygons-wkt/">Load hotel polygons (WKT)</a>.</p>
<p><a class="btn" href="/data/hotel-building-polygons/">Building polygons</a></p>
</section>

<section id="ai-travel" aria-labelledby="uc6">
<h2 id="uc6">AI travel assistants and search</h2>
<p>AI travel assistants, chat products and travel search tools answer questions such as "Which four-star hotels near the old town have parking and a late check-out?" Answers like that need checked, structured facts rather than text scraped on the fly.</p>
<p>The data gives each hotel's <?= col('hotel_class') ?>, <?= col('rating') ?>, <?= col('amenities') ?> and <?= col('amenities_unavailable') ?>, <?= col('check_in_time') ?> and <?= col('check_out_time') ?>, <?= col('languages') ?>, coordinates and the nearest services within 3 km. <?= col('facts_change_timeline') ?> and <?= col('last_confirmed_month') ?> show how recently a fact was seen, so an assistant can prefer current information and say when it is not sure.</p>
<p>The monthly change files make it possible to refresh a retrieval index incrementally instead of rebuilding it. See <a href="/formats-and-delivery/">formats and delivery</a>.</p>
<p><a class="btn" href="/data/hotel-details-and-amenities/">Details and amenities</a></p>
</section>

<?= partial('cta') ?>
