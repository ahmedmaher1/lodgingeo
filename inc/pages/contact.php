<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Tell us the countries, fields and format you need and we will send counts and a quote by email. <?= e(REPLY_PROMISE) ?></p>
<dl class="facts facts-small">
  <div><dt>Email</dt><dd><a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a></dd></div>
  <div><dt>Chat</dt><dd><?= whatsapp_link() ?></dd></div>
  <div><dt>Reply</dt><dd>Within 24 hours</dd></div>
</dl>

<h2 id="what-to-include">What to include</h2>
<ul>
  <li>The countries (or cities) you need, or worldwide (<a href="/coverage/">coverage</a>).</li>
  <li>The fields you need (<a href="/data/">data overview</a>, <a href="/data-dictionary/">data dictionary</a>).</li>
  <li>The format: CSV, Excel or Google Sheets (<a href="/formats-and-delivery/">formats and delivery</a>).</li>
  <li>Whether it's one-off or monthly.</li>
</ul>

<h2 id="form">Send a message</h2>
<?= partial('contact-form', ['variant' => 'full', 'source' => '/contact/']) ?>

<p>Want to see the data first? <a href="/hotel-database-sample/">Download the free sample</a>.</p>
<?= partial('cta') ?>
