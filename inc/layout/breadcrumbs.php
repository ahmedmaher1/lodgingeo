<?php
/** @var array $page */
$path = $page['path'];
$trail = isset(routes()[$path]) && empty($page['noindex'])
    ? breadcrumb_trail($path)
    : breadcrumb_trail($page['parent'] ?? '/') + ['' => $page['crumb'] ?? $page['h1']];
$last = array_key_last($trail);
?>
<nav class="breadcrumbs" aria-label="Breadcrumb">
  <ol>
<?php foreach ($trail as $href => $label): ?>
<?php if ($href === $last): ?>
    <li><span aria-current="page"><?= e($label) ?></span></li>
<?php else: ?>
    <li><a href="<?= e($href) ?>"><?= e($label) ?></a></li>
<?php endif; ?>
<?php endforeach; ?>
  </ol>
</nav>
