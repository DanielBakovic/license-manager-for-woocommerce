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
    /**
     * Class constructor.
     */
    public function __construct()
    {
        // Main menu.
        add_action('admin_menu', array($this, 'main'), 9);
        add_action('admin_menu', array($this, 'generators'), 9);
        add_action('admin_menu', array($this, 'generatorsAdd'), 9);
        add_action('admin_menu', array($this, 'settings'), 9);

        // WooCommerce related
        add_filter('woocommerce_product_data_tabs', array($this, 'wooLicenseProductDataTab'));
        add_action('woocommerce_product_data_panels', array($this, 'wooLicenseProductDataPanel'));

        // Meta Box
        add_action('add_meta_boxes', array($this, 'licensesMetaBoxHandler'));
    }

    public function main()
    {
        add_menu_page(
            __('License Manager', 'lima'),
            __('License Manager', 'lima'),
            'manage_options',
            'license_manager',
            array($this, 'mainPage'),
            'dashicons-lock',
            10
        );
        add_submenu_page(
            'license_manager',
            __('License Manager', 'lima'),
            __('Licenses', 'lima'),
            'manage_options',
            'license_manager',
            array($this, 'mainPage')
        );
    }

    public function generators()
    {
        add_submenu_page(
            'license_manager',
            __('License Manager - Generators', 'lima'),
            __('Generators', 'lima'),
            'manage_options',
            'license_manager_generators',
            array($this, 'generatorsPage')
        );
    }

    public function generatorsAdd()
    {
        add_submenu_page(
            'license_manager',
            __('Add New Generator', 'lima'),
            __('Add New Generator', 'lima'),
            'manage_options',
            'license_manager_generators_add',
            array($this, 'generatorsAddPage')
        );
    }

    public function settings()
    {
        add_submenu_page(
            'license_manager',
            __('License Manager - Settings', 'lima'),
            __('Settings', 'lima'),
            'manage_options',
            'license_manager_settings',
            array($this, 'settingsPage')
        );
    }

    public function wooLicenseProductDataTab($product_data_tabs)
    {
        $product_data_tabs['license-manager-tab'] = array(
            'label'  => __( 'License Manager', 'lima' ),
            'target' => 'license_manager_product_data'
        );

        return $product_data_tabs;
    }

    public function wooLicenseProductDataPanel()
    {
        global $woocommerce, $post;
            ?>
            <div id="license_manager_product_data" class="panel woocommerce_options_panel">
                <?php
                woocommerce_wp_checkbox(array( 
                    'id'            => 'sell_licenses', 
                    'wrapper_class' => 'show_if_simple', 
                    'label'         => __('Sell licenses for this product?', 'lima'),
                    'description'   => __('License Manager Description', 'lima'),
                    'default'       => '0',
                    'desc_tip'      => false,
                ));
                ?>
            </div>
        <?php
    }

    public function mainPage()
    {
        $licenses = new \LicenseManager\Classes\Lists\LicensesList();

        add_screen_option(
            'per_page',
            array(
                'label'   => 'Licenses per page',
                'default' => 5,
                'option'  => 'licenses_per_page'
            )
        );

        include LM_TEMPLATES_DIR . 'main_page.php';
    }

    public function settingsPage()
    {
        echo 'Settings Page!';
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

    /**
     * @todo If statement should check if this order has licenses attached to it. Perhaps loop through the products and
     * Check if a meta data entry exists for any of them. Or set a meta data for the entire order, should it contain
     * license products.
     * @todo Add a meta box for the edit products view.
     */
    public function licensesMetaBoxHandler($post_type)
    {
        if (1 == 1) {
            add_meta_box(
                'lm-licenses-meta-box',
                __('Licenses', 'lima'),
                array($this, 'licensesMetaBox'),
                'shop_order'
            );
        }
    }

    public function licensesMetaBox()
    {
        global $post;

        $licenses = Database::getLicenseKeys($post->ID);

        include LM_TEMPLATES_DIR . 'licenses_meta_box.php';
    }
}