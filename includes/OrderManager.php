<?php

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Database;
use \LicenseManagerForWooCommerce\Enums\LicenseStatusEnum;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce OrderManager.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class OrderManager
{
    /**
     * @var \LicenseManagerForWooCommerce\Crypto
     */
    protected $crypto;

    /**
     * Class constructor.
     */
    public function __construct(
        \LicenseManagerForWooCommerce\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        add_action('woocommerce_order_status_completed',          array($this, 'generateOrderLicenses'));
        add_action('woocommerce_email_after_order_table',         array($this, 'deliverLicenseKeys'), 10, 2);
        add_action('woocommerce_order_details_after_order_table', array($this, 'showBoughtLicenses'), 10, 1);
    }

    /**
     * Generate licenses for a given order when the status changes to 'completed'
     *
     * @since 1.0.0
     *
     * @param int $order_id - WooCommerce Order ID
     */
    public function generateOrderLicenses($order_id)
    {
        // Keys have already been generated for this order.
        if (get_post_meta($order_id, 'lmfwc_order_complete')) return;

        $order    = new \WC_Order($order_id);
        $licenses = [];

        // Loop through the order items
        foreach ($order->get_items() as $item_data) {
            /**
             * @var $product WC_Product
             */
            $product = $item_data->get_product();

            // Skip this product because it's not a licensed product.
            if (!get_post_meta($product->get_id(), 'lmfwc_licensed_product', true)) continue;

            $use_stock = get_post_meta($product->get_id(), 'lmfwc_licensed_product_use_stock', true);
            $use_generator = get_post_meta($product->get_id(), 'lmfwc_licensed_product_use_generator', true);

            // Skip this product because neither selling from stock or from generators is active.
            if (!$use_stock && !$use_generator) {
                continue;
            }

            // Sell license keys through available stock.
            if ($use_stock) {
                // Retrieve the available license keys.
                $license_keys = Database::getLicenseKeysByProductId(
                    $product->get_id(),
                    LicenseStatusEnum::ACTIVE
                );

                $available_stock = count($license_keys);

                // There are enough keys.
                if ($item_data->get_quantity() <= $available_stock) {
                    // Set the retrieved license keys as sold.
                    do_action('lmfwc_sell_imported_license_keys', array(
                        'license_keys' => $license_keys,
                        'order_id'     => $order_id,
                        'amount'       => $item_data->get_quantity()
                    ));
                // There aren not enough keys.
                } else {

                    // Set the available license keys as sold.
                    do_action('lmfwc_sell_imported_license_keys', array(
                        'license_keys' => $license_keys,
                        'order_id'     => $order_id,
                        'amount'       => $available_stock
                    ));

                    // The "use generator" option is active, generate them
                    if ($use_generator) {
                        $amount_to_generate = intval($item_data->get_quantity()) - intval($available_stock);
                        $generator_id = get_post_meta(
                            $product->get_id(),
                            'lmfwc_licensed_product_assigned_generator',
                            true
                        );

                        // Retrieve the generator from the database and set up the args.
                        $generator = Database::getGenerator($generator_id);

                        $licenses = apply_filters('lmfwc_create_license_keys', array(
                            'amount'       => $amount_to_generate,
                            'charset'      => $generator->charset,
                            'chunks'       => $generator->chunks,
                            'chunk_length' => $generator->chunk_length,
                            'separator'    => $generator->separator,
                            'prefix'       => $generator->prefix,
                            'suffix'       => $generator->suffix,
                            'expires_in'   => $generator->expires_in
                        ));

                        // Save the license keys.
                        do_action('lmfwc_insert_generated_license_keys', array(
                            'order_id'   => $order_id,
                            'product_id' => $product->get_id(),
                            'licenses'   => $licenses['licenses'],
                            'expires_in' => $licenses['expires_in'],
                            'status'     => LicenseStatusEnum::SOLD
                        ));
                    }
                }

            // Scenario 3 - Use generator.
            } else if (!$use_stock && $use_generator) {
                $generator_id = get_post_meta($product->get_id(), 'lmfwc_licensed_product_assigned_generator', true);

                // Retrieve the generator from the database and set up the args.
                $generator = Database::getGenerator($generator_id);

                $licenses = apply_filters('lmfwc_create_license_keys', array(
                    'amount'       => $item_data->get_quantity(),
                    'charset'      => $generator->charset,
                    'chunks'       => $generator->chunks,
                    'chunk_length' => $generator->chunk_length,
                    'separator'    => $generator->separator,
                    'prefix'       => $generator->prefix,
                    'suffix'       => $generator->suffix,
                    'expires_in'   => $generator->expires_in
                ));

                // Save the license keys.
                do_action('lmfwc_insert_generated_license_keys', array(
                    'order_id'   => $order_id,
                    'product_id' => $product->get_id(),
                    'licenses'   => $licenses['licenses'],
                    'expires_in' => $licenses['expires_in'],
                    'status'     => LicenseStatusEnum::SOLD
                ));
            }

            // Set the order as complete.
            update_post_meta($order_id, 'lmfwc_order_complete', 1);

            // Set status to delivered if the setting is on.
            if (Settings::get('lmfwc_auto_delivery')) {
                apply_filters('lmfwc_toggle_license_key_status', array(
                    'column_name' => 'order_id',
                    'operator' => 'eq',
                    'value' => $order_id,
                    'status' => LicenseStatusEnum::DELIVERED
                ));
            }
        }
    }

    /**
     * Adds the bought license keys to the "Order complete" email, or displays a notice - depending on the settings.
     *
     * @since 1.0.0
     *
     * @param int $order          - WC_Order
     * @param int $is_admin_email - boolean
     */
    public function deliverLicenseKeys($order, $is_admin_email)
    {
        // Return if the order isn't complete.
        if ($order->get_status() != 'completed' && !get_post_meta($order->get_id(), 'lmfwc_order_complete')) return;

        // Send the keys out if the setting is active.
        if (Settings::get('lmfwc_auto_delivery')) {
            $data = [];

            /**
             * @var $item_data WC_Order_Item_Product
             */
            foreach ($order->get_items() as $item_data) {
                /**
                 * @var $product WC_Product_Simple
                 */
                $product = $item_data->get_product();

                // Check if the product has been activated for selling.
                if (!get_post_meta($product->get_id(), 'lmfwc_licensed_product', true)) break;

                $data[$product->get_id()]['name'] = $product->get_name();
                $data[$product->get_id()]['keys'] = Database::getOrderedLicenseKeys(
                    $order->get_id(),
                    $product->get_id()
                );
            }

            include LMFWC_TEMPLATES_DIR . 'emails/email-order-license-keys.php';

        // Only display a notice.
        } else {
            include LMFWC_TEMPLATES_DIR . 'emails/email-order-license-notice.php';
        }
    }

    /**
     * Displays the bought licenses in the order view inside "My Account" -> "Orders".
     *
     * @since 1.0.0
     *
     * @param int $order - WC_Order
     */
    public function showBoughtLicenses($order)
    {
        // Return if the order isn't complete.
        if ($order->get_status() != 'completed' && !get_post_meta($order->get_id(), 'lmfwc_order_complete')) return;

        // Add missing style.
        if (!wp_style_is('lmfwc_admin_css', $list = 'enqueued' )) {
            wp_enqueue_style('lmfwc_admin_css', LMFWC_CSS_URL . 'main.css');
        }

        /**
         * @var $item_data WC_Order_Item_Product
         */
        foreach ($order->get_items() as $item_data) {
            /**
             * @var $product WC_Product_Simple
             */
            $product = $item_data->get_product();

            // Check if the product has been activated for selling.
            if (!get_post_meta($product->get_id(), 'lmfwc_licensed_product', true)) break;

            $data[$product->get_id()]['name'] = $product->get_name();
            $data[$product->get_id()]['keys'] = Database::getOrderedLicenseKeys(
                $order->get_id(),
                $product->get_id()
            );
        }

        include LMFWC_TEMPLATES_DIR . 'order-view-license-keys.php';
    }

}