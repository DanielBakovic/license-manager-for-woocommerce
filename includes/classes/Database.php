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
     * @param int   $args['order_id']   - Corresponding order ID.
     * @param int   $args['product_id'] - Corresponding product ID.
     * @param array $args['licenses']   - Return value of the 'LM_create_license_keys' filter.
     * @param array $args['expires_in'] - Number of days in which the license key expires.
     *
     * @return null
     */
    public static function saveLicenseKeys($args)
    {
        global $wpdb;

        $date       = new \DateTime();
        $created_at = $date->format('Y-m-d H:i:s');
        $expires_at = null;

        // Set the expiration date if specified
        if ($args['expires_in'] != null && is_numeric($args['expires_in'])) {
            $expires_at = $date->add(new \DateInterval('P' . $args['expires_in'] . 'D'))->format('Y-m-d H:i:s');
        }

        /**
         * @todo update with proper status handling
         */
        foreach ($args['licenses'] as $license_key) {
            $wpdb->insert(
                $wpdb->prefix . \LicenseManager\Classes\Setup::LICENSES_TABLE_NAME,
                array(
                    'order_id'    => $args['order_id'],
                    'product_id'  => $args['product_id'],
                    'license_key' => $license_key,
                    'created_at'  => $created_at,
                    'expires_at'  => $expires_at,
                    'status'      => 1
                ),
                array('%d', '%d', '%s', '%s', '%s', '%d')
            );
        }
    }
}