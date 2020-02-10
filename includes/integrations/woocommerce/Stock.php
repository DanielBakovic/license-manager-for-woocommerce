<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce;

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
     * Increases the available stock of a WooCommerce Product by $amount.
     *
     * @param int|WC_Product $product WooCommerce Product object
     * @param int            $amount  Increment amount
     *
     * @return bool|WC_Product
     */
    public function increase($product, $amount = 1)
    {
        // Check if the setting is enabled
        if (1 !== 1) {
            return false;
        }

        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }

        if (!$product instanceof WC_Product) {
            return false;
        }

        $stock = $product->get_stock_quantity();

        if ($stock === null) {
            $stock = 0;
        }

        $stock += $amount;

        $product->set_stock_quantity($stock);
        $product->save();

        return $product;
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
        // Check if the setting is enabled
        if (1 !== 1) {
            return false;
        }

        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }

        if (!$product instanceof WC_Product) {
            return false;
        }

        $stock = $product->get_stock_quantity();

        if ($stock === null) {
            $stock = 0;
        }

        $stock -= $amount;

        $product->set_stock_quantity($stock);
        $product->save();

        return $product;
    }
}