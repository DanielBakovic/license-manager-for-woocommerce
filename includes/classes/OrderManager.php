<?php

namespace LicenseManager\Classes;

use \LicenseManager\Classes\Database;
use \LicenseManager\Classes\Abstracts\LicenseStatusEnum;

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
     * @var \LicenseManager\Classes\Crypto
     */
    protected $crypto;

    /**
     * Class constructor.
     */
    public function __construct(
        \LicenseManager\Classes\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        add_action('woocommerce_order_status_completed', array($this, 'generateOrderLicenses'));
        add_action('woocommerce_email_after_order_table', array($this, 'deliverLicenseKeys'), 10, 2);
    }

    /**
     * Generate licenses for a given order when the status changes to 'completed'
     *
     * @since 1.0.0
     *
     * @param int $order_id - WooCommerce Order ID
     *
     * @todo Implement sending licenses from other sources (imported or manually added lists for now).
     */
    public function generateOrderLicenses($order_id)
    {
        // Keys have already been generated for this order.
        if (get_post_meta($order_id, '_lima_order_complete')) return;

        $order    = new \WC_Order($order_id);
        $licenses = [];

        // Loop through the order items
        foreach ($order->get_items() as $item_data) {
            /**
             * @var $product WC_Product
             */
            $product = $item_data->get_product();

            // Check if the product has been activated for selling.
            if (!get_post_meta($product->get_id(), '_lima_licensed_product', true)) break;

            // Check if the product has active keys attached to it.
            if ($license_keys = Database::getLicenseKeysByProductId($product->get_id(), LicenseStatusEnum::ACTIVE)) {
                /**
                 * @todo Improve quantity check. (If generator is also assigned quantity is not a problem, otherwise
                 * more thorough checks are required).
                 */
                if ($item_data->get_quantity() > count($license_keys)) return;

                // Set the license keys as sold.
                do_action('lima_sell_imported_license_keys', array(
                    'license_keys' => $license_keys,
                    'order_id'     => $order_id,
                    'amount'       => $item_data->get_quantity()
                ));

                // Set the order as complete.
                update_post_meta($order_id, '_lima_order_complete', 1);

            // Check if the product has a generator assigned to it.
            } elseif ($gen_id = get_post_meta($product->get_id(), '_lima_generator_id', true)) {

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

                // Save the license keys.
                $save_license_args = array(
                    'order_id'   => $order_id,
                    'product_id' => $product->get_id(),
                    'licenses'   => $licenses['licenses'],
                    'expires_in' => $licenses['expires_in']
                );
                do_action('lima_save_generated_license_keys', $save_license_args);
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
     *
     * @todo Implement a second check (after the setting) to see if the admin manually sent out the keys.
     */
    public function deliverLicenseKeys($order, $is_admin_email)
    {
        // Send the keys out if the setting is active.
        if (Settings::get('_lima_auto_delivery')) {
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
                if (!get_post_meta($product->get_id(), '_lima_licensed_product', true)) break;

                $data[$product->get_id()]['name'] = $product->get_name();
                $data[$product->get_id()]['keys'] = Database::getLicenseKeysByOrderId($order->get_id(), LicenseStatusEnum::SOLD);
            }

            include LM_TEMPLATES_DIR . 'emails/email-order-license-keys.php';

        // Only display a notice.
        } else {
            include LM_TEMPLATES_DIR . 'emails/email-order-license-notice.php';
        }
    }

}