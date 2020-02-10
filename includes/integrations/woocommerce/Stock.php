<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce;

use LicenseManagerForWooCommerce\Settings;
use WC_Product;

defined('ABSPATH') || exit;

class Stock
{
    /**
     * Stock constructor.
     */
    public function __construct()
    {
        add_filter('lmfwc_stock_increase', array($this, 'increase'), 10, 2);
        add_filter('lmfwc_stock_decrease', array($this, 'decrease'), 10, 2);
    }

    /**
     * Class internal function used to modify the stock amount.
     *
     * @param int|WC_Product $product
     * @param string         $action
     * @param int            $amount
     *
     * @return bool|WC_Product
     */
    private function modify($product, $action, $amount = 1)
    {
        // Check if the setting is enabled
        if (!Settings::get('lmfwc_enable_stock_manager')) {
            return false;
        }

        // Retrieve the WooCommerce Product if we're given an ID
        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }

        // No need to modify if WooCommerce is not managing the stock
        if (!$product instanceof WC_Product || !$product->managing_stock()) {
            return false;
        }

        // Retrieve the current stock
        $stock = $product->get_stock_quantity();

        // Normalize
        if ($stock === null) {
            $stock = 0;
        }

        // Add or subtract the given amount to the stock
        if ($action === 'increase') {
            $stock += $amount;
        } elseif ($action === 'decrease') {
            $stock -= $amount;
        }

        // Set and save
        $product->set_stock_quantity($stock);
        $product->save();

        return $product;
    }

    /**
     * Increases the available stock of a WooCommerce Product by $amount.
     *
     * @param int|WC_Product $product WooCommerce Product object
     * @param int            $amount  Increment amount
     *
     * @return bool|WC_Product
     */
    public function increase($product, $amount = 1)
    {
        return $this->modify($product,'increase', $amount);
    }

    /**
     * Decreases the available stock of a WooCommerce Product by $amount.
     *
     * @param WC_Product $product WooCommerce Product object
     * @param int        $amount  Decrement amount
     *
     * @return bool|WC_Product
     */
    public function decrease($product, $amount = 1)
    {
        return $this->modify($product,'decrease', $amount);
    }
}
