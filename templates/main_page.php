<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?=__('Licenses', 'lima'); ?></h1>
    <a href="" class="page-title-action"><?=__('Import');?></a>

    <form method="post">
        <?php
            $licenses->prepare_items();
            $licenses->display();
        ?>
    </form>

</div>