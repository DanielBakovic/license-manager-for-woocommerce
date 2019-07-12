<?php

namespace LicenseManagerForWooCommerce\Repositories;

use LicenseManagerForWooCommerce\Exception as LMFWC_Exception;

defined('ABSPATH') || exit;

class PostMeta
{
    /**
     * Adds all filters for interaction with the database table.
     */
    public function __construct()
    {
        // SELECT
        add_filter('lmfwc_get_assigned_products', array($this, 'getAssignedProducts'), 10, 1);
    }

    /**
     * Retrieve assigned products for a specific generator.
     *
     * @param int $generator_id
     *
     * @return array
     * @throws LMFWC_Exception
     */
    public function getAssignedProducts($generator_id)
    {
        $clean_generator_id = $generator_id ? absint($generator_id) : null;

        if (!$clean_generator_id) {
            throw new LMFWC_Exception('Generator ID is invalid.');
        }

        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "
                    SELECT
                        post_id
                    FROM
                        {$wpdb->postmeta}
                    WHERE
                        1 = 1
                        AND meta_key = %s
                        AND meta_value = %d
                ",
                'lmfwc_licensed_product_assigned_generator',
                $clean_generator_id
            ),
            OBJECT
        );

        if ($results) {
            $products = [];

            foreach ($results as $row) {
                $products[] = wc_get_product($row->post_id);
            }
        } else {
            $products = [];
        }

        return $products;
    }

}