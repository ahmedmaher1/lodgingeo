<?php
/** @var array $page */
$lead = $page['lead'];
$token = $lead['token'];
?>
<h1><?= e($page['h1']) ?>, <?= e($lead['name']) ?></h1>
<?php if ($page['ready']): ?>
<p class="lead">Your sample is ready. The link works for 7 days and up to 5 downloads.</p>
<p><a class="btn btn-primary" href="/download/<?= e($token) ?>/">Download the sample (.xlsx)</a></p>
<p>We've also emailed you the link.</p>
<?php else: ?>
<p class="lead">We're preparing the latest sample and will email it to you shortly.</p>
<?php endif; ?>
<p>Want counts or a quote for specific countries and fields? Reply to the email, or <a href="/contact/">send us a message</a>. <?= e(REPLY_PROMISE) ?></p>
<h2>While you wait</h2>
<ul>
  <li><a href="/data-dictionary/">Data dictionary</a>: what every column means.</li>
  <li><a href="/guides/">Guides</a>: loading polygons, reading prices, building prospect lists.</li>
  <li><a href="/formats-and-delivery/">Formats and delivery</a>.</li>
</ul>
