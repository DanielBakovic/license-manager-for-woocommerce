<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?=__('Licenses', 'lmfwc'); ?></h1>
    <a class="page-title-action" href="<?=admin_url(sprintf('admin.php?page=%s', \LicenseManagerForWooCommerce\AdminMenus::ADD_IMPORT_PAGE));?>">
        <span><?=__('Add/Import', 'lmfwc');?></span>
    </a>
    <hr class="wp-header-end">

    <?php $licenses->views(); ?>
    <form method="post">
        <?php
            $licenses->prepare_items();
            $licenses->display();
        ?>
    </form>

</div>