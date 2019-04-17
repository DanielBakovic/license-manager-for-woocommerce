<?php
/**
 * Post Meta repository
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce\Repositories;

use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;

defined('ABSPATH') || exit;

/**
 * Post Meta database connector.
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class PostMeta
{
    /**
     * Adds all filters for interaction with the database table.
     * 
     * @return null
     */
    public function __construct()
    {
        // SELECT
        add_filter('lmfwc_get_assigned_products', array($this, 'getAssignedProducts'), 10, 1);
    }

    /**
     * Retrieve assigned products for a specific generator.
     *
     * @param int $generator_id ID of the given generator
     *
     * @since  1.0.0
     * @return array
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