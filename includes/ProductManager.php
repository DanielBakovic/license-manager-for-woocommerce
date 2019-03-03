<?php

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Enums\LicenseStatusEnum;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce ProductManager.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class ProductManager
{
    const ADMIN_TAB_NAME   = 'license_manager_tab';
    const ADMIN_TAB_TARGET = 'license_manager_product_data';

    /**
     * Class constructor.
     */
    public function __construct(
    ) {
        /**
         * @see https://www.proy.info/woocommerce-admin-custom-product-data-tab/
         */
        add_filter('woocommerce_product_data_tabs',   array($this, 'inventoryManagementTab'));
        add_action('woocommerce_product_data_panels', array($this, 'inventoryManagementPanel'));

        // Change the product_data_tab icon
        add_action('admin_head', array($this, 'styleInventoryManagement'));

        add_action('save_post', array($this, 'savePost'), 10);
    }

    public function inventoryManagementTab($tabs)
    {
        global $post;

        if (get_post_meta($post->ID, 'lmfwc_licensed_product', true)) {

        }

        $tabs[self::ADMIN_TAB_NAME] = array(
            'label' => __('Licenses', 'lmfwc'),
            'target' => self::ADMIN_TAB_TARGET,
            'class' => array('show_if_simple'),
            'priority' => 21
        );

        return $tabs;
    }

    public function inventoryManagementPanel()
    {
        global $post;

        $licensed          = get_post_meta($post->ID, 'lmfwc_licensed_product',                    true);
        $generator_id      = get_post_meta($post->ID, 'lmfwc_licensed_product_assigned_generator', true);
        $use_generator     = get_post_meta($post->ID, 'lmfwc_licensed_product_use_generator',      true);
        $use_stock         = get_post_meta($post->ID, 'lmfwc_licensed_product_use_stock',          true);

        $generator_options = array('' => __('Please select a generator', 'lmfwc'));
        $license_keys      = array(
            'available' => Database::getLicenseKeysByProductId($post->ID, LicenseStatusEnum::ACTIVE),
            'inactive'  => Database::getLicenseKeysByProductId($post->ID, LicenseStatusEnum::INACTIVE)
        );

        foreach (Database::getGenerators() as $generator) {
            $generator_options[$generator->id] = $generator->name;
        }

        echo sprintf(
            '<div id="%s" class="panel woocommerce_options_panel"><div class="options_group">',
            self::ADMIN_TAB_TARGET
        );

        // Checkbox "lmfwc_licensed_product"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product',
                'label'       => __('Sell licenses', 'lmfwc'),
                'description' => __('Sell license keys for this product', 'lmfwc'),
                'value'       => $licensed,
                'cbvalue'     => 1,
                'desc_tip'    => false
            )
        );

        echo '</div><div class="options_group">';

        // Checkbox "lmfwc_licensed_product_use_generator"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product_use_generator',
                'label'       => __('Generate licenses', 'lmfwc'),
                'description' => __('Automatically generate license keys with each sold product.', 'lmfwc'),
                'value'       => $use_generator,
                'cbvalue'     => 1,
                'desc_tip'    => false
            )
        );

        // Dropdown "lmfwc_licensed_product_assigned_generator"
        woocommerce_wp_select(
            array(
                'id' => 'lmfwc_licensed_product_assigned_generator',
                'label' => __('Assign generator', 'lmfwc'),
                'options' => $generator_options
            )
        );

        echo '</div><div class="options_group">';

        // Checkbox "lmfwc_licensed_product_use_stock"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product_use_stock',
                'label'       => __('Sell from stock', 'lmfwc'),
                'description' => __('Sell added/imported license keys.', 'lmfwc'),
                'value'       => $use_stock,
                'cbvalue'     => 1,
                'desc_tip'    => false
            )
        );

        echo sprintf(
            '<p class="form-field"><label>%s</label><span class="description">%d %s</span></p>',
            __('Available', 'lmfwc'),
            apply_filters('lmfwc_get_available_stock', $post->ID),
            __('License key(s) in stock and available for sale.')
        );

        echo '</div></div>';
    }

    /**
     * @see https://docs.woocommerce.com/document/utilising-the-woocommerce-icon-font-in-your-extensions/
     * @see https://developer.wordpress.org/resource/dashicons/
     */
    public function styleInventoryManagement()
    {
        echo sprintf(
            '<style>#woocommerce-product-data ul.wc-tabs li.%s_options a:before { font-family: %s; content: "%s"; }</style>',
            self::ADMIN_TAB_NAME,
            'dashicons',
            '\f160'
        );
    }

    public function savePost($post_id)
    {
        // This is not a product.
        if (!array_key_exists('post_type', $_POST) || $_POST['post_type'] != 'product') return;

        // Update licensed product flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product', $_POST)) {
            update_post_meta($post_id, 'lmfwc_licensed_product', 1);
        } else {
            update_post_meta($post_id, 'lmfwc_licensed_product', 0);
        }

        // Update the use stock flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product_use_stock', $_POST)) {
            update_post_meta($post_id, 'lmfwc_licensed_product_use_stock', 1);
        } else {
            update_post_meta($post_id, 'lmfwc_licensed_product_use_stock', 0);
        }

        // Update the use generator flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product_use_generator', $_POST)) {
            // You must select a generator if you wish to assign it to the product.
            if (!$_POST['lmfwc_licensed_product_assigned_generator']) {
                $error = new \WP_Error(2, __('Assign a generator if you wish to sell automatically generated licenses for this product.', 'lmfwc'));

                set_transient('lmfwc_error', $error, 45);
                update_post_meta($post_id, 'lmfwc_licensed_product_use_generator', 0);
            } else {
                update_post_meta($post_id, 'lmfwc_licensed_product_use_generator', 1);
            }
        } else {
            update_post_meta($post_id, 'lmfwc_licensed_product_use_generator', 0);
        }

        // Update the assigned generator id, according to checkbox.
        update_post_meta(
            $post_id,
            'lmfwc_licensed_product_assigned_generator',
            intval($_POST['lmfwc_licensed_product_assigned_generator'])
        );
    }
}