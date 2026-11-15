<?php
/** @var array $page */
$all = stat_val('countries.all');
$top = stat_val('countries.top') ?? [];
$cats = stat_val('categories');
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">The database covers <?= e(PLACES_TO_STAY_TOTAL) ?> places to stay, including <?= e(HOTELS_TOTAL) ?> hotels, in <?= e(COUNTRIES_TOTAL) ?>. It has been updated monthly since January 2025 and is sold as one global dataset: worldwide, or for the countries you choose.</p>
<?= partial('fact-box') ?>

<?php if ($cats): ?>
<h2 id="kinds">Kinds of places to stay</h2>
<p>Categories present in the sample's <code>category</code> and <code>categories</code> columns:</p>
<p><?= e(implode(', ', array_map(fn($c) => $c[0], array_slice($cats, 0, 40)))) ?>.</p>
<?php endif; ?>

<h2 id="sample-countries">Countries in the sample</h2>
<?php if ($all): ?>
<p>The <?= n(count($all)) ?> countries with hotels in the free sample:</p>
<p><?= e(implode(', ', $all)) ?>.</p>
<?php else: ?>
<p>The free sample has hotels in <?= n(stat_val('countries.count')) ?> countries and <?= n(stat_val('cities.count')) ?> cities. The ten with the most sample hotels:</p>
<p><?= e(implode(', ', array_map(fn($c) => $c[0] . ' (' . $c[1] . ')', $top))) ?>.</p>
<?php endif; ?>
<p>The sample is a cross-section for judging the columns. The full data covers every country and territory in the list above and the rest of the world.</p>

<h2 id="counts">Counts for your countries</h2>
<p>Need counts for your countries or cities? <a href="/contact/">Ask us</a>. We send the number of places to stay and hotels, and how many have each field filled, for exactly the area you need. See also <a href="/formats-and-delivery/">formats and delivery</a> and the <a href="/data/">data overview</a>.</p>

<?= partial('cta') ?>
