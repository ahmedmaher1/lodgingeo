<?php
/**
 * Inline SVG of a real building polygon from the sample.
 * @var array $poly  entry from polygon_preview.json
 * @var bool  $named show the hotel name in the caption
 */
$named = $named ?? false;
$area = n((float) $poly['area_m2'], 0);
$src = str_starts_with((string) $poly['source'], 'osm') ? 'OpenStreetMap' : 'Overture';
$label = 'Building footprint matched to a hotel in the sample, ' . $area . ' square metres, grade ' . $poly['grade'];
?>
<figure class="poly">
  <svg viewBox="0 0 300 200" width="300" height="200" role="img" aria-label="<?= e($label) ?>"><path d="<?= e($poly['svg_path']) ?>"/></svg>
  <figcaption><?= $named ? e($poly['name']) . ' · ' : 'Building footprint matched to a hotel in the sample · ' ?><?= $area ?> m² · grade <?= e($poly['grade']) ?> · source: <?= e($src) ?> (<code><?= e($poly['source']) ?></code>)</figcaption>
</figure>
