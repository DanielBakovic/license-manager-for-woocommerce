<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?=__('Generators', 'lima'); ?></h1>
    <a href="<?php menu_page_url('license_manager_generators_add');?>" class="page-title-action"><?=__('Add new');?></a>

    <form method="post">
        <?php
            $generators->prepare_items();
            $generators->display();
        ?>
    </form>

</div>