<?php
defined('C5_EXECUTE') or die('Access denied.');

$form = app()->make('helper/form');
$config = app()->make('config');
?>

<div class="alert alert-info">
    <?= t('A site key and secret key must be provided. They can be obtained from the <a href="%s" target="_blank">Cloudflare website</a>.', 'https://developers.cloudflare.com/turnstile/get-started/') ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('site_key', t('Site Key')) ?>
    <?php  echo  $form->text('site_key', $config->get('msv_cloudflare_turnstile.turnstile.site_key', ''), ['maxlength'=>100]) ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('secret_key', t('Secret Key')) ?>
    <?php  echo  $form->text('secret_key', $config->get('msv_cloudflare_turnstile.turnstile.secret_key', ''),  ['maxlength'=>100]) ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('size', t('Size')) ?>
    <?php  echo  $form->select('size', ['normal'=>t('Normal'), 'compact'=>t('Compact')], $config->get('msv_cloudflare_turnstile.turnstile.size', 'normal')) ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('theme', t('Theme')) ?>
    <?php  echo  $form->select('theme', ['auto'=>t('Auto'), 'light'=>t('Light'), 'dark'=>t('Dark')], $config->get('msv_cloudflare_turnstile.turnstile.theme', 'auto')) ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('appearance', t('Displayed')) ?>
    <?php  echo  $form->select('appearance', ['always'=>t('Always'), 'interaction-only'=>t('Only when interaction required')], $config->get('msv_cloudflare_turnstile.turnstile.appearance', 'always')) ?>
</div>


<div class="form-group">
    <?= $form->label('', t('Options')) ?>
    <div class="form-check">
        <label class="form-check-label">
            <?= $form->checkbox('log_failed', '1', $config->get('msv_cloudflare_turnstile.turnstile.log_failed')) ?>
            <?= t('Log failed CAPTCHA events') ?>
        </label>
    </div>
</div>

