<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Settings', 'lmfwc'); ?></h1>

    <?php settings_errors(); ?>
    <form action="options.php" method="POST">
        <?php settings_fields('lmfwc_settings_group'); ?>
        <?php do_settings_sections('lmfwc'); ?>
        <?php submit_button(); ?>
    </form>

</div>