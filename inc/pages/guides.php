<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Practical walkthroughs for working with the hotel data, using the free sample's real columns and figures.</p>
<?php foreach (routes() as $p => $r): if (empty($r['guide'])) { continue; } ?>
<h2><a href="<?= e($p) ?>"><?= e($r['h1']) ?></a></h2>
<p><?= e($r['desc']) ?></p>
<?php endforeach; ?>
<p>Every column mentioned in the guides is in the <a href="/data-dictionary/">data dictionary</a>. To follow along, <a href="/hotel-database-sample/">download the sample</a>.</p>
<?= partial('cta') ?>
