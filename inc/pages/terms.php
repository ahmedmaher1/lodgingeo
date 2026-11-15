<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">These terms cover the use of lodgingeo.com and the licence under which purchased Lodgingeo data may be used.</p>

<h2 id="website">Website terms</h2>
<ul>
  <li>The website and its content are provided by <?= e(LEGAL_ENTITY) ?>. You may read, print and link to its pages.</li>
  <li>The free sample is provided so that you can evaluate the data. It is covered by the data licence below, as if it were purchased data, and may not be republished.</li>
  <li>Figures on the website describe the sample and the database at the latest release (<?= e(LATEST_RELEASE) ?>) and may change with later releases.</li>
  <li><?= e(TRADEMARK_LINE) ?> Other names are trademarks of their respective owners.</li>
  <li>How we handle form submissions is described in the <a href="/privacy/">privacy policy</a>.</li>
</ul>

<h2 id="data-licence">Data licence</h2>
<ol class="licence">
  <li><strong>A licence, not a sale.</strong> Lodgingeo, LLC grants the buyer a non-exclusive, non-transferable licence to use the purchased data for its own internal business purposes. Ownership of the data stays with Lodgingeo, LLC.</li>
  <li><strong>Who may use it:</strong> the buyer's employees, and contractors working on the buyer's behalf. Use by affiliates needs our written approval.</li>
  <li><strong>Allowed:</strong> analysis; import into CRM and internal systems; sales and marketing outreach that complies with the law; building internal tools and models; and publishing aggregated statistics, charts or insights that do not reproduce individual records.</li>
  <li><strong>Not allowed:</strong> reselling, sublicensing, sharing or publishing the data or any substantial part of it, raw or modified; offering it as a list, dataset or lookup service; or using it to build a competing data product.</li>
  <li><strong>Term:</strong> data from a one-off purchase may be used indefinitely. Data from a monthly subscription may be used while the subscription is active. When a subscription ends, stop using its data and delete it within 30 days; aggregated results already produced may be kept.</li>
  <li><strong>Third-party sources:</strong> building polygons derived from OpenStreetMap and Overture Maps keep their original open licences (ODbL and others) and attribution requirements, including "© OpenStreetMap contributors".</li>
  <li><strong>Compliance:</strong> the buyer is responsible for using contact details lawfully, including GDPR/ePrivacy, CAN-SPAM and local anti-spam and data-protection laws.</li>
  <li><strong>No warranty:</strong> the data is provided "as is". Rates are indicative. Lodgingeo does not guarantee completeness or accuracy, and its liability is limited to the amount paid for the data.</li>
  <li><strong>Order terms win:</strong> the quote or order confirmation sets out the specific data, term and any extra rights. Where it differs from this text, the order confirmation applies.</li>
</ol>
<p>Attribution details for the open sources are on the <a href="/methodology/#attribution">methodology page</a>. Questions about the licence: <a href="/contact/">contact us</a>.</p>
<?= partial('cta') ?>
