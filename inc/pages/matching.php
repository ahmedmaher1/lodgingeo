<?php
/** @var array $page */
$tiers = stat_val('match_tiers') ?? [];
$total = (int) stat_val('sheets.Hotels.rows');
$tierText = [
    'high' => 'Name and city agree strongly. Accept automatically.',
    'medium' => 'Likely the same hotel. Review before use.',
    'low' => 'A weak candidate. Review carefully.',
    'no_match' => 'No suitable candidate was found.',
];
?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Send us your hotel list with your own ID, hotel name, city and country. We match each row to a place in our database and return it with our ID, coordinates and every fact column, plus scores that show how good each match is. The free sample was produced exactly this way.</p>

<h2 id="what-you-send">What you send, what you get back</h2>
<div class="table-wrap">
<table>
  <caption>Input and output of a matching job</caption>
  <thead><tr><th scope="col">You send</th><th scope="col">We return</th></tr></thead>
  <tbody>
    <tr><td>Your ID, hotel name, city, country (CSV or Excel)</td><td>Your columns unchanged, then our <?= col('ld_id') ?>, <?= col('latitude') ?>, <?= col('longitude') ?> and every fact column described on the <a href="/data/">data pages</a></td></tr>
    <tr><td>Optional: address or website</td><td>Match scores and a match tier per row, as below</td></tr>
  </tbody>
</table>
</div>

<h2 id="match-columns">Match columns</h2>
<?= fields_table([
    'name_score' => 'Similarity between your hotel name and ours, 0–100.',
    'city_score' => 'Similarity between your city and ours, 0–100.',
    'confidence' => 'Combined score: 70% name score and 30% city score, plus a small bonus for places typed as hotels.',
    'match_tier' => 'high, medium, low or no_match, derived from the confidence.',
    'flags' => 'Notes on the match, for example when a second candidate scores close to the first.',
], 'Columns added to each matched row') ?>
<p>Each row also carries the second-best and closest candidates (name and confidence), so a reviewer can see the alternative without searching. Those candidate columns appear in your delivered file only.</p>

<h2 id="sample-figures">Match tiers in the sample</h2>
<div class="table-wrap">
<table class="num">
  <caption>Match tier of the <?= n($total) ?> rows in the sample</caption>
  <thead><tr><th scope="col">Tier</th><th scope="col">What to do</th><th scope="col">Rows</th><th scope="col">Share</th></tr></thead>
  <tbody>
<?php foreach ($tiers as $t => $cnt): ?>
    <tr><th scope="row"><code><?= e($t) ?></code></th><td class="text"><?= e($tierText[$t] ?? '') ?></td><td><?= n($cnt) ?></td><td><?= pct($cnt, $total) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<h2 id="workflow">Recommended workflow</h2>
<ol>
  <li>Send the list in CSV or Excel with one row per hotel.</li>
  <li>Auto-accept rows with <code>match_tier = high</code>.</li>
  <li>Review <code>medium</code> and <code>low</code> rows: compare the name, city and second candidate; fix or reject.</li>
  <li>For <code>no_match</code> rows, check the spelling or send an address; some may be places we do not list.</li>
  <li>Keep our <code>ld_id</code> next to your ID. It stays stable across monthly releases, so later updates join on it.</li>
</ol>
<p>Matching is a common first step for <a href="/use-cases/#content-mapping">content mapping</a> at OTAs and bedbanks.</p>

<section class="block" aria-labelledby="send-list">
  <h2 id="send-list">Send us your list</h2>
  <p>Email your file to <a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a> or describe it on the <a href="/contact/">contact form</a>. We return the match results with counts and a quote. <?= e(REPLY_PROMISE) ?></p>
</section>

<?= partial('data-closing', ['source' => '/hotel-matching/']) ?>
