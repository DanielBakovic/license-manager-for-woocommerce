<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?=__('Licenses', 'lima'); ?></h1>
    <a href="<?=admin_url('admin.php?page=licence_manager_add_import');?>" class="page-title-action">
        <span><?=__('Add/Import', 'lima');?></span>
    </a>

    <form method="post">
        <?php
            $licenses->prepare_items();
            $licenses->display();
        ?>
    </form>

</div>