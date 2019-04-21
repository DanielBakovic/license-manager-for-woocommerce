<?php

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;

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
     * Class constructor.
     */
    public function __construct() {
        add_action('woocommerce_order_status_completed',               array($this, 'generateOrderLicenses'));
        add_action('woocommerce_email_after_order_table',              array($this, 'deliverLicenseKeys'),       10, 4);
        add_action('woocommerce_order_details_after_order_table',      array($this, 'showBoughtLicenses'),       10, 1);
        add_filter('woocommerce_order_actions',                        array($this, 'addSendLicenseKeysAction'), 10, 1);
        add_action('woocommerce_order_action_lmfwc_send_license_keys', array($this, 'processSendLicenseKeysAction'));
    }

    /**
     * Generate licenses for a given order when the status changes to 'completed'
     *
     * @param integer $order_id WooCommerce Order ID
     *
     * @since  1.0.0
     * @return null
     */
    public function generateOrderLicenses($order_id)
    {
        // Keys have already been generated for this order.
        if (get_post_meta($order_id, 'lmfwc_order_complete')) return;

        $order    = new \WC_Order($order_id);
        $licenses = array();

        // Loop through the order items
        foreach ($order->get_items() as $item_data) {
            /**
             * @var $product WC_Product
             */
            $product = $item_data->get_product();

            // Skip this product because it's not a licensed product.
            if (!get_post_meta($product->get_id(), 'lmfwc_licensed_product', true)){
                continue;
            }

            $use_stock = get_post_meta($product->get_id(), 'lmfwc_licensed_product_use_stock', true);
            $use_generator = get_post_meta($product->get_id(), 'lmfwc_licensed_product_use_generator', true);

            // Skip this product because neither selling from stock or from generators is active.
            if (!$use_stock && !$use_generator) {
                continue;
            }

            // Determines how many times should the license key be delivered
            if (!$delivered_quantity = absint(get_post_meta(
                    $product->get_id(),
                    'lmfwc_licensed_product_delivered_quantity',
                    true
                ))
            ) {
                $delivered_quantity = 1;
            }

            // Set the needed delivery amount
            $needed_amount = absint($item_data->get_quantity()) * $delivered_quantity;

            // Sell license keys through available stock.
            if ($use_stock) {
                // Retrieve the available license keys.
                $license_keys = apply_filters(
                    'lmfwc_get_product_license_keys',
                    $product->get_id(),
                    LicenseStatusEnum::ACTIVE
                );

                // Retrieve the current stock amount
                $available_stock = count($license_keys);

                // There are enough keys.
                if ($needed_amount <= $available_stock) {
                    // Set the retrieved license keys as "SOLD".
                    apply_filters(
                        'lmfwc_sell_imported_license_keys',
                        $license_keys,
                        $order_id,
                        $needed_amount
                    );
                // There are not enough keys.
                } else {
                    // Set the available license keys as "SOLD".
                    apply_filters(
                        'lmfwc_sell_imported_license_keys',
                        $license_keys,
                        $order_id,
                        $available_stock
                    );

                    // The "use generator" option is active, generate them
                    if ($use_generator) {
                        $amount_to_generate = $needed_amount - $available_stock;
                        $generator_id = get_post_meta(
                            $product->get_id(),
                            'lmfwc_licensed_product_assigned_generator',
                            true
                        );

                        // Retrieve the generator from the database and set up the args.
                        $generator = apply_filters('lmfwc_get_generator', $generator_id);

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
                        apply_filters(
                            'lmfwc_insert_generated_license_keys',
                            $order_id,
                            $product->get_id(),
                            $licenses['licenses'],
                            $licenses['expires_in'],
                            LicenseStatusEnum::SOLD,
                            get_current_user_id(),
                            $generator
                        );

                    // Create a backorder
                    } else {
                        // Coming soon...
                    }
                }

            // Scenario 3 - Use generator.
            } else if (!$use_stock && $use_generator) {
                $generator_id = get_post_meta($product->get_id(), 'lmfwc_licensed_product_assigned_generator', true);

                // Retrieve the generator from the database and set up the args.
                $generator = apply_filters('lmfwc_get_generator', $generator_id);

                $licenses = apply_filters('lmfwc_create_license_keys', array(
                    'amount'       => $needed_amount,
                    'charset'      => $generator->charset,
                    'chunks'       => $generator->chunks,
                    'chunk_length' => $generator->chunk_length,
                    'separator'    => $generator->separator,
                    'prefix'       => $generator->prefix,
                    'suffix'       => $generator->suffix,
                    'expires_in'   => $generator->expires_in
                ));

                // Save the license keys.
                apply_filters(
                    'lmfwc_insert_generated_license_keys',
                    $order_id,
                    $product->get_id(),
                    $licenses['licenses'],
                    $licenses['expires_in'],
                    LicenseStatusEnum::SOLD,
                    get_current_user_id(),
                    $generator
                );
            }

            // Set the order as complete.
            update_post_meta($order_id, 'lmfwc_order_complete', 1);

            // Set status to delivered if the setting is on.
            if (Settings::get('lmfwc_auto_delivery')) {
                apply_filters(
                    'lmfwc_toggle_license_key_status',
                    'order_id',
                    'eq',
                    $order_id,
                    LicenseStatusEnum::DELIVERED
                );
            }
        }
    }

    /**
     * Adds the bought license keys to the "Order complete" email, or displays a
     * notice - depending on the settings.
     *
     * @param WC_Order $order          The WooCommerce Order object
     * @param boolean  $is_admin_email Either true or false
     * @param boolean  $plain_text     Plain text or HTML email identifier
     * @param WC_Email $email          The WooCommerce Email object
     *
     * @since 1.0.0
     */
    public function deliverLicenseKeys($order, $is_admin_email, $plain_text, $email)
    {
        // Return if the order isn't complete.
        if ($order->get_status() != 'completed'
            && !get_post_meta($order->get_id(), 'lmfwc_order_complete')
        ) {
            return;
        }

        if (Settings::get('lmfwc_auto_delivery')) {

            // Send the keys out if the setting is active.
            if ($plain_text) {
                echo wc_get_template(
                    'emails/plain/email-order-license-keys.php',
                    array(
                        'heading'       => apply_filters('lmfwc_license_keys_table_heading', null),
                        'valid_until'   => apply_filters('lmfwc_license_keys_table_valid_until', null),
                        'data'          => apply_filters('lmfwc_get_customer_license_keys', $order),
                        'date_format'   => get_option('date_format'),
                        'order'         => $order,
                        'sent_to_admin' => $is_admin_email,
                        'plain_text'    => true,
                        'email'         => $email
                    ),
                    '',
                    LMFWC_TEMPLATES_DIR
                );
            } else {
                echo wc_get_template_html(
                    'emails/email-order-license-keys.php',
                    array(
                        'heading'       => apply_filters('lmfwc_license_keys_table_heading', null),
                        'valid_until'   => apply_filters('lmfwc_license_keys_table_valid_until', null),
                        'data'          => apply_filters('lmfwc_get_customer_license_keys', $order),
                        'date_format'   => get_option('date_format'),
                        'order'         => $order,
                        'sent_to_admin' => $is_admin_email,
                        'plain_text'    => false,
                        'email'         => $email
                    ),
                    '',
                    LMFWC_TEMPLATES_DIR
                );
            }

        } else {

            // Only display a notice.
            if ($plain_text) {
                echo wc_get_template(
                    'emails/plain/email-order-license-notice.php',
                    array(),
                    '',
                    LMFWC_TEMPLATES_DIR
                );
            } else {
                echo wc_get_template_html(
                    'emails/email-order-license-notice.php',
                    array(),
                    '',
                    LMFWC_TEMPLATES_DIR
                );
            }

            include LMFWC_TEMPLATES_DIR . '';
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
        if ($order->get_status() != 'completed'
            && !get_post_meta($order->get_id(), 'lmfwc_order_complete')
        ) {
            return;
        }

        // Add missing style.
        if (!wp_style_is('lmfwc_admin_css', $list = 'enqueued' )) {
            wp_enqueue_style('lmfwc_admin_css', LMFWC_CSS_URL . 'main.css');
        }

        $data = apply_filters('lmfwc_get_customer_license_keys', $order);
        $date_format = get_option('date_format');
        $heading = apply_filters('lmfwc_license_keys_table_heading', null);
        $valid_until = apply_filters('lmfwc_license_keys_table_valid_until', null);

        include LMFWC_TEMPLATES_DIR . 'order-view-license-keys.php';
    }

    /**
     * Adds a new order action used to resend the sold license keys
     * 
     * @param array $actions Currently available order actions
     * 
     * @return array
     */
    public function addSendLicenseKeysAction($actions)
    {
        if (1 == 1) {
            $actions['lmfwc_send_license_keys'] = __('Send license key(s) to customer', 'lmfwc');
        }

        return $actions;
    }

    /**
     * Sends out the ordered license keys.
     * 
     * @param WC_Order $order The WooCommerce order on which the action is being performed
     */
    public function processSendLicenseKeysAction($order)
    {
        \WC()->mailer()->emails['LMFWC_Customer_Deliver_License_Keys']->trigger($order->get_id(), $order);
    }
}