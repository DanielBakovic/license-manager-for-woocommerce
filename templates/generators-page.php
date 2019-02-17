<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?=__('Generators', 'lmfwc'); ?></h1>
    <a href="<?php menu_page_url('license_manager_generators_add');?>" class="page-title-action"><?=__('Add new');?></a>
    <p>
        <b><?=__('Important:');?></b>
        <span><?=__('You can not delete generators which are still assigned to active products! To delete those, please remove the generator from all of its assigned products first.');?></span>
    </p>
    <hr class="wp-header-end">

    <form method="post">
        <?php
            $generators->prepare_items();
            $generators->display();
        ?>
    </form>

</div>