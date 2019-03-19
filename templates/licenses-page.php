<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc">
    <?php
        if ($action === 'list'
            || $action === 'activate'
            || $action === 'deactivate'
            || $action === 'delete'
        ) {
            include_once('licenses/list-page.php');
        } elseif ($action === 'add') {
            include_once('licenses/add-page.php');
        } elseif ($action === 'edit') {
            include_once('licenses/edit-page.php');
        }
    ?>
</div>