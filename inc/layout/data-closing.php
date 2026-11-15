<?php /** Compact sample form + closing CTA at the end of data pages. @var string $source */ ?>
<section class="block" id="sample" aria-labelledby="sample-h">
  <h2 id="sample-h">See it in the free sample</h2>
  <p>The Excel sample has <?= n(stat_val('sheets.Hotels.rows')) ?> real hotel rows with these columns. Enter your name and work email to get the link.</p>
  <?= partial('sample-form', ['variant' => 'compact', 'source' => $source]) ?>
</section>
<?= partial('cta') ?>
