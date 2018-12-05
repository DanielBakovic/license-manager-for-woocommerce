<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?=__('Licenses', 'lima'); ?></h1>
    <a class="page-title-action" href="<?=admin_url(sprintf('admin.php?page=%s', \LicenseManager\AdminMenus::ADD_IMPORT_PAGE));?>">
        <span><?=__('Add/Import', 'lima');?></span>
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