<?php

namespace LicenseManager\Classes;

/**
 * LicenseManager OrderManager.
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * OrderManager class.
 */
class OrderManager
{
    public function __construct()
    {
        add_action('woocommerce_order_status_completed', array($this, 'generateOrderLicences'));
    }

    public function generateOrderLicences($order_id)
    {
        $order    = new \WC_Order($order_id);
        $licenses = [];

        foreach ($order->get_items() as $item_data) {
            /**
             * @todo check if the product is a license product.
             */
            $product = $item_data->get_product(); // Get an instance of the corresponding WC_Product object

            /**
             * @todo Obtain the parameters programatically, from the options or generator rule?
             */

            // Create the license keys
            $create_license_args = array(
                'amount'       => $item_data->get_quantity(),
                'charset'      => '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ',
                'chunks'       => 2,
                'chunk_length' => 5,
                'separator'    => '-'
            );
            $licenses = apply_filters('LM_create_license_keys', $create_license_args);

            // Save the license keys
            $save_license_args = array(
                'order_id' => $order_id,
                'product_id' => $product->get_id(),
                'licenses' => $licenses['licenses']
            );
            do_action('LM_save_license_keys', $save_license_args);

            // Log to file
            Logger::file(array(
                'order_id' => $order_id,
                'licenses' => $licenses
            ));
        }
    }
}