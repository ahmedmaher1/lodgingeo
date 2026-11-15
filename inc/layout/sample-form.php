<?php
/**
 * Sample download form (§8).
 * @var string $variant full|compact
 * @var string $source  page path the form sits on
 */
$state = form_state('sample') + ['form' => 'sample'];
$state['form'] = 'sample';
$v = $state['values'];
$full = ($variant ?? 'full') === 'full';
$fid = $full ? 'sample-form' : 'sample-form-compact';
?>
<form class="form sample-form" id="<?= $fid ?>" method="post" action="/sample-request/" novalidate>
<?php if (!empty($state['general'])): ?>
  <p class="form-error" role="alert"><?= e($state['general']) ?></p>
<?php endif; ?>
  <div class="field">
    <label for="s-name">Full name <span class="req">(required)</span></label>
    <input type="text" id="s-name" name="name" required minlength="2" maxlength="100" autocomplete="name" value="<?= e($v['name'] ?? '') ?>"<?= field_aria($state, 'name') ?>>
    <?= field_error($state, 'name') ?>
  </div>
  <div class="field">
    <label for="s-email">Work email <span class="req">(required)</span></label>
    <input type="email" id="s-email" name="email" required maxlength="254" autocomplete="email" value="<?= e($v['email'] ?? '') ?>"<?= field_aria($state, 'email') ?>>
    <?= field_error($state, 'email') ?>
  </div>
<?php if ($full): ?>
  <div class="field">
    <label for="s-company">Company <span class="opt">(optional)</span></label>
    <input type="text" id="s-company" name="company" maxlength="200" autocomplete="organization" value="<?= e($v['company'] ?? '') ?>">
  </div>
  <div class="field">
    <label for="s-countries">Countries you're interested in <span class="opt">(optional)</span></label>
    <input type="text" id="s-countries" name="countries" maxlength="500" value="<?= e($v['countries'] ?? '') ?>">
  </div>
<?php endif; ?>
  <div class="field check">
    <input type="checkbox" id="s-optin" name="opt_in" value="1"<?= !empty($v['opt_in']) ? ' checked' : '' ?>>
    <label for="s-optin">Email me when new monthly releases are available</label>
  </div>
  <?= form_hidden_fields('sample', $source) ?>
  <p><button type="submit" class="btn btn-primary">Download free sample</button></p>
  <p class="small">By downloading you agree to our <a href="/privacy/">privacy policy</a>.</p>
</form>
