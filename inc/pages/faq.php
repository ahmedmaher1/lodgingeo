<?php /** @var array $page */ ?>
<h1><?= e($page['h1']) ?></h1>
<p class="lead">Short answers about the hotel data. If your question is not here, <a href="/contact/">ask us</a>; <?= e(lcfirst(REPLY_PROMISE)) ?></p>
<dl class="faq">
<?php foreach (faq_items(false) as $i => $f): ?>
  <dt id="q<?= $i + 1 ?>"><?= e($f['q']) ?></dt>
  <dd><?= $f['a'] ?></dd>
<?php endforeach; ?>
</dl>
<p>More detail: <a href="/data/">data overview</a>, <a href="/data-dictionary/">data dictionary</a>, <a href="/methodology/">methodology</a>.</p>
<?= partial('cta') ?>
