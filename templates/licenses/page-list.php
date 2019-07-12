<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('License keys', 'lmfwc'); ?></h1>
<a class="page-title-action" href="<?php echo esc_url($addLicenseUrl); ?>">
    <span><?php esc_html_e('Add new', 'lmfwc');?></span>
</a>
<a class="page-title-action" href="<?php echo esc_url($importLicenseUrl); ?>">
    <span><?php esc_html_e('Import', 'lmfwc');?></span>
</a>
<hr class="wp-header-end">

<?php $licenses->views(); ?>

<form method="post">
    <?php
        $licenses->prepare_items();
        $licenses->display();
    ?>
</form>

<span class="lmfwc-txt-copied-to-clipboard" style="display: none"><?php esc_html_e('Copied to clipboard', 'lmfwc'); ?></span>