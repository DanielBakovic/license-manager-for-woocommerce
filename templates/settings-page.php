<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Settings', 'lima'); ?></h1>

    <?php settings_errors(); ?>
    <form action="options.php" method="POST">
        <?php settings_fields('_lima_settings_group'); ?>
        <?php do_settings_sections('_lima'); ?>
        <?php submit_button(); ?>
    </form>

</div>