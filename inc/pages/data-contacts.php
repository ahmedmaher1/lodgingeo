<?php
/** @var array $page */
$total = (int) stat_val('fill.total');
$fill = stat_val('fill.fields') ?? [];
$contactFill = [];
foreach (['phone' => 'Phone', 'website' => 'Website', 'email' => 'Email', 'whatsapp' => 'WhatsApp', 'facebook_url' => 'Facebook',
          'instagram_url' => 'Instagram', 'linkedin_url' => 'LinkedIn', 'x_url' => 'X', 'tiktok_url' => 'TikTok', 'youtube_url' => 'YouTube', 'brand' => 'Brand'] as $c => $label) {
    if (isset($fill[$c]) && in_sample($c)) {
        $contactFill[$c] = $label;
    }
}
$rows = data_json('sample_preview')['contact_rows'] ?? [];
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Business contact details for every hotel, as the property publishes them: phone in international format, website and domain, email, WhatsApp and social profiles where they are publicly listed, plus the chain, brand and languages spoken.</p>

<h2 id="fields">Fields</h2>
<?= fields_table([
    'phone' => 'Phone number in international format, e.g. +43 2732 71123.',
    'website' => 'Website URL as published by the property.',
    'domain' => 'Website domain without protocol or path.',
    'email' => 'Publicly listed business email addresses found on the hotel\'s own website and official pages.',
    'whatsapp' => 'WhatsApp number or link, where the property publishes one.',
    'facebook_url' => 'Facebook profile link, where published.',
    'instagram_url' => 'Instagram profile link, where published.',
    'linkedin_url' => 'LinkedIn page link, where published.',
    'x_url' => 'X (Twitter) profile link, where published.',
    'tiktok_url' => 'TikTok profile link, where published.',
    'youtube_url' => 'YouTube channel link, where published.',
    'parent_name' => null,
    'chain_from_website' => null,
    'brand' => null,
    'languages' => null,
], 'Contact and identity columns') ?>
<p>Fields marked <span class="tag tag-muted">full dataset</span> are delivered in the data you order but are not in the current sample's header row. The <a href="/data-dictionary/#full-dataset-fields">full dataset field list</a> shows them all.</p>

<?php if ($contactFill): ?>
<h2 id="fill-rates">Fill rates in the sample</h2>
<div class="table-wrap">
<table class="num narrow">
  <caption>Contact fields filled, of <?= n($total) ?> sample hotels</caption>
  <thead><tr><th scope="col">Field</th><th scope="col">Hotels</th><th scope="col">Share</th></tr></thead>
  <tbody>
<?php foreach ($contactFill as $c => $label): ?>
    <tr><th scope="row"><?= e($label) ?> (<?= col($c, false) ?>)</th><td><?= n($fill[$c]) ?></td><td><?= pct($fill[$c], $total) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>

<?php if ($rows): ?>
<h2 id="example">Example rows from the sample</h2>
<div class="table-wrap">
<table>
  <caption>Three hotels from the <?= e(SAMPLE_RELEASE) ?> sample</caption>
  <thead><tr><th scope="col">Hotel</th><th scope="col">City</th><th scope="col">Country</th><th scope="col">Phone</th><th scope="col">Website domain</th></tr></thead>
  <tbody>
<?php foreach ($rows as $r): ?>
    <tr><th scope="row"><?= e($r['hotel']) ?></th><td><?= e($r['city']) ?></td><td><?= e($r['country']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['domain']) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>

<h2 id="uses">Uses</h2>
<p>The contact fields are built for business-to-business prospecting by companies that sell to hotels:</p>
<ul>
  <li>linen, amenities and housekeeping suppliers;</li>
  <li>food and beverage suppliers;</li>
  <li>property management system (PMS) and channel-manager software;</li>
  <li>revenue management tools and consultancies;</li>
  <li>bedbanks building their hotel supply.</li>
</ul>
<p>Segment the list by country, <?= col('hotel_class') ?>, <?= col('rating') ?> and chain (<?= col('brand') ?>, <?= col('chain_from_website') ?>). The guide <a href="/guides/build-a-hotel-prospect-list/">How to build a hotel prospect list in Excel</a> walks through the filters, and <a href="/use-cases/#sales-prospecting">sales prospecting</a> describes the use case.</p>

<h2 id="compliance">Compliance</h2>
<p>These are business contact details as published by the properties. Buyers are responsible for complying with GDPR/ePrivacy, CAN-SPAM and local rules when they use them. We remove records on request: <a href="/contact/">contact us</a> with the property name and address. The <a href="/terms/#data-licence">data licence</a> sets out what is allowed.</p>

<?= partial('data-closing', ['source' => '/data/hotel-contact-data/']) ?>
