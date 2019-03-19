<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?=__('Licenses', 'lmfwc'); ?></h1>
<a class="page-title-action" href="<?php echo esc_url($add_license_url); ?>">
    <span><?php echo __('Add/Import', 'lmfwc');?></span>
</a>
<hr class="wp-header-end">

<?php $licenses->views(); ?>

<form method="post">
    <?php
        $licenses->prepare_items();
        $licenses->display();
    ?>
</form>