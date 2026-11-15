<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">This policy covers the lodgingeo.com website and its two forms. It does not cover the hotel data itself, which is described in the <a href="/terms/#data-licence">data licence</a>.</p>

<h2 id="what-we-collect">What the forms collect</h2>
<div class="table-wrap">
<table>
  <caption>Data collected by the website</caption>
  <thead><tr><th scope="col">Form</th><th scope="col">Fields</th><th scope="col">Why</th></tr></thead>
  <tbody>
    <tr><th scope="row">Sample download</th><td>Name, email, company, countries of interest, release-email preference</td><td>To send you the sample link, answer follow-up questions and, if you ticked the box, tell you about new monthly releases.</td></tr>
    <tr><th scope="row">Contact</th><td>Name, email, company, countries, data needed, format, frequency, message</td><td>To answer your message and prepare a quote.</td></tr>
    <tr><th scope="row">Both</th><td>The page you sent the form from, the page you arrived on, the referring site, campaign (UTM) tags, browser user agent, and a one-way hash of your IP address</td><td>To understand which pages lead to enquiries and to limit spam and abuse. Your IP address itself is not stored.</td></tr>
  </tbody>
</table>
</div>
<p>When you download the sample we record the number and time of downloads made with your link.</p>

<h2 id="cookies">Cookies and analytics</h2>
<p>The site does not use analytics or advertising cookies by default. A session cookie may be set when you arrive from a campaign link or another website, so that the source can be attached to a form you send; it expires when you close your browser. If analytics are ever enabled, you will be asked first, and nothing loads unless you accept.</p>

<h2 id="retention">Retention</h2>
<p>Form submissions are kept for 24 months and then deleted. Rate-limiting records (hashed IP and email) are deleted after two days.</p>

<h2 id="sharing">Sharing</h2>
<p>We do not sell or share your details. They are stored with our hosting provider and sent to us by email so that we can reply.</p>

<h2 id="your-rights">Your rights and deletion</h2>
<p>You can ask to see, correct or delete your details at any time: email <a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a> or use the <a href="/contact/">contact form</a>. We act on requests without undue delay. To have a hotel record removed from the data, send us the property name and address.</p>

<h2 id="controller">Who is responsible</h2>
<p><?= e(LEGAL_ENTITY) ?>, contact <a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a>. See also the <a href="/terms/">terms of use</a>.</p>
<?= partial('cta') ?>
