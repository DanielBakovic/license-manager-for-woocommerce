<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Add new license key(s)', 'lmfwc'); ?></h1>
<hr class="wp-header-end">

<h2><?php esc_html_e('Add a single license key', 'lmfwc'); ?></h2>
<?php include_once(LMFWC_TEMPLATES_DIR . 'licenses/forms/single-text.php'); ?>

<h2><?php esc_html_e('Add license keys in bulk', 'lmfwc'); ?></h2>
<?php include_once(LMFWC_TEMPLATES_DIR . 'licenses/forms/bulk-text.php'); ?>
