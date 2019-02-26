<h2>
    <span><?=__('REST API', 'lmfwc'); ?></span>
    <a class="add-new-h2" href="<?=admin_url(sprintf('admin.php?page=%s&tab=rest_api&create_key=1', \LicenseManagerForWooCommerce\AdminMenus::SETTINGS_PAGE));?>">
    <span><?=__('Add key', 'lmfwc');?></span>
</a></h1>
<hr class="wp-header-end">

<form method="post">
    <?php
        $keys->prepare_items();
        $keys->views();
        $keys->search_box(__('Search key', 'lmfwc'), 'key');
        $keys->display();
    ?>
</form>
