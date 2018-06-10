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
     * Hook in tabs.
     */
    public function __construct()
    {
        // Add menus.
        add_action('admin_menu', array($this, 'main'), 9);
        add_action('admin_menu', array($this, 'generators'), 9);
        add_action('admin_menu', array($this, 'settings'), 9);

        // WooCommerce related
        add_filter('woocommerce_product_data_tabs', array($this, 'wooLicenseProductDataTab'));
        add_action('woocommerce_product_data_panels', array($this, 'wooLicenseProductDataPanel'));
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
        echo 'Generators Page!';
    }
}