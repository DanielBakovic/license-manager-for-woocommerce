<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?=__('Licenses', 'lima'); ?></h1>
    <a href="<?=admin_url(sprintf('admin.php?page=%s', \LicenseManager\Classes\AdminMenus::ADD_IMPORT_PAGE));?>" class="page-title-action">
        <span><?=__('Add/Import', 'lima');?></span>
    </a>

    <form method="post">
        <?php
            $licenses->prepare_items();
            $licenses->display();
        ?>
    </form>

</div>