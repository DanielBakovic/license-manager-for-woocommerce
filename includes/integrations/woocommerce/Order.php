<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce;

use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Models\Resources\Generator as GeneratorResourceModel;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\Generator as GeneratorResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Settings;
use function WC;
use WC_Order;
use WC_Order_Item;
use WC_Product;

defined('ABSPATH') || exit;

class Order
{
    /**
     * OrderManager constructor.
     */
    public function __construct() {
        add_action('woocommerce_order_status_completed',               array($this, 'generateOrderLicenses'));
        add_action('woocommerce_order_details_after_order_table',      array($this, 'showBoughtLicenses'),       10, 1);
        add_filter('woocommerce_order_actions',                        array($this, 'addSendLicenseKeysAction'), 10, 1);
        add_action('woocommerce_order_action_lmfwc_send_license_keys', array($this, 'processSendLicenseKeysAction'));
    }

    /**
     * @param integer $orderId
     */
    public function generateOrderLicenses($orderId)
    {
        // Keys have already been generated for this order.
        if (get_post_meta($orderId, 'lmfwc_order_complete')) {
            return;
        }

        /** @var WC_Order $order */
        $order = new WC_Order($orderId);

        /** @var WC_Order_Item $itemData */
        foreach ($order->get_items() as $itemData) {
            /** @var WC_Product $product */
            $product = $itemData->get_product();

            // Skip this product because it's not a licensed product.
            if (!get_post_meta($product->get_id(), 'lmfwc_licensed_product', true)){
                continue;
            }

            $useStock = get_post_meta($product->get_id(), 'lmfwc_licensed_product_use_stock', true);
            $useGenerator = get_post_meta($product->get_id(), 'lmfwc_licensed_product_use_generator', true);

            // Skip this product because neither selling from stock or from generators is active.
            if (!$useStock && !$useGenerator) {
                continue;
            }

            $deliveredQuantity = absint(
                get_post_meta(
                    $product->get_id(),
                    'lmfwc_licensed_product_delivered_quantity',
                    true
                )
            );

            // Determines how many times should the license key be delivered
            if (!$deliveredQuantity) {
                $deliveredQuantity = 1;
            }

            // Set the needed delivery amount
            $neededAmount = absint($itemData->get_quantity()) * $deliveredQuantity;

            // Sell license keys through available stock.
            if ($useStock) {
                // Retrieve the available license keys.
                /** @var LicenseResourceModel[] $licenseKeys */
                $licenseKeys = LicenseResourceRepository::instance()->findAllBy(
                    array(
                        'product_id' => $product->get_id(),
                        'status' => LicenseStatus::ACTIVE
                    )
                );

                // Retrieve the current stock amount
                $availableStock = count($licenseKeys);

                // There are enough keys.
                if ($neededAmount <= $availableStock) {
                    // Set the retrieved license keys as "SOLD".
                    apply_filters(
                        'lmfwc_sell_imported_license_keys',
                        $licenseKeys,
                        $orderId,
                        $neededAmount
                    );
                    // There are not enough keys.
                } else {
                    // Set the available license keys as "SOLD".
                    apply_filters(
                        'lmfwc_sell_imported_license_keys',
                        $licenseKeys,
                        $orderId,
                        $availableStock
                    );

                    // The "use generator" option is active, generate them
                    if ($useGenerator) {
                        $amountToGenerate = $neededAmount - $availableStock;
                        $generatorId = get_post_meta(
                            $product->get_id(),
                            'lmfwc_licensed_product_assigned_generator',
                            true
                        );

                        // Retrieve the generator from the database and set up the args.
                        /** @var GeneratorResourceModel $generator */
                        $generator = GeneratorResourceRepository::instance()->find($generatorId);

                        $licenses = apply_filters('lmfwc_create_license_keys', array(
                            'amount'       => $amountToGenerate,
                            'charset'      => $generator->getCharset(),
                            'chunks'       => $generator->getChunks(),
                            'chunk_length' => $generator->getChunkLength(),
                            'separator'    => $generator->getSeparator(),
                            'prefix'       => $generator->getPrefix(),
                            'suffix'       => $generator->getSuffix(),
                            'expires_in'   => $generator->getExpiresIn()
                        ));

                        // Save the license keys.
                        apply_filters(
                            'lmfwc_insert_generated_license_keys',
                            $orderId,
                            $product->get_id(),
                            $licenses['licenses'],
                            $licenses['expires_in'],
                            LicenseStatus::SOLD,
                            $generator
                        );

                        // Create a backorder
                    } else {
                        // Coming soon...
                    }
                }

                // Scenario 3 - Use generator.
            } else if (!$useStock && $useGenerator) {
                $generatorId = get_post_meta($product->get_id(), 'lmfwc_licensed_product_assigned_generator', true);

                // Retrieve the generator from the database and set up the args.
                /** @var GeneratorResourceModel $generator */
                $generator = GeneratorResourceRepository::instance()->find($generatorId);

                $licenses = apply_filters('lmfwc_create_license_keys', array(
                    'amount'       => $neededAmount,
                    'charset'      => $generator->getCharset(),
                    'chunks'       => $generator->getChunks(),
                    'chunk_length' => $generator->getChunkLength(),
                    'separator'    => $generator->getSeparator(),
                    'prefix'       => $generator->getPrefix(),
                    'suffix'       => $generator->getSuffix(),
                    'expires_in'   => $generator->getExpiresIn()
                ));

                // Save the license keys.
                apply_filters(
                    'lmfwc_insert_generated_license_keys',
                    $orderId,
                    $product->get_id(),
                    $licenses['licenses'],
                    $licenses['expires_in'],
                    LicenseStatus::SOLD,
                    $generator
                );
            }

            // Set the order as complete.
            update_post_meta($orderId, 'lmfwc_order_complete', 1);

            // Set status to delivered if the setting is on.
            if (Settings::get('lmfwc_auto_delivery')) {
                LicenseResourceRepository::instance()->updateBy(
                    array('order_id' => $orderId),
                    array('status' => LicenseStatus::DELIVERED)
                );
            }
        }
    }

    /**
     * Displays the bought licenses in the order view inside "My Account" -> "Orders".
     *
     * @param WC_Order $order
     */
    public function showBoughtLicenses($order)
    {
        // Return if the order isn't complete.
        if ($order->get_status() != 'completed'
            && !get_post_meta($order->get_id(), 'lmfwc_order_complete')
        ) {
            return;
        }

        $data = apply_filters('lmfwc_get_customer_license_keys', $order);

        // No license keys found, nothing to do.
        if (!$data) {
            return;
        }

        // Add missing style.
        if (!wp_style_is('lmfwc_admin_css', $list = 'enqueued' )) {
            wp_enqueue_style('lmfwc_admin_css', LMFWC_CSS_URL . 'main.css');
        }

        $date_format = get_option('date_format');
        $heading     = apply_filters('lmfwc_license_keys_table_heading', null);
        $valid_until = apply_filters('lmfwc_license_keys_table_valid_until', null);

        include LMFWC_TEMPLATES_DIR . 'order-view-license-keys.php';
    }

    /**
     * Adds a new order action used to resend the sold license keys
     *
     * @param array $actions
     *
     * @return array
     */
    public function addSendLicenseKeysAction($actions)
    {
        global $post;

        if (!empty(LicenseResourceRepository::instance()->findAllBy(array('order_id' => $post->ID)))) {
            $actions['lmfwc_send_license_keys'] = __('Send license key(s) to customer', 'lmfwc');
        }

        return $actions;
    }

    /**
     * Sends out the ordered license keys.
     *
     * @param WC_Order $order
     */
    public function processSendLicenseKeysAction($order)
    {
        WC()->mailer()->emails['LMFWC_Customer_Deliver_License_Keys']->trigger($order->get_id(), $order);
    }
}