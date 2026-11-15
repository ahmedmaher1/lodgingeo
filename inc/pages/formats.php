<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">The hotel data is delivered as files: CSV, Excel or a shared Google Sheet, one file per country or one worldwide file, updated every month.</p>

<h2 id="formats">Formats</h2>
<div class="table-wrap">
<table>
  <caption>Delivery formats</caption>
  <thead><tr><th scope="col">Format</th><th scope="col">Best for</th><th scope="col">Notes</th></tr></thead>
  <tbody>
    <tr><th scope="row">CSV</th><td>Databases, GIS, data pipelines</td><td>UTF-8, one row per place. Large extracts always ship as CSV.</td></tr>
    <tr><th scope="row">Excel (.xlsx)</th><td>Sales and research teams</td><td>The same columns as the <a href="/hotel-database-sample/">free sample</a>, with a column guide sheet.</td></tr>
    <tr><th scope="row">Google Sheets</th><td>Shared, browser-based work</td><td>A sheet shared with the email addresses you give us.</td></tr>
  </tbody>
</table>
</div>
<p>Building polygons come as a WKT text column (<?= col('building_polygon_wkt') ?>) in every format. Every column is described in the <a href="/data-dictionary/">data dictionary</a>.</p>

<h2 id="delivery">Delivery</h2>
<ul>
  <li>A download link for the CSV or Excel files, or</li>
  <li>a Google Sheet shared with the buyer.</li>
</ul>
<p>You choose the scope: one country, several countries or worldwide, and all fields or only the groups you need.</p>

<h2 id="updates">Monthly updates</h2>
<p>The data is released every month (the latest release is <?= e(LATEST_RELEASE) ?>). A monthly subscription receives each new release together with a change file listing what changed since the previous month: new listings, closures, reopenings and changed fields. Places keep the same <?= col('ld_id') ?>, so updates join on it. See the <a href="/releases/">release log</a> and <a href="/data/hotel-history-and-changes/">history and changes</a>.</p>

<h2 id="quote">What to send us for a quote</h2>
<ol>
  <li>The countries or cities you need, or "worldwide".</li>
  <li>The fields or field groups you need (see the <a href="/data/">data overview</a>).</li>
  <li>The format: CSV, Excel or Google Sheets.</li>
  <li>One-off or monthly.</li>
  <li>If you have your own hotel list to match, its size and columns (<a href="/hotel-matching/">list matching</a>).</li>
</ol>
<p>Quotes go by email. <a href="/contact/">Contact us</a> or write to <a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a>. <?= e(REPLY_PROMISE) ?></p>

<?= partial('cta') ?>
