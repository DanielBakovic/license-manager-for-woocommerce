<?php
/**
 * User repository
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce\Repositories;

defined('ABSPATH') || exit;

/**
 * Users database connector.
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.5>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.1.5
 */
class Users
{
    /**
     * Adds all filters for interaction with the database table.
     * 
     * @return null
     */
    public function __construct()
    {
        // SELECT
        add_filter('lmfwc_get_users', array($this, 'getUsers'), 10, 0);
    }

    /**
     * Retrieve assigned products for a specific generator.
     *
     * @param int $generator_id ID of the given generator
     *
     * @since  1.0.0
     * @return array
     */
    public function getUsers()
    {
        global $wpdb;

        return $wpdb->get_results( 
            "
                SELECT
                    ID
                    , user_login
                    , user_email
                FROM
                    {$wpdb->users}
            ",
            OBJECT
        );
    }

}