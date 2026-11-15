<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">The hotel data is one table with a row per property. Its columns fall into eight groups, from coordinates to monthly change history. This page summarises each group; the linked pages list the columns and show what the sample contains.</p>
<?= partial('fact-box') ?>

<h2 id="location">Location and address</h2>
<p>Coordinates in WGS84 decimal degrees, the full address split into parts and on one line, and the time zone. Every row has a stable Lodgingeo ID, <?= col('ld_id') ?>, which stays the same from one monthly release to the next.</p>
<p>Key columns: <?= col('latitude') ?> <?= col('longitude') ?> <?= col('street_address') ?> <?= col('postal_code') ?> <?= col('formatted_address') ?> <?= col('timezone') ?>. <a href="/data/hotel-location-data/">Location and neighbourhood data</a>.</p>

<h2 id="contacts">Contact details</h2>
<p>Business contact details as published by each property: phone in international format, website and domain, chain and brand, and in the full dataset email, WhatsApp and social profiles.</p>
<p>Key columns: <?= col('phone') ?> <?= col('website') ?> <?= col('domain') ?> <?= col('brand') ?>. <a href="/data/hotel-contact-data/">Hotel contact details</a>.</p>

<h2 id="prices">Prices across booking sites</h2>
<p>For each hotel, the nightly offer on each booking site that lists it, the lowest offer, the offer on the hotel's own website, and the gap between the two. Rates are captured every month, so each hotel also has a rate history.</p>
<p>Key columns: <?= col('hotel_rates_by_site') ?> <?= col('lowest_offer_latest') ?> <?= col('direct_vs_lowest_ota_pct') ?> <?= col('rate_history_by_month') ?>. <a href="/data/hotel-prices-by-booking-site/">Hotel prices by booking site</a>.</p>

<h2 id="polygons">Building polygons</h2>
<p>The footprint of the building each hotel is in, as a WKT MULTIPOLYGON in WGS84, with its area and a grade from A to E for how confidently it matches the hotel.</p>
<p>Key columns: <?= col('building_polygon_wkt') ?> <?= col('building_area_m2') ?> <?= col('building_match_grade') ?> <?= col('polygon_source') ?>. <a href="/data/hotel-building-polygons/">Hotel building polygons</a>.</p>

<h2 id="details">Details and amenities</h2>
<p>Star class, guest rating, review count and the distribution of reviews by star, check-in and check-out times, amenities available and unavailable, attributes and languages spoken.</p>
<p>Key columns: <?= col('hotel_class') ?> <?= col('rating') ?> <?= col('review_count') ?> <?= col('amenities') ?>. <a href="/data/hotel-details-and-amenities/">Hotel details and amenities</a>.</p>

<h2 id="neighbourhood">Neighbourhood</h2>
<p>What is around each hotel within 3 km: the nearest place of each service type and its distance, counts of places by group and radius, other lodging nearby, and places that opened or closed.</p>
<p>Key columns: <?= col('nearest_<service>') ?> <?= col('count_<group>_3km') ?> <?= col('places_within_500m') ?> <?= col('nearby_lodging_*') ?>. <a href="/data/hotel-location-data/#neighbourhood">Neighbourhood data</a>.</p>

<h2 id="history">History and changes</h2>
<p>Monthly releases since January 2025 are compared with each other, so every hotel has a first-seen month, a last-confirmed month, a closed month where it closed, and a timeline of changed facts.</p>
<p>Key columns: <?= col('first_seen_month') ?> <?= col('closed_month') ?> <?= col('name_history') ?> <?= col('facts_change_timeline') ?>. <a href="/data/hotel-history-and-changes/">Hotel history and changes</a>.</p>

<h2 id="matching">List matching</h2>
<p>When you send your own hotel list, each of your rows is matched to a Lodgingeo place and returned with all of the columns above, a name score, a city score and a match tier.</p>
<p>Key columns: <?= col('name_score') ?> <?= col('city_score') ?> <?= col('confidence') ?> <?= col('match_tier') ?>. <a href="/hotel-matching/">Hotel list matching</a>.</p>

<?= partial('data-closing', ['source' => '/data/']) ?>
