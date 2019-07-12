<?php

namespace LicenseManagerForWooCommerce\Repositories;

use WP_Query;

defined('ABSPATH') || exit;

class Posts
{
    /**
     * Adds all filters for interaction with the database table.
     */
    public function __construct()
    {
        // SELECT
        add_filter('lmfwc_get_products_dropdown', array($this, 'getProductsDropdown'), 10, 0);
    }

    /**
     * Retrieve assigned products for a specific generator.
     *
     * @return array
     */
    public function getProductsDropdown()
    {
        $products = array();

        $products_query = new WP_Query(
            array(
                'post_type'      => 'product',
                'posts_per_page' => -1
            )
        );

        foreach ($products_query->posts as $post) {
            if (!$product = wc_get_product($post->ID)) {
                continue;
            }

            if ($product->get_type() != 'variable') {
                $products[] = array(
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'parent_id' => null,
                    'parent_name' => null
                );

                continue;
            }

            if ($product->get_type() == 'variable') {
                $children = $product->get_children();

                foreach ($children as $variable_product_id) {
                    try {
                        $variation  = new \WC_Product_Variation($variable_product_id);

                        $products[] = array(
                            'id' => $variation->get_id(),
                            'name' => $variation->get_name(),
                            'parent_id' => $product->get_id(),
                            'parent_name' => $product->get_name()
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $products;
    }

}