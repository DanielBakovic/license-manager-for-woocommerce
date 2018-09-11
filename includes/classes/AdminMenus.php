<?php

namespace LicenseManager\Classes;

/**
 * Setup menus in WP admin.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

if (class_exists('AdminMenus', false)) {
    return new AdminMenus();
}

/**
 * AdminMenus Class.
 */
class AdminMenus
{
    private $crypto;

    /**
     * Class constructor.
     */
    public function __construct(
        \LicenseManager\Classes\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        // Plugin pages.
        add_action('admin_menu', array($this, 'createPluginPages'), 9);
        add_action('admin_init', array($this, 'initSettingsAPI'));

        // Meta Boxes.
        add_action('add_meta_boxes', array($this, 'createMetaBoxes'));
    }

    public function createPluginPages()
    {
        add_menu_page(
            __('License Manager', 'lima'),
            __('License Manager', 'lima'),
            'manage_options',
            'license_manager',
            array($this, 'licensesPage'),
            'dashicons-lock',
            10
        );
        add_submenu_page(
            'license_manager',
            __('License Manager', 'lima'),
            __('Licenses', 'lima'),
            'manage_options',
            'license_manager',
            array($this, 'licensesPage')
        );
        add_submenu_page(
            'license_manager',
            __('License Manager - Import', 'lima'),
            __('Import', 'lima'),
            'manage_options',
            'license_manager_add_import',
            array($this, 'licensesAddImportPage')
        );
        add_submenu_page(
            'license_manager',
            __('License Manager - Generators', 'lima'),
            __('Generators', 'lima'),
            'manage_options',
            'license_manager_generators',
            array($this, 'generatorsPage')
        );
        add_submenu_page(
            'license_manager',
            __('License Manager - Add New Generator', 'lima'),
            __('Add New Generator', 'lima'),
            'manage_options',
            'license_manager_generators_add',
            array($this, 'generatorsAddPage')
        );
        add_submenu_page(
            null,
            __('License Manager - Edit Generator', 'lima'),
            __('Edit Generator', 'lima'),
            'manage_options',
            'license_manager_generators_edit',
            array($this, 'generatorsEditPage')
        );
        add_submenu_page(
            'license_manager',
            __('License Manager - Settings', 'lima'),
            __('Settings', 'lima'),
            'manage_options',
            'license_manager_settings',
            array($this, 'settingsPage')
        );
    }

    public function createMetaBoxes($post_type)
    {
        // Not relevant for post types other than product and shop_order.
        if ($post_type != 'product' && $post_type != 'shop_order') return;

        // The edit order meta box.
        add_meta_box(
            'lm-licenses-meta-box',
            __('License Manager - Order Licenses', 'lima'),
            array($this, 'orderMetaBox'),
            'shop_order'
        );

        // The edit product meta box.
        add_meta_box(
            'lm-licenses-meta-box',
            __('License Manager - Product License Settings', 'lima'),
            array($this, 'productMetaBox'),
            'product'
        );
    }

    public function licensesPage()
    {
        $licenses = new \LicenseManager\Classes\Lists\LicensesList($this->crypto);

        add_screen_option(
            'per_page',
            array(
                'label'   => 'Licenses per page',
                'default' => 5,
                'option'  => 'licenses_per_page'
            )
        );

        include LM_TEMPLATES_DIR . 'licenses_page.php';
    }

    public function licensesAddImportPage()
    {
        $products = new \WP_Query(
            array(
                'post_type'      => 'product',
                'posts_per_page' => -1
            )
        );

        include LM_TEMPLATES_DIR . 'licenses_add_import_page.php';
    }

    public function settingsPage()
    {
        include LM_TEMPLATES_DIR . 'settings_page.php';
    }

    public function generatorsPage()
    {
        $generators = new \LicenseManager\Classes\Lists\GeneratorsList();

        add_screen_option(
            'per_page',
            array(
                'label'   => 'Generators per page',
                'default' => 5,
                'option'  => 'generators_per_page'
            )
        );

        include LM_TEMPLATES_DIR . 'generators_page.php';
    }

    public function generatorsAddPage()
    {
        include LM_TEMPLATES_DIR . 'generators_add_new.php';
    }

    public function generatorsEditPage()
    {
        include LM_TEMPLATES_DIR . 'generators_edit.php';
    }

    /**
     * @todo If statement should check if this order has licenses attached to it. Perhaps loop through the products and
     * Check if a meta data entry exists for any of them. Or set a meta data for the entire order, should it contain
     * license products.
     */
    public function orderMetaBox()
    {
        global $post;

        $licenses = Database::getLicenseKeysByOrderId($post->ID);

        include LM_METABOX_DIR . 'edit-order.php';
    }

    public function productMetaBox()
    {
        global $post;

        $generators = Database::getGenerators();
        $license_keys = array(
            'available' => Database::getLicenseKeysByProductId($post->ID, 3),
            'inactive' => Database::getLicenseKeysByProductId($post->ID, 4)
        );

        if (!$gen_id = get_post_meta($post->ID, '_lima_generator_id', true)) {
            $gen_id = false;
        }

        include LM_METABOX_DIR . 'edit-product.php';
    }

    public function initSettingsAPI()
    {
        new Settings();
    }

}