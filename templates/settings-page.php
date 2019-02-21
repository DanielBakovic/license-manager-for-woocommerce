<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Settings', 'lmfwc'); ?></h1>

    <?php settings_errors(); ?>
    <form action="options.php" method="POST">

        <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="<?=admin_url('admin.php?page=license_manager_settings&amp;tab=general'); ?>" class="nav-tab nav-tab-active"><?=__('General', 'lmfwc');?></a>
            <a href="<?=admin_url('admin.php?page=license_manager_settings&amp;tab=advances'); ?>" class="nav-tab "><?=__('Advanced', 'lmfwc');?></a>
        </nav>

        <?php settings_fields('lmfwc_settings_group'); ?>
        <?php do_settings_sections('lmfwc'); ?>
        <?php submit_button(); ?>
    </form>

</div>