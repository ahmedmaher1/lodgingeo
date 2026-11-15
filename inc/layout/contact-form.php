<?php
/**
 * Contact forms (§9).
 * @var string $variant short|full
 * @var string $source
 */
$variant = $variant ?? 'full';
$key = 'contact-' . $variant;
$state = form_state($key);
$state['form'] = $key;
$v = $state['values'];
$sent = isset($_GET['sent']) && (($variant === 'short' && request_path() === '/') || ($variant === 'full' && request_path() === '/contact/'));
$p = $variant === 'short' ? 'cs' : 'cf';
?>
<?php if ($sent): ?>
<p class="form-success" role="status" id="<?= $variant === 'short' ? 'contact-sent' : 'contact-form' ?>">Thank you. Your message has been sent and a copy is on its way to your inbox. <?= e(REPLY_PROMISE) ?></p>
<?php else: ?>
<form class="form contact-form" id="<?= $variant === 'short' ? 'contact-short' : 'contact-form' ?>" method="post" action="/contact-submit/" novalidate>
<?php if (!empty($state['general'])): ?>
  <p class="form-error" role="alert"><?= e($state['general']) ?></p>
<?php endif; ?>
  <input type="hidden" name="form" value="<?= e($variant) ?>">
  <div class="field">
    <label for="<?= $p ?>-name">Name <span class="req">(required)</span></label>
    <input type="text" id="<?= $p ?>-name" name="name" required minlength="2" maxlength="100" autocomplete="name" value="<?= e($v['name'] ?? '') ?>"<?= field_aria($state, 'name') ?>>
    <?= field_error($state, 'name') ?>
  </div>
  <div class="field">
    <label for="<?= $p ?>-email">Email <span class="req">(required)</span></label>
    <input type="email" id="<?= $p ?>-email" name="email" required maxlength="254" autocomplete="email" value="<?= e($v['email'] ?? '') ?>"<?= field_aria($state, 'email') ?>>
    <?= field_error($state, 'email') ?>
  </div>
<?php if ($variant === 'full'): ?>
  <div class="field">
    <label for="cf-company">Company <span class="opt">(optional)</span></label>
    <input type="text" id="cf-company" name="company" maxlength="200" autocomplete="organization" value="<?= e($v['company'] ?? '') ?>">
  </div>
  <div class="field">
    <label for="cf-countries">Countries needed <span class="opt">(optional)</span></label>
    <input type="text" id="cf-countries" name="countries" maxlength="500" value="<?= e($v['countries'] ?? '') ?>">
  </div>
  <fieldset class="field">
    <legend>Data needed <span class="opt">(optional)</span></legend>
<?php foreach (DATA_NEEDED_OPTIONS as $k => $label): ?>
    <div class="check"><input type="checkbox" id="cf-data-<?= $k ?>" name="data[]" value="<?= $k ?>"<?= in_array($k, $v['data'] ?? [], true) ? ' checked' : '' ?>> <label for="cf-data-<?= $k ?>"><?= e($label) ?></label></div>
<?php endforeach; ?>
  </fieldset>
  <fieldset class="field">
    <legend>Format <span class="opt">(optional)</span></legend>
<?php foreach (FORMAT_OPTIONS as $k => $label): ?>
    <div class="check"><input type="radio" id="cf-format-<?= $k ?>" name="format" value="<?= $k ?>"<?= ($v['format'] ?? '') === $k ? ' checked' : '' ?>> <label for="cf-format-<?= $k ?>"><?= e($label) ?></label></div>
<?php endforeach; ?>
  </fieldset>
  <fieldset class="field">
    <legend>One-off or monthly <span class="opt">(optional)</span></legend>
<?php foreach (FREQUENCY_OPTIONS as $k => $label): ?>
    <div class="check"><input type="radio" id="cf-freq-<?= $k ?>" name="frequency" value="<?= $k ?>"<?= ($v['frequency'] ?? '') === $k ? ' checked' : '' ?>> <label for="cf-freq-<?= $k ?>"><?= e($label) ?></label></div>
<?php endforeach; ?>
  </fieldset>
<?php endif; ?>
  <div class="field">
    <label for="<?= $p ?>-message">Message <span class="req">(required)</span></label>
    <textarea id="<?= $p ?>-message" name="message" required rows="<?= $variant === 'full' ? 6 : 4 ?>" maxlength="5000"<?= field_aria($state, 'message') ?>><?= e($v['message'] ?? '') ?></textarea>
    <?= field_error($state, 'message') ?>
  </div>
  <?= form_hidden_fields($key, $source) ?>
  <p><button type="submit" class="btn">Send message</button></p>
</form>
<?php endif; ?>
