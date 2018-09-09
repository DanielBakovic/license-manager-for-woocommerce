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
    /**
     * Class constructor.
     */
    public function __construct()
    {
        add_action('woocommerce_order_status_completed', array($this, 'generateOrderLicences'));
    }

    /**
     * Generate licenses for a given order when the status changes to 'completed'
     *
     * @since 1.0.0
     *
     * @param int $order_id - WooCommerce Order ID
     *
     * @todo Implement sending licences from other sources (imported or manually added lists for now).
     */
    public function generateOrderLicences($order_id)
    {
        // Keys have already been generated for this order, since there's a status for it.
        if (get_post_meta($order_id, '_lima_order_status')) {
            return;
        }

        $order    = new \WC_Order($order_id);
        $licenses = [];

        foreach ($order->get_items() as $item_data) {
            // Get an instance of the corresponding WC_Product object
            $product = $item_data->get_product();

            // Check if the product has a generator assigned to it.
            if ($gen_id = get_post_meta($product->get_id(), '_lima_generator_id', true)) {

                // Obtain the generator details from the database and set up the args.
                $generator = Database::getGenerator($gen_id);
                $create_license_args = array(
                    'amount'       => $item_data->get_quantity(),
                    'charset'      => $generator->charset,
                    'chunks'       => $generator->chunks,
                    'chunk_length' => $generator->chunk_length,
                    'separator'    => $generator->separator,
                    'prefix'       => $generator->prefix,
                    'suffix'       => $generator->suffix,
                    'expires_in'   => $generator->expires_in
                );

                $licenses = apply_filters('lima_create_license_keys', $create_license_args);

                // Save the license keys
                $save_license_args = array(
                    'order_id'   => $order_id,
                    'product_id' => $product->get_id(),
                    'licenses'   => $licenses['licenses'],
                    'expires_in' => $licenses['expires_in']
                );
                do_action('lima_save_license_keys', $save_license_args);
            }
        }
    }
}