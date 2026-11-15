<?php
/** @var array $page */
$nav = [
    '/data/' => 'Data',
    '/hotel-database-sample/' => 'Sample',
    '/coverage/' => 'Coverage',
    '/data-dictionary/' => 'Dictionary',
    '/guides/' => 'Guides',
    '/contact/' => 'Contact',
];
$current = $page['path'];
?><!doctype html>
<html lang="en">
<head>
<?= render_head($page['path'], $page) ?>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="/"><span class="wordmark">Lodgingeo</span> <span class="tagline"><?= e(TAGLINE) ?></span></a>
    <nav aria-label="Main">
      <ul class="nav">
<?php foreach ($nav as $href => $label):
    $active = $current === $href || ($href !== '/' && str_starts_with($current, $href)) || ($href === '/data/' && $current === '/hotel-matching/');
?>
        <li><a href="<?= e($href) ?>"<?= $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>
<main id="main" class="wrap">
<?php if ($current !== '/') { echo partial('breadcrumbs', ['page' => $page]); } ?>
