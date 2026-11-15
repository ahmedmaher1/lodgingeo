<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Lodgingeo is the hotel data service of <a href="<?= e(PARENT_ORG_URL) ?>">Location Drive</a>, which has compiled global place data since 2010.</p>
<p>Every month we collect, match and check data on <?= e(PLACES_TO_STAY_TOTAL) ?> places to stay, including <?= e(HOTELS_TOTAL) ?> hotels, in <?= e(COUNTRIES_TOTAL) ?>, and deliver it as CSV, Excel or Google Sheets.</p>
<p>Lodgingeo is run by Ahmed Maher. Every email and WhatsApp message is answered by the person who builds the data, within 24 hours.</p>
<h2 id="who-answers">Who answers your email</h2>
<p>When you write to <a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a>, use the <a href="/contact/">contact form</a> or send a message on <?= whatsapp_link() ?>, the reply comes from the same person who builds the monthly releases. Questions about a column, a country or a match result get a direct answer, not a ticket number.</p>
<h2 id="learn-more">Learn more</h2>
<ul>
  <li><a href="/methodology/">How the data is built</a></li>
  <li><a href="/releases/">Monthly release log</a></li>
  <li><a href="/hotel-database-sample/">Free sample</a></li>
</ul>
<?= partial('cta') ?>
