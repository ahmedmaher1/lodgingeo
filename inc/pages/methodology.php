<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">How the hotel data is collected, matched and checked each month, and which open data it builds on.</p>

<h2 id="sources">Sources</h2>
<ul>
  <li><strong>Places to stay and their facts:</strong> publicly available web listings and booking sites.</li>
  <li><strong>Prices:</strong> nightly offers from a fixed list of known booking sites and from the hotels' own websites, captured monthly. <?= e(RATES_CAVEAT) ?></li>
  <li><strong>Contact details:</strong> as published by the properties, including publicly listed business email addresses found on the hotel's own website and official pages.</li>
  <li><strong>Building footprints:</strong> OpenStreetMap and Overture Maps, matched to each hotel's point.</li>
</ul>

<h2 id="monthly-cycle">The monthly cycle</h2>
<ol>
  <li>Collect the current listings and offers.</li>
  <li>Match each listing to the existing place, keeping its stable <?= col('ld_id') ?>.</li>
  <li>Match each hotel to a building footprint and grade the match.</li>
  <li>Compare the release with the previous one to produce the change history (<a href="/data/hotel-history-and-changes/">history and changes</a>).</li>
</ol>

<h2 id="quality">Quality rules</h2>
<ul>
  <li><strong>Match tiers:</strong> list matching scores name (70%) and city (30%) similarity, with a small bonus for places typed as hotels, and assigns high, medium, low or no_match (<a href="/hotel-matching/">list matching</a>).</li>
  <li><strong>Polygon grades:</strong> each footprint match is graded A (highest confidence) to E (lowest), and its source is recorded as <code>overture_contains</code>, <code>osm_name</code> or <code>overture_nearest</code>. Grades A–B suit automated use; C–E deserve a review (<a href="/data/hotel-building-polygons/">building polygons</a>).</li>
  <li><strong>Gaps are gaps:</strong> a place missing from one release is treated as a data gap, not a closure. A closure is recorded only when the place is marked closed.</li>
  <li><strong>Published facts only:</strong> contact fields are left empty when the property does not publish them.</li>
</ul>

<h2 id="attribution">Attribution</h2>
<p>Building footprints are derived from open data and keep their original licences and attribution requirements:</p>
<ul>
  <li>© OpenStreetMap contributors, available under the Open Database License (ODbL). <a href="https://www.openstreetmap.org/copyright">openstreetmap.org/copyright</a></li>
  <li>Contains data from the Overture Maps Foundation, used under the licences listed in its <a href="https://docs.overturemaps.org/attribution/">attribution guidance</a>.</li>
</ul>
<p>The <a href="/terms/#data-licence">data licence</a> explains how these open licences apply to delivered data.</p>

<?= partial('cta') ?>
