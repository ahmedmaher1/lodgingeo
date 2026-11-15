<?php $pub = new DateTime(GUIDES_PUBLISHED); $upd = new DateTime(GUIDES_UPDATED); ?>
<p class="byline">By <?= e(SIGNATURE_NAME) ?> · Published <time datetime="<?= e(GUIDES_PUBLISHED) ?>"><?= e($pub->format('j F Y')) ?></time> · Updated <time datetime="<?= e(GUIDES_UPDATED) ?>"><?= e($upd->format('j F Y')) ?></time></p>
