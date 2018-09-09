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
    private $crpyto;

    /**
     * Database Constructor.
     */
    public function __construct(
        \LicenseManager\Classes\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        add_action('lima_save_generated_licence_keys',   array($this, 'saveGeneratedLicenceKeys' ), 10, 1);
        add_filter('lima_save_imported_licence_keys',    array($this, 'saveImportedLicenseKeys'  ), 10, 1);
        add_filter('lima_save_added_licence_key',        array($this, 'saveAddedLicenseKey'      ), 10, 1);
        add_filter('lima_license_key_exists',            array($this, 'licenseKeyExists'         ), 10, 1);
        add_filter('lima_import_license_keys',           array($this, 'importLicenseKeys'        ), 10, 1);
    }

    /**
     * Save the license keys for a given product to the database.
     *
     * @since 1.0.0
     *
     * @todo Convert to filter, return array of added licenses.
     *
     * @param int    $args['order_id']     - Corresponding order ID.
     * @param int    $args['product_id']   - Corresponding product ID.
     * @param array  $args['licenses']     - Return value of the 'lima_create_license_keys' filter.
     * @param int    $args['expires_in']   - Number of days in which the license key expires.
     * @param string $args['charset']      - Character map from which the license will be generated.
     * @param int    $args['chunk_length'] - The length of an individual chunk.
     * @param int    $args['chunks']       - Number of chunks.
     * @param string $args['prefix']       - Prefix used.
     * @param string $args['separator']    - Separator used.
     * @param string $args['suffix']       - Suffix used.
     */
    public function saveGeneratedLicenceKeys($args)
    {
        global $wpdb;

        $date         = new \DateTime();
        $created_at   = $date->format('Y-m-d H:i:s');
        $expires_at   = null;
        $invalid_keys = 0;

        // Set the expiration date if specified.
        if ($args['expires_in'] != null && is_numeric($args['expires_in'])) {
            $expires_at = $date->add(new \DateInterval('P' . $args['expires_in'] . 'D'))->format('Y-m-d H:i:s');
        }

        /**
         * @todo Update with proper status handling.
         */
        // Add the keys to the database table.
        foreach ($args['licenses'] as $license_key) {
            // Key exists, up the invalid keys count.
            if (apply_filters('lima_license_key_exists', $license_key)) {
                $invalid_keys++;
            // Key doesn't exist, add it to the database table.
            } else {
                // Save to database.
                $wpdb->insert(
                    $wpdb->prefix . \LicenseManager\Classes\Setup::LICENSES_TABLE_NAME,
                    array(
                        'order_id'    => $args['order_id'],
                        'product_id'  => $args['product_id'],
                        'license_key' => $this->crypto->encrypt($license_key),
                        'hash'        => $this->crypto->hash($license_key),
                        'created_at'  => $created_at,
                        'expires_at'  => $expires_at,
                        'status'      => 1
                    ),
                    array('%d', '%d', '%s', '%s', '%s', '%s', '%d')
                );
            }
        }

        // There have been duplicate keys, regenerate and add them.
        if ($invalid_keys > 0) {
            $new_keys = apply_filters('lima_create_license_keys', array(
                'amount'       => $invalid_keys,
                'charset'      => $args['charset'],
                'chunks'       => $args['chunks'],
                'chunk_length' => $args['chunk_length'],
                'separator'    => $args['separator'],
                'prefix'       => $args['prefix'],
                'suffix'       => $args['suffix'],
                'expires_in'   => $args['expires_in']
            ));
            $this->saveGeneratedLicenceKeys(array(
                'order_id'     => $args['order_id'],
                'product_id'   => $args['product_id'],
                'licenses'     => $new_keys['licenses'],
                'expires_in'   => $args['expires_in'],
                'charset'      => $args['charset'],
                'chunk_length' => $args['chunk_length'],
                'chunks'       => $args['chunks'],
                'prefix'       => $args['prefix'],
                'separator'    => $args['separator'],
                'suffix'       => $args['suffix']
            ));
        } else {
            // Keys have been generated and saved, this order is now complete.
            //update_post_meta($args['order_id'], '_lima_order_status', 'complete');
        }
    }

    /**
     * Imports an array of un-encrypted licence keys into the database.
     *
     * @since 1.0.0
     *
     * @param array   $args['licence_keys']
     * @param boolean $args['activate'] 
     * @param int     $args['product_id']
     *
     * @return array
     */
    public function saveImportedLicenseKeys($args)
    {
        global $wpdb;

        $created_at = date('Y-m-d H:i:s');
        $result['added'] = $result['failed'] = 0;
        $args['activate'] ? $status = 3 : $status = 4;

        // Add the keys to the database table.
        foreach ($args['licence_keys'] as $licence_key) {
            if ($wpdb->insert(
                    $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
                    array(
                        'order_id'    => null,
                        'product_id'  => $args['product_id'],
                        'license_key' => $this->crypto->encrypt($licence_key),
                        'hash'        => $this->crypto->hash($licence_key),
                        'created_at'  => $created_at,
                        'expires_at'  => null,
                        'source'      => 2,
                        'status'      => $status
                    ),
                    array('%d', '%d', '%s', '%s', '%s', '%s', '%d')
                )
            ) {
                $result['added']++;
            } else {
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * Saves an un-encrypted licence keys into the database.
     *
     * @since 1.0.0
     *
     * @param string  $args['licence_key']
     * @param boolean $args['activate']
     * @param int     $args['product_id']
     *
     * @return array
     */
    public function saveAddedLicenseKey($args)
    {
        global $wpdb;

        $created_at = date('Y-m-d H:i:s');
        $args['activate'] ? $status = 3 : $status = 4;

        return $wpdb->insert(
            $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
            array(
                'order_id'    => null,
                'product_id'  => $args['product_id'],
                'license_key' => $this->crypto->encrypt($args['licence_key']),
                'hash'        => $this->crypto->hash($args['licence_key']),
                'created_at'  => $created_at,
                'expires_at'  => null,
                'source'      => 3,
                'status'      => $status
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%d')
        );
    }

    /**
     * Check if the license key already exists in the database.
     *
     * @since 1.0.0
     *
     * @param string $license_key - License key to be checked (plain text).
     *
     * @return boolean
     */
    public function licenseKeyExists($license_key)
    {
        global $wpdb;

        $table = $wpdb->prefix . \LicenseManager\Classes\Setup::LICENSES_TABLE_NAME;
        $sql   = "SELECT license_key FROM `{$table}` WHERE hash = '%s';";

        return $wpdb->get_var($wpdb->prepare($sql, $this->crypto->hash($license_key))) != null;
    }

    /**
     * Retrieves all license keys from the licenses table in the database.
     *
     * @since 1.0.0
     *
     * @param int $order_id - The order ID for which the keys will be retrieved.
     */
    public static function getLicenseKeys($order_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE order_id = %d",
                $order_id
            ),
            OBJECT
        );
    }

    public static function getGenerators()
    {
        global $wpdb;
        $table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

        return $wpdb->get_results("SELECT * FROM $table", OBJECT);
    }

    public static function getGenerator($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), OBJECT);
    }
}