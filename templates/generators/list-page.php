<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Generators', 'lmfwc'); ?></h1>
<a href="<?php echo esc_url($add_generator_url) ;?>" class="page-title-action">
    <span><?php esc_html_e('Add new', 'lmfwc');?></span>
</a>
<p>
    <b><?php esc_html_e('Important:', 'lmfwc');?></b>
    <span><?php esc_html_e('You can not delete generators which are still assigned to active products! To delete those, please remove the generator from all of its assigned products first.', 'lmfwc');?></span>
</p>
<hr class="wp-header-end">

<form method="post">
    <?php
        $generators->prepare_items();
        $generators->display();
    ?>
</form>
