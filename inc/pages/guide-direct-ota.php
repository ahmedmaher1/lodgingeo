<?php
/** @var array $page */
$dv = stat_val('direct_vs_ota') ?? [];
$bs = stat_val('booking_sites') ?? [];
$total = (int) stat_val('sheets.Hotels.rows');
$gap = $dv['median_gap_pct'] ?? null;
?>
<article>
<h1><?= e($page['h1']) ?></h1>
<?= partial('byline') ?>
<p class="lead">When a hotel's own website shows a price, the data compares it with the lowest offer from an online travel agency for the same hotel. This guide explains what that comparison column means, what the free sample shows, and the caveats that keep the numbers honest.</p>
<p class="note"><?= e(RATES_CAVEAT) ?></p>

<h2 id="columns">The columns involved</h2>
<p>Every month, offers are captured for each hotel from a fixed list of known booking sites. Four columns carry the comparison:</p>
<ul>
  <li><?= col('hotel_rates_by_site') ?>: the nightly offer on each booking site that listed the hotel;</li>
  <li><?= col('lowest_offer_latest') ?>: the lowest of those offers in the latest release;</li>
  <li><?= col('direct_offer_latest') ?>: the offer on the hotel's own (official) website, where one was shown;</li>
  <li><?= col('direct_vs_lowest_ota_pct') ?>: the difference between the direct offer and the lowest OTA offer, in percent.</li>
</ul>
<p>As the column name says, the comparison is with online travel agencies, the sites listed in <?= col('booking_otas_latest') ?>. The exact definition of each column is in the Column guide sheet of the sample and in the <a href="/data-dictionary/#direct_vs_lowest_ota_pct">data dictionary</a>.</p>

<h2 id="reading">How to read the value</h2>
<p><?= col('direct_vs_lowest_ota_pct') ?> is signed:</p>
<ul>
  <li><strong>Positive</strong>: the hotel's own website was more expensive than the cheapest OTA. A value of +5 means the direct price was about 5% higher.</li>
  <li><strong>Zero</strong>: the direct price matched the lowest OTA offer: rate parity.</li>
  <li><strong>Negative</strong>: the direct price was lower than every OTA offer, which is what many hotels aim for when they push direct bookings.</li>
  <li><strong>Empty</strong>: no direct offer was shown, so there is nothing to compare. Empty does not mean the hotel has no website; it means no bookable price was captured from it.</li>
</ul>
<p>Read one value as a snapshot of one capture, not as the hotel's pricing policy. Patterns become meaningful when you look across many hotels, or across months for one hotel.</p>

<h2 id="sample">What the sample shows</h2>
<p>Of the <?= n($total) ?> hotels in the free sample, <?= n(stat_val('fill.fields.hotel_rates_by_site')) ?> have rates by site. They were offered on <?= n($bs['distinct'] ?? null) ?> distinct booking sites in all, with a median of <?= n($bs['median_per_hotel'] ?? null) ?> sites per hotel and a maximum of <?= n($bs['max_per_hotel'] ?? null) ?>.</p>
<p><?= n($dv['hotels_with_direct'] ?? null) ?> hotels had an official-site (direct) offer. Of those, <?= n($dv['above_lowest_ota_pct'] ?? null) ?>% had a direct price above the lowest OTA offer, and the median gap was <?= $gap !== null && $gap >= 0 ? '+' : '' ?><?= n($gap) ?>%. In other words, in this sample the cheapest place to book was more often an OTA than the hotel itself, and the typical difference was small.</p>
<p>This is a sample of <?= n($total) ?> hotels chosen to show the columns, not a market study. Treat it as an illustration of what the full data lets you measure for your own countries, cities and segments.</p>

<h2 id="why">Why a direct price can be higher</h2>
<p>A positive gap is not automatically a parity breach. Common reasons include:</p>
<ul>
  <li><strong>Different products.</strong> The cheapest OTA offer may be a non-refundable rate or a smaller room, while the hotel's site shows a flexible rate first.</li>
  <li><strong>Member or package prices.</strong> Some OTA offers are discounted for logged-in members or bundled with other travel.</li>
  <li><strong>Taxes and fees.</strong> Sites differ in whether the headline price includes taxes and fees.</li>
  <li><strong>Timing.</strong> Offers were captured for given search dates at a given moment; prices move during the day.</li>
</ul>
<p>When the gap matters to you, look at the offers per site in <?= col('hotel_rates_by_site') ?> rather than at the summary column alone.</p>

<h2 id="excel">Working with it in Excel</h2>
<ol>
  <li>Open the Hotels sheet and turn the data into a table (Ctrl+T) so filters and formulas follow the columns.</li>
  <li>Filter <code>direct_vs_lowest_ota_pct</code> to exclude blanks. The remaining rows are the hotels with a comparison.</li>
  <li>Share of hotels priced above the lowest OTA: <code>=COUNTIF(Table1[direct_vs_lowest_ota_pct],"&gt;0")/COUNT(Table1[direct_vs_lowest_ota_pct])</code>.</li>
  <li>Median gap: <code>=MEDIAN(Table1[direct_vs_lowest_ota_pct])</code>. Use the median rather than the average, because a few large gaps can pull the average a long way.</li>
  <li>Break it down with a pivot table: rows <code>country_name</code> or <code>hotel_class</code>, values the count and median of the gap.</li>
</ol>
<p>Adding <?= col('booking_sites_latest') ?> to the pivot shows whether hotels sold on many sites behave differently from those on a few. <?= col('rate_history_by_month') ?> lets you check whether a hotel's gap is a one-month event or a habit.</p>

<h2 id="caveats">Caveats</h2>
<ul>
  <li><?= e(RATES_CAVEAT) ?></li>
  <li>Offers come from a fixed list of known booking sites. An offer on a site outside that list is not seen.</li>
  <li>Booking links are not exported; the data records the site and its offer.</li>
  <li>The direct offer is only present when the hotel's own site showed a bookable price at capture time.</li>
  <li><?= e(TRADEMARK_LINE) ?></li>
</ul>

<h2 id="next">Where to go next</h2>
<p>The <a href="/data/hotel-prices-by-booking-site/">prices by booking site page</a> lists every price column and the booking sites seen in the sample. For revenue-management uses, see <a href="/use-cases/#revenue-management">revenue management and rate intelligence</a>.</p>
</article>
<?= partial('cta') ?>
