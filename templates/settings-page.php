<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc">

    <?php settings_errors(); ?>

    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="<?=$url_general;?>" class="nav-tab <?=$tab == 'general' ? 'nav-tab-active' : '';?>">
            <span><?php esc_html_e('General', 'lmfwc');?></span>
        </a>
        <a href="<?=$url_rest_api;?>" class="nav-tab <?=$tab == 'rest_api' ? 'nav-tab-active' : '';?>">
            <span><?php esc_html_e('REST API', 'lmfwc');?></span>
        </a>
    </nav>

    <?php if ($tab == 'general'): ?>

        <form action="options.php" method="POST">
            <?php settings_fields('lmfwc_settings_group'); ?>
            <?php do_settings_sections('lmfwc'); ?>
            <?php submit_button(); ?>
        </form>

    <?php elseif ($tab === 'rest_api'): ?>

        <?php if ($action === 'list'): ?>

            <?php include_once 'settings/rest-api-list.php'; ?>

        <?php elseif ($action === 'show'): ?>

            <?php include_once 'settings/rest-api-show.php'; ?>

        <?php else: ?>

            <?php include_once 'settings/rest-api-key.php'; ?>

        <?php endif; ?>

    <?php endif; ?>

</div>