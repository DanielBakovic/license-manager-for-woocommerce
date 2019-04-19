<?php

namespace LicenseManagerForWooCommerce;

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
        add_filter(
            'woocommerce_product_data_tabs',
            array($this, 'simpleProductLicenseManagerTab')
        );
        add_action(
            'woocommerce_product_data_panels',
            array($this, 'simpleProductLicenseManagerPanel')
        );

        /**
         * @see
         */
        add_action(
            'woocommerce_product_after_variable_attributes',
            array($this, 'variableProductLicenseManagerFields'),
            10,
            3
        );

        add_action(
            'woocommerce_save_product_variation',
            array($this, 'variableProductLicenseManagerSaveAction'),
            10,
            2
        );

        // Change the product_data_tab icon
        add_action('admin_head', array($this, 'styleInventoryManagement'));

        add_action('save_post', array($this, 'savePost'), 10);
    }

    public function simpleProductLicenseManagerTab($tabs)
    {
        $tabs[self::ADMIN_TAB_NAME] = array(
            'label' => __('License Manager', 'lmfwc'),
            'target' => self::ADMIN_TAB_TARGET,
            'class' => array('show_if_simple'),
            'priority' => 21
        );

        return $tabs;
    }

    public function simpleProductLicenseManagerPanel()
    {
        global $post;

        $licensed          = get_post_meta($post->ID, 'lmfwc_licensed_product',                    true);
        $deliverd_quantity = get_post_meta($post->ID, 'lmfwc_licensed_product_delivered_quantity', true);
        $generator_id      = get_post_meta($post->ID, 'lmfwc_licensed_product_assigned_generator', true);
        $use_generator     = get_post_meta($post->ID, 'lmfwc_licensed_product_use_generator',      true);
        $use_stock         = get_post_meta($post->ID, 'lmfwc_licensed_product_use_stock',          true);

        $generator_options = array('' => __('Please select a generator', 'lmfwc'));

        foreach (apply_filters('lmfwc_get_generators', null) as $generator) {
            $generator_options[$generator->id] = sprintf(
                '(#%d) %s',
                $generator->id,
                $generator->name
            );
        }

        echo sprintf(
            '<div id="%s" class="panel woocommerce_options_panel"><div class="options_group">',
            self::ADMIN_TAB_TARGET
        );

        echo '<input type="hidden" name="lmfwc_edit_flag" value="true" />';

        // Checkbox "lmfwc_licensed_product"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product',
                'label'       => __('Sell license keys', 'lmfwc'),
                'description' => __('Sell license keys for this product', 'lmfwc'),
                'value'       => $licensed,
                'cbvalue'     => 1,
                'desc_tip'    => false
            )
        );

        // Number "lmfwc_licensed_product_deliver_amount"
        woocommerce_wp_text_input( 
            array( 
                'id'                => 'lmfwc_licensed_product_delivered_quantity',
                'label'             => __('Delivered quantity', 'lmfwc'),
                'value'             => $deliverd_quantity ? $deliverd_quantity : 1, 
                'description'       => __('Defines the amount of license keys to be delivered upon purchase.', 'lmfwc'),
                'type'              => 'number', 
                'custom_attributes' => array(
                        'step'  => 'any',
                        'min'   => '1'
                    ) 
            )
        );

        echo '</div><div class="options_group">';

        // Checkbox "lmfwc_licensed_product_use_generator"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product_use_generator',
                'label'       => __('Generate license keys', 'lmfwc'),
                'description' => __('Automatically generate license keys with each sold product', 'lmfwc'),
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
                'options' => $generator_options,
                'value' => $generator_id
            )
        );

        echo '</div><div class="options_group">';

        // Checkbox "lmfwc_licensed_product_use_stock"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product_use_stock',
                'label'       => __('Sell from stock', 'lmfwc'),
                'description' => __('Sell license keys from the available stock.', 'lmfwc'),
                'value'       => $use_stock,
                'cbvalue'     => 1,
                'desc_tip'    => false
            )
        );

        echo sprintf(
            '<p class="form-field"><label>%s</label><span class="description">%d %s</span></p>',
            __('Available', 'lmfwc'),
            apply_filters('lmfwc_get_available_stock', $post->ID),
            __('License key(s) in stock and available for sale', 'lmfwc')
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
        if (!array_key_exists('post_type', $_POST)
            || $_POST['post_type'] != 'product'
            || !array_key_exists('lmfwc_edit_flag', $_POST)
        ) {
            return;
        }

        // Update licensed product flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product', $_POST)) {
            update_post_meta($post_id, 'lmfwc_licensed_product', 1);
        } else {
            update_post_meta($post_id, 'lmfwc_licensed_product', 0);
        }

        // Update delivered quantity, according to field.
        $deliverd_quantity = absint($_POST['lmfwc_licensed_product_delivered_quantity']);

        update_post_meta(
            $post_id,
            'lmfwc_licensed_product_delivered_quantity',
            $deliverd_quantity ? $deliverd_quantity : 1
        );

        // Update the use stock flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product_use_stock', $_POST)) {
            update_post_meta($post_id, 'lmfwc_licensed_product_use_stock', 1);
        } else {
            update_post_meta($post_id, 'lmfwc_licensed_product_use_stock', 0);
        }

        // Update the assigned generator id, according to select field.
        update_post_meta(
            $post_id,
            'lmfwc_licensed_product_assigned_generator',
            intval($_POST['lmfwc_licensed_product_assigned_generator'])
        );

        // Update the use generator flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product_use_generator', $_POST)) {
            // You must select a generator if you wish to assign it to the product.
            if (!$_POST['lmfwc_licensed_product_assigned_generator']) {
                $error = new \WP_Error(2, __('Assign a generator if you wish to sell automatically generated licenses for this product.', 'lmfwc'));

                set_transient('lmfwc_error', $error, 45);
                update_post_meta($post_id, 'lmfwc_licensed_product_use_generator', 0);
                update_post_meta($post_id, 'lmfwc_licensed_product_assigned_generator', 0);
            } else {
                update_post_meta($post_id, 'lmfwc_licensed_product_use_generator', 1);
            }
        } else {
            update_post_meta($post_id, 'lmfwc_licensed_product_use_generator', 0);
            update_post_meta($post_id, 'lmfwc_licensed_product_assigned_generator', 0);
        }
    }

    public function variableProductLicenseManagerFields($loop, $variation_data, $variation)
    {
        global $post;

        $post_id           = $variation->ID;
        $licensed          = get_post_meta($post_id, 'lmfwc_licensed_product',                    true);
        $deliverd_quantity = get_post_meta($post_id, 'lmfwc_licensed_product_delivered_quantity', true);
        $generator_id      = get_post_meta($post_id, 'lmfwc_licensed_product_assigned_generator', true);
        $use_generator     = get_post_meta($post_id, 'lmfwc_licensed_product_use_generator',      true);
        $use_stock         = get_post_meta($post_id, 'lmfwc_licensed_product_use_stock',          true);

        $generator_options = array('' => __('Please select a generator', 'lmfwc'));

        foreach (apply_filters('lmfwc_get_generators', null) as $generator) {
            $generator_options[$generator->id] = sprintf(
                '(#%d) %s',
                $generator->id,
                $generator->name
            );
        }

        echo '<div class="panel woocommerce_options_panel" style="width: 100%;"><div class="options_group">';

        echo sprintf('<strong>%s</strong>', __('License Manager for WooCommerce', 'lmfwc'));

        echo '<input type="hidden" name="lmfwc_edit_flag" value="true" />';

        // Checkbox "lmfwc_licensed_product"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product',
                'label'       => __('Sell license key(s)', 'lmfwc'),
                'description' => __('Sell license keys for this variation', 'lmfwc'),
                'value'       => $licensed,
                'cbvalue'     => 1,
                'desc_tip'    => false
            )
        );

        // Number "lmfwc_licensed_product_deliver_amount"
        woocommerce_wp_text_input( 
            array( 
                'id'                => 'lmfwc_licensed_product_delivered_quantity',
                'label'             => __('Delivered quantity', 'lmfwc'),
                'value'             => $deliverd_quantity ? $deliverd_quantity : 1, 
                'description'       => __('Defines the amount of license keys to be delivered upon purchase.', 'lmfwc'),
                'type'              => 'number', 
                'custom_attributes' => array(
                        'step'  => 'any',
                        'min'   => '1'
                    ) 
            )
        );

        echo '</div><div class="options_group">';

        // Checkbox "lmfwc_licensed_product_use_generator"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product_use_generator',
                'label'       => __('Generate license keys', 'lmfwc'),
                'description' => __('Automatically generate license keys with each sold variation', 'lmfwc'),
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
                'options' => $generator_options,
                'value' => $generator_id
            )
        );

        echo '</div><div class="options_group">';

        // Checkbox "lmfwc_licensed_product_use_stock"
        woocommerce_wp_checkbox(
            array(
                'id'          => 'lmfwc_licensed_product_use_stock',
                'label'       => __('Sell from stock', 'lmfwc'),
                'description' => __('Sell license keys from the available stock.', 'lmfwc'),
                'value'       => $use_stock,
                'cbvalue'     => 1,
                'desc_tip'    => false
            )
        );

        echo sprintf(
            '<p class="form-field"><label>%s</label><span class="description">%d %s</span></p>',
            __('Available', 'lmfwc'),
            apply_filters('lmfwc_get_available_stock', $post_id),
            __('License key(s) in stock and available for sale.', 'lmfwc')
        );

        echo '</div></div>';
    }

    /**
     * Saves the data from the product variation fields
     * 
     * @param integer $variation_id WordPress Post ID of the variation
     * @param integer $i            Iteration count
     * 
     * @return null
     */
    public function variableProductLicenseManagerSaveAction($variation_id, $i)
    {
        // Update licensed product flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product', $_POST)) {
            update_post_meta($variation_id, 'lmfwc_licensed_product', 1);
        } else {
            update_post_meta($variation_id, 'lmfwc_licensed_product', 0);
        }

        // Update delivered quantity, according to field.
        $deliverd_quantity = absint($_POST['lmfwc_licensed_product_delivered_quantity']);

        update_post_meta(
            $variation_id,
            'lmfwc_licensed_product_delivered_quantity',
            $deliverd_quantity ? $deliverd_quantity : 1
        );

        // Update the use stock flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product_use_stock', $_POST)) {
            update_post_meta($variation_id, 'lmfwc_licensed_product_use_stock', 1);
        } else {
            update_post_meta($variation_id, 'lmfwc_licensed_product_use_stock', 0);
        }

        // Update the assigned generator id, according to select field.
        update_post_meta(
            $variation_id,
            'lmfwc_licensed_product_assigned_generator',
            intval($_POST['lmfwc_licensed_product_assigned_generator'])
        );

        // Update the use generator flag, according to checkbox.
        if (array_key_exists('lmfwc_licensed_product_use_generator', $_POST)) {
            // You must select a generator if you wish to assign it to the product.
            if (!$_POST['lmfwc_licensed_product_assigned_generator']) {
                $error = new \WP_Error(2, __('Assign a generator if you wish to sell automatically generated licenses for this product.', 'lmfwc'));

                set_transient('lmfwc_error', $error, 45);
                update_post_meta($variation_id, 'lmfwc_licensed_product_use_generator', 0);
                update_post_meta($variation_id, 'lmfwc_licensed_product_assigned_generator', 0);
            } else {
                update_post_meta($variation_id, 'lmfwc_licensed_product_use_generator', 1);
            }
        } else {
            update_post_meta($variation_id, 'lmfwc_licensed_product_use_generator', 0);
            update_post_meta($variation_id, 'lmfwc_licensed_product_assigned_generator', 0);
        }
    }
}