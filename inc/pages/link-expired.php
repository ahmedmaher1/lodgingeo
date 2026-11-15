<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Download links work for 7 days and up to 5 downloads. Enter your details again and we will send you a new link.</p>
<?= partial('sample-form', ['variant' => 'full', 'source' => '/hotel-database-sample/']) ?>
<p>Problems with the download? Email <a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a>.</p>
