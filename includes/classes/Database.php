<?php

namespace LicenseManager\Classes;

/**
 * LicenseManager Database.
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Database class.
 */
class Database
{
    /**
     * Database Constructor.
     */
    public function __construct()
    {
        add_action('LM_save_license_keys', array($this, 'saveLicenseKeys'), 10, 1);
    }

    /**
     * Save the license keys for a given product to the database.
     *
     * @since 1.0.0
     *
     * @param int   $args['order_id']   - Corresponding order ID
     * @param int   $args['product_id'] - Corresponding product ID
     * @param array $args['licenses']   - Return value of the 'LM_create_license_keys' filter
     *
     * @return null
     */
    public static function saveLicenseKeys($args)
    {
        global $wpdb;

        Logger::file(date('Y-m-d H:i:s'));

        /**
         * @todo update with proper status handling
         */
        foreach ($args['licenses'] as $license_key) {
            $wpdb->insert(
                $wpdb->prefix . \LicenseManager\Classes\Setup::LICENSES_TABLE_NAME,
                array(
                    'product_id'  => $args['product_id'],
                    'license_key' => $license_key,
                    'order_id'    => $args['order_id'],
                    'created'     => date('Y-m-d H:i:s'),
                    'status'      => 1
                ),
                array('%d', '%d', '%s', '%d', '%s', '%d')
            );
        }
    }
}