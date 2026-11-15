<?php
/** @var array $page */
$total = (int) stat_val('sheets.Hotels.rows');
$cls = stat_val('hotel_class') ?? [];
$fill = stat_val('fill.fields') ?? [];
?>
<article>
<h1><?= e($page['h1']) ?></h1>
<?= partial('byline') ?>
<p class="lead">If you sell to hotels, the hotel data is a ready-made list of accounts. This guide shows how to cut it down in Excel to the hotels that fit your product, by country, class, rating, chain and the contact details available, and which columns to keep for your CRM.</p>

<h2 id="start">Start from the right sheet</h2>
<p>Open the Hotels sheet of the <a href="/hotel-database-sample/">free sample</a> (or your delivered file). There is one row per hotel. Select any cell and press <strong>Ctrl+T</strong> to turn the range into a table: filters appear on every header, and formulas you add keep working as you filter.</p>
<p>Before filtering, hide the columns you do not need for prospecting. The groups that matter most are location, contacts and details; prices, polygons and neighbourhood columns can wait. The <a href="/data-dictionary/">data dictionary</a> lists every column by group.</p>

<h2 id="filters">Step 1: filter to your segment</h2>
<h3>Country and city</h3>
<p>Filter <?= col('country_name') ?> (or <?= col('country_code') ?>) to your sales territory, then <?= col('ld_city') ?> if you work city by city. In the sample, the largest countries are <?= e(implode(', ', array_map(fn($c) => $c[0] . ' (' . $c[1] . ')', array_slice(stat_val('countries.top') ?? [], 0, 4)))) ?>.</p>
<h3>Hotel class</h3>
<p>Filter <?= col('hotel_class') ?> to the star levels your product serves. Premium linen or amenity ranges usually target four and five stars; budget PMS software, two and three stars. In the sample, the split is:</p>
<div class="table-wrap">
<table class="num narrow">
  <caption>Hotel class of the <?= n($total) ?> sample hotels</caption>
  <thead><tr><th scope="col">Class</th><th scope="col">Hotels</th></tr></thead>
  <tbody>
<?php foreach (['5', '4', '3', '2', '1', 'none'] as $k): if (!isset($cls[$k])) { continue; } ?>
    <tr><th scope="row"><?= $k === 'none' ? 'Unclassified' : $k . '★' ?></th><td><?= n($cls[$k]) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<p>Do not drop unclassified hotels without a look: many independent and boutique properties simply do not list a star class.</p>
<h3>Rating and size</h3>
<p>Use a number filter on <?= col('rating') ?> ("greater than or equal to") to focus on well-reviewed properties, and on <?= col('review_count') ?> to favour busy ones. Review count is a rough proxy for size and guest volume. If floor area matters, <?= col('building_area_m2') ?> gives the footprint of the building.</p>
<h3>Chain or independent</h3>
<p><?= col('brand') ?> and <?= col('chain_from_website') ?> identify chain hotels; <?= col('parent_name') ?> shows a parent group where one is listed. Chains often buy centrally, so one contact can open many properties, while independents decide locally. Filter <code>brand</code> to blanks for independents, or to a list of brands for a chain campaign. In the sample, <code>brand</code> is filled for <?= n($fill['brand'] ?? null) ?> of <?= n($total) ?> hotels, so most sample hotels are independents or unbranded.</p>
<h3>Operating status</h3>
<p>Keep only operating hotels: filter <?= col('status') ?> and make sure <?= col('closed_month') ?> is empty. <?= col('last_confirmed_month') ?> shows when the hotel was last confirmed operating.</p>

<h2 id="contacts">Step 2: keep the hotels you can reach</h2>
<p>Filter out rows with no route in. In the sample, <?= col('phone') ?> is filled for <?= n($fill['phone'] ?? null) ?> and <?= col('website') ?> for <?= n($fill['website'] ?? null) ?> of the <?= n($total) ?> hotels. The full dataset adds publicly listed business email addresses, WhatsApp and social profiles (Facebook, Instagram, LinkedIn, X, TikTok, YouTube) where the property publishes them; see <a href="/data/hotel-contact-data/">contact details</a> for which fields the current sample includes.</p>
<p>Use <?= col('domain') ?> to remove duplicates: hotels that share a domain usually share a sales contact, so keep one row per domain for a first campaign and attach the others to it.</p>

<h2 id="columns">Step 3: choose the columns for your CRM</h2>
<p>A lean import has one column per CRM field. A practical set:</p>
<div class="table-wrap">
<table>
  <caption>Suggested columns for a CRM import</caption>
  <thead><tr><th scope="col">CRM field</th><th scope="col">Column</th></tr></thead>
  <tbody>
    <tr><th scope="row">External ID</th><td><?= col('ld_id') ?>: stable across monthly releases, so later updates match</td></tr>
    <tr><th scope="row">Account name</th><td><?= col('name') ?></td></tr>
    <tr><th scope="row">Address</th><td><?= col('street_address') ?>, <?= col('postal_code') ?>, <?= col('ld_city') ?>, <?= col('country_name') ?></td></tr>
    <tr><th scope="row">Phone / website</th><td><?= col('phone') ?>, <?= col('website') ?></td></tr>
    <tr><th scope="row">Segment</th><td><?= col('hotel_class') ?>, <?= col('rating') ?>, <?= col('review_count') ?></td></tr>
    <tr><th scope="row">Group</th><td><?= col('brand') ?>, <?= col('chain_from_website') ?></td></tr>
    <tr><th scope="row">Map pin</th><td><?= col('latitude') ?>, <?= col('longitude') ?></td></tr>
  </tbody>
</table>
</div>
<p>Copy the filtered table to a new sheet with <strong>Paste Special → Values</strong>, then save it as CSV UTF-8 for the import. Keeping <code>ld_id</code> as the external ID is the single most useful habit: next month's file updates the same records instead of creating duplicates.</p>

<h2 id="prioritise">Step 4: prioritise</h2>
<p>Sort the list so the best fits come first. A simple score in a new column works well, for example points for the right class, a rating above your threshold, a high review count and a chain you already serve. Monthly change files add timing signals: a hotel that recently appeared (<?= col('first_seen_month') ?>) or changed its amenities (<?= col('amenities_added') ?>) may be buying right now. See <a href="/guides/track-hotel-openings-and-closures/">Track hotel openings and closures</a>.</p>

<h2 id="compliance">Step 5: use it lawfully</h2>
<p>The contact fields are business details as published by the properties themselves. You are responsible for using them in line with GDPR/ePrivacy, CAN-SPAM and local anti-spam and data-protection rules: identify yourself, offer an opt-out and honour it. The <a href="/terms/#data-licence">data licence</a> allows lawful sales and marketing outreach for your own business, but not reselling or sharing the list.</p>

<h2 id="current">Step 6: keep the list current</h2>
<p>A prospect list goes stale quickly: hotels open, close, change owners and rebrand. If you take monthly updates, each release arrives with a change file. Join it to your CRM on <code>ld_id</code> and act on three kinds of change. New hotels in your segment become new accounts. Hotels with a <code>closed_month</code> should be marked inactive so nobody calls them. Changes to <code>brand</code>, <code>name_history</code> or <code>website</code> often mean a new owner or operator, which is a good reason to get in touch again. For a one-off purchase, plan a refresh before a major campaign rather than reusing a file that is a year old.</p>

<h2 id="next">Where to go next</h2>
<p>See <a href="/use-cases/#sales-prospecting">sales prospecting for hotel suppliers</a>, or <a href="/contact/">tell us your territory and segment</a> and we will send counts of matching hotels and how many have each contact field.</p>
</article>
<?= partial('cta') ?>
