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

        add_action('LM_save_license_keys',  array($this, 'saveLicenseKeys'), 10, 1);
        add_filter('LM_license_key_exists', array($this, 'licenseKeyExists'), 10, 1);
        add_filter('LM_add_license_key',    array($this, 'addLicenseKey'), 10, 1);
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
     * @param array  $args['licenses']     - Return value of the 'LM_create_license_keys' filter.
     * @param int    $args['expires_in']   - Number of days in which the license key expires.
     * @param string $args['charset']      - Character map from which the license will be generated.
     * @param int    $args['chunk_length'] - The length of an individual chunk.
     * @param int    $args['chunks']       - Number of chunks.
     * @param string $args['prefix']       - Prefix used.
     * @param string $args['separator']    - Separator used.
     * @param string $args['suffix']       - Suffix used.
     */
    public function saveLicenseKeys($args)
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
            // Kex exists, up the invalid keys count.
            if (apply_filters('LM_license_key_exists', $license_key)) {
                $invalid_keys++;
            // Key doesn't exist, add it to the database table.
            } else {
                // Check if the keys should be encrypted before saving.
                if (get_option('_lima_encrypt_license_keys')) {
                    $license_key = $this->crypto->encrypt($license_key);
                }

                // Save to database.
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

        // There have been duplicate keys, regenerate and add them.
        if ($invalid_keys > 0) {
            $new_keys = apply_filters('LM_create_license_keys', array(
                'amount'       => $invalid_keys,
                'charset'      => $args['charset'],
                'chunks'       => $args['chunks'],
                'chunk_length' => $args['chunk_length'],
                'separator'    => $args['separator'],
                'prefix'       => $args['prefix'],
                'suffix'       => $args['suffix'],
                'expires_in'   => $args['expires_in']
            ));
            $this->saveLicenseKeys(array(
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
            update_post_meta($args['order_id'], '_lima_order_status', 'complete');
        }
    }

    /**
     * Check if the license key already exists in the database.
     *
     * @since 1.0.0
     *
     * @param string $license_key - License key to be checked.
     *
     * @return boolean
     */
    public function licenseKeyExists($license_key)
    {
        global $wpdb;

        $table = $wpdb->prefix . \LicenseManager\Classes\Setup::LICENSES_TABLE_NAME;
        $sql   = "SELECT license_key FROM `{$table}` WHERE license_key = '%s';";

        return $wpdb->get_var($wpdb->prepare($sql, $license_key)) != null;
    }

    /**
     * Check if the license key already exists in the database. Returns the license key that was added. If the function
     * looped for more than 20 times (for whatever reason), it will return boolean false.
     *
     * @since 1.0.0
     *
     * @param int    $args['order_id']     - Corresponding order ID.
     * @param int    $args['product_id']   - Corresponding product ID.
     * @param int    $args['expires_in']   - Number of days in which the license key expires.
     * @param string $args['charset']      - Character map from which the license will be generated.
     * @param int    $args['chunk_length'] - The length of an individual chunk.
     * @param int    $args['chunks']       - Number of chunks.
     * @param string $args['prefix']       - Prefix used.
     * @param string $args['separator']    - Separator used.
     * @param string $args['suffix']       - Suffix used.
     * 
     * @return boolean|string
     */
    public function addLicenseKey($args)
    {
        // Used to prevent the function from looping indefinitely.
        static $counter = 0;

        // Generate the license string.
        $license_key = apply_filters('LM_generate_license_string', $args);
        $license_key = '00000-AAAAA';

        // Returns false and logs an exception if the function has looped for more than 20 times.
        if ($counter >= 20) {
            return 'if';

            $e = new \Exception(
                __(
                    'Too many failed attempts at generating a license key. Parameters are included in the exception log.',
                    'lima'
                ),
                2
            );

            Logger::exception($e);
            Logger::exception($args);

            return false;
        // Recursively call this method to generate a new license key with the the same arguments. Also up the counter.
        } else if (apply_filters('LM_license_key_exists', $license_key)) {
            $counter++;
            $this->addLicenseKey($args);
        // License key is valid, save it in the database table.
        } else {
            return 'else';
            return $license_key;
        }

        return false;
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