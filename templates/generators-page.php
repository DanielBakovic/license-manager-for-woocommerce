<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc">
    <?php
        if ($action === 'list'
            || $action === 'delete'
        ) {
            include_once('generators/list-page.php');
        } elseif ($action === 'add') {
            include_once('generators/add-page.php');
        } elseif ($action === 'edit') {
            include_once('generators/edit-page.php');
        }
    ?>
</div>