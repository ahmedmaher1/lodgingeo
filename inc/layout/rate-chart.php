<?php
/**
 * Hand-made inline SVG bar chart of one sample hotel's rates by site.
 * @var array $ex rates_example.json
 */
$rates = array_slice($ex['rates'], 0, 14);
$max = max(array_map(fn($r) => (float) $r[1], $rates)) ?: 1;
$rowH = 22;
$labelW = 150;
$barW = 380;
$h = count($rates) * $rowH + 10;
?>
<figure class="chart">
  <svg viewBox="0 0 620 <?= $h ?>" width="620" height="<?= $h ?>" role="img" aria-label="Nightly offers by booking site for <?= e($ex['hotel']) ?>">
<?php foreach ($rates as $i => [$site, $price]):
    $y = 5 + $i * $rowH;
    $w = max(1, round($price / $max * $barW, 1));
?>
    <text x="<?= $labelW - 8 ?>" y="<?= $y + 15 ?>" text-anchor="end"><?= e($site) ?></text>
    <rect x="<?= $labelW ?>" y="<?= $y + 3 ?>" width="<?= $w ?>" height="<?= $rowH - 8 ?>"/>
    <text x="<?= $labelW + $w + 6 ?>" y="<?= $y + 15 ?>"><?= e(n($price)) ?></text>
<?php endforeach; ?>
  </svg>
  <figcaption>Nightly offers by booking site for one sample hotel (<?= e($ex['hotel']) ?>, <?= e($ex['city']) ?>), release <?= e(SAMPLE_RELEASE) ?>. <?= e(RATES_CAVEAT) ?></figcaption>
</figure>
