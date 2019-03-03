<?php
/**
 * License repository
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce\Repositories;

use \LicenseManagerForWooCommerce\Setup;
use \LicenseManagerForWooCommerce\Enums\LicenseStatusEnum;
use \LicenseManagerForWooCommerce\Enums\SourceEnum;

defined('ABSPATH') || exit;

/**
 * License database connector.
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class License
{
    /**
     * Prefixed table name.
     * 
     * @var string
     */
    protected $table;

    /**
     * Adds all filters for interaction with the database table.
     * 
     * @return null
     */
    public function __construct()
    {
        global $wpdb;

        $this->table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        // SELECT
        add_filter('lmfwc_get_license_keys', array($this, 'getLicenseKeys'), 10);
        add_filter('lmfwc_get_license_key', array($this, 'getLicenseKey'), 10, 1);
        add_filter('lmfwc_get_available_stock', array($this, 'getAvailableStock'), 10, 1);

        // INSERT
        add_filter('lmfwc_insert_license_key', array($this, 'insertLicenseKey'), 10, 6);
        add_action('lmfwc_insert_generated_license_keys', array($this, 'insertGeneratedLicenseKeys'), 10, 1);
        add_filter('lmfwc_insert_imported_license_keys', array($this, 'insertImportedLicenseKeys'), 10, 3);

        // UPDATE
        add_filter('lmfwc_update_selective_license_key', array($this, 'updateSelectiveLicenseKey'), 10, 6);

        // DELETE
        add_filter('lmfwc_delete_license_keys', array($this, 'deleteLicenseKeys'), 10, 1);
    }

    /**
     * Returns all currently available license keys.
     * 
     * @since  1.1.0
     * @return array
     */
    public function getLicenseKeys()
    {
        global $wpdb;

        return $wpdb->get_results(
            "
                SELECT
                    `id`
                    , `order_id`
                    , `product_id`
                    , `license_key`
                    , `hash`
                    , `created_at`
                    , `expires_at`
                    , `valid_for`
                    , `source`
                    , `status`
                FROM
                    {$this->table}
                ;
            "
        );
    }

    /**
     * Returns a single License Key by its ID.
     * 
     * @param integer $id License Key ID
     * 
     * @since  1.1.0
     * @throws Exception
     * @return array
     */
    public function getLicenseKey($id)
    {
        $clean_id = $id ? absint($id) : null;

        if (!$clean_id) {
            throw new \Exception('License Key ID is missing', 1);
        }

        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "
                    SELECT
                        `id`
                        , `order_id`
                        , `product_id`
                        , `license_key`
                        , `hash`
                        , `created_at`
                        , `expires_at`
                        , `valid_for`
                        , `source`
                        , `status`
                    FROM
                        {$this->table}
                    WHERE
                        id = %d
                    ;
                ",
                $clean_id
            ), ARRAY_A
        );
    }

    /**
     * Retrieves the number of available license keys for a given product.
     *
     * @param int $product_id WooCommerce Product ID
     *
     * @since  1.0.0
     * @throws Exception
     * @return int
     */
    public function getAvailableStock($product_id)
    {
        $clean_product_id = $product_id ? absint($product_id) : null;

        if (!$clean_product_id) {
            throw new Exception('Product ID is invalid.', 1);
        }

        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT
                    COUNT(*)
                FROM
                    {$this->table}
                WHERE
                    1=1
                    AND product_id = %d
                    AND status = %d
                ",
                $clean_product_id,
                LicenseStatusEnum::ACTIVE
            )
        );
    }

    /**
     * Adds a single License Key.
     * 
     * @param integer $order_id    WooCommerce Order ID
     * @param integer $product_id  WooCommerce Product ID
     * @param string  $license_key License Key
     * @param integer $valid_for   Validity after purchase (in days)
     * @param integer $source      Source enumerator value
     * @param integer $status      Status enumerator value
     * 
     * @since  1.1.0
     * @throws Exception
     * @return integer
     */
    public function insertLicenseKey(
        $order_id,
        $product_id,
        $license_key,
        $valid_for,
        $source,
        $status
    ) {
        $clean_order_id    = $order_id    ? absint($order_id)                 : null;
        $clean_product_id  = $product_id  ? absint($product_id)               : null;
        $clean_license_key = $license_key ? sanitize_text_field($license_key) : null;
        $clean_valid_for   = $valid_for   ? absint($valid_for)                : null;
        $clean_source      = $source      ? absint($source)                   : null;
        $clean_status      = $status      ? absint($status)                   : null;

        if ($clean_order_id) {
            new \WC_Order($clean_order_id);
        }

        if ($clean_product_id) {
            new \WC_Product($clean_product_id);
        }

        if (!$clean_license_key) {
            throw new \Exception('License Key is missing', 1);
        }

        if (!$clean_source) {
            throw new \Exception('Source Enumerator is missing', 2);
        }

        if (!in_array($clean_source, SourceEnum::$sources)) {
            throw new \Exception('Source Enumerator is invalid', 3);
        }

        if (!$clean_status) {
            throw new \Exception('Status Enumerator is missing', 4);
        }

        if (!in_array($clean_status, LicenseStatusEnum::$statuses)) {
            throw new \Exception('Status Enumerator is invalid', 5);
        }

        $license_key_encrypted = apply_filters('lmfwc_encrypt', $clean_license_key);
        $license_key_hashed = apply_filters('lmfwc_hash', $clean_license_key);
        $created_at = gmdate('Y-m-d H:i:s');

        global $wpdb;

        $insert = $wpdb->insert(
            $this->table,
            array(
                'order_id'    => $clean_order_id,
                'product_id'  => $clean_product_id,
                'license_key' => $license_key_encrypted,
                'hash'        => $license_key_hashed,
                'created_at'  => $created_at,
                'expires_at'  => null,
                'valid_for'   => $clean_valid_for,
                'source'      => $clean_source,
                'status'      => $clean_status
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d')
        );

        if (!$insert) {
            return 0;
        }

        return $wpdb->insert_id;
    }

    /**
     * Save the license keys for a given product to the database.
     *
     * @since 1.0.0
     *
     * @param int    $args['order_id']
     * @param int    $args['product_id']
     * @param array  $args['licenses']
     * @param int    $args['expires_in']
     * @param string $args['charset']
     * @param int    $args['chunk_length']
     * @param int    $args['chunks']
     * @param string $args['prefix']
     * @param string $args['separator']
     * @param string $args['suffix']
     * @param int    $args['status']
     */
    public function insertGeneratedLicenseKeys($args)
    {
        global $wpdb;

        $date                = new \DateTime();
        $created_at          = $date->format('Y-m-d H:i:s');
        $expires_at          = null;
        $invalid_keys_amount = 0;

        // Set the expiration date if specified.
        if ($args['expires_in'] != null && is_numeric($args['expires_in'])) {
            $expires_at = $date->add(new \DateInterval('P' . $args['expires_in'] . 'D'))->format('Y-m-d H:i:s');
        }

        // Add the keys to the database table.
        foreach ($args['licenses'] as $license_key) {
            // Key exists, up the invalid keys count.
            if (apply_filters('lmfwc_license_key_exists', $license_key)) {
                $invalid_keys_amount++;
                // Key doesn't exist, add it to the database table.
            } else {
                // Save to database.
                $wpdb->insert(
                    $this->table,
                    array(
                        'order_id'    => $args['order_id'],
                        'product_id'  => $args['product_id'],
                        'license_key' => apply_filters('lmfwc_encrypt', $license_key),
                        'hash'        => apply_filters('lmfwc_hash', $license_key),
                        'created_at'  => $created_at,
                        'expires_at'  => $expires_at,
                        'source'      => SourceEnum::GENERATOR,
                        'status'      => $args['status']
                    ),
                    array('%d', '%d', '%s', '%s', '%s', '%s', '%d')
                );
            }
        }

        // There have been duplicate keys, regenerate and add them.
        if ($invalid_keys_amount > 0) {
            $new_keys = apply_filters(
                'lmfwc_create_license_keys', array(
                'amount'       => $invalid_keys_amount,
                'charset'      => $args['charset'],
                'chunks'       => $args['chunks'],
                'chunk_length' => $args['chunk_length'],
                'separator'    => $args['separator'],
                'prefix'       => $args['prefix'],
                'suffix'       => $args['suffix'],
                'expires_in'   => $args['expires_in']
                )
            );
            $this->insertGeneratedLicenseKeys(
                array(
                'order_id'     => $args['order_id'],
                'product_id'   => $args['product_id'],
                'licenses'     => $new_keys['licenses'],
                'expires_in'   => $args['expires_in'],
                'charset'      => $args['charset'],
                'chunk_length' => $args['chunk_length'],
                'chunks'       => $args['chunks'],
                'prefix'       => $args['prefix'],
                'separator'    => $args['separator'],
                'suffix'       => $args['suffix'],
                'status'       => $args['status']
                )
            );
        } else {
            // Keys have been generated and saved, this order is now complete.
            update_post_meta($args['order_id'], 'lmfwc_order_complete', 1);
        }
    }

    /**
     * Imports an array of un-encrypted license keys.
     *
     * @param array   $license_keys License Keys to be added
     * @param integer $status       License Status Enumerator value 
     * @param int     $product_id   WooCommerce Product ID
     *
     * @since  1.1.0
     * @throws Exception
     * @return array
     */
    public function insertImportedLicenseKeys($license_keys, $status, $product_id)
    {
        $clean_license_keys = array();
        $clean_status       = $status     ? absint($status)     : null;
        $clean_product_id   = $product_id ? absint($product_id) : null;
        $result             = array();

        if (!is_array($license_keys)) {
            throw new \Exception('License Keys must be an array', 1);
        }

        if (!$clean_status) {
            throw new \Exception('Status enumerator is missing', 2);
        }

        if (!in_array($clean_status, LicenseStatusEnum::$statuses)) {
            throw new \Exception('Status enumerator is invalid', 3);
        }

        if ($product_id) {
            new \WC_Product($clean_product_id);
        }

        foreach ($license_keys as $license_key) {
            array_push($clean_license_keys, sanitize_text_field($license_key));
        }

        global $wpdb;

        $created_at       = gmdate('Y-m-d H:i:s');
        $result['added']  = 0;
        $result['failed'] = 0;

        // Add the keys to the database table.
        foreach ($clean_license_keys as $license_key) {
            if ($wpdb->insert(
                    $this->table,
                    array(
                            'order_id'    => null,
                            'product_id'  => $clean_product_id,
                            'license_key' => apply_filters('lmfwc_encrypt', $license_key),
                            'hash'        => apply_filters('lmfwc_hash', $license_key),
                            'created_at'  => $created_at,
                            'expires_at'  => null,
                            'source'      => SourceEnum::IMPORT,
                            'status'      => $clean_status
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
     * Selectively update parts of an existing License Key.
     *
     * @param integer $id          The License Key ID
     * @param integer $order_id    The WooCommerce Order ID
     * @param integer $product_id  The WooCommerce Product ID
     * @param string  $license_key License Key
     * @param string  $valid_for   Validity in days
     * @param integer $status      Status enumerator
     *
     * @since  1.1.0
     * @throws Exception
     * @return array
     */
    public function updateSelectiveLicenseKey(
        $id,
        $order_id,
        $product_id,
        $license_key,
        $valid_for,
        $status
    ) {
        $clean_id          = $id          ? absint($id)                       : null;
        $clean_order_id    = $order_id    ? absint($order_id)                 : null;
        $clean_product_id  = $product_id  ? absint($product_id)               : null;
        $clean_license_key = $license_key ? sanitize_text_field($license_key) : null;
        $clean_valid_for   = $valid_for   ? sanitize_text_field($valid_for)   : null;
        $clean_status      = $status      ? absint($status)                   : null;

        if (!$id) {
            throw new \Exception('License Key ID is missing', 1);
        }

        if (!$clean_order_id
            && !$clean_product_id
            && !$clean_license_key
            && !$clean_valid_for
            && !$clean_status
        ) {
            throw new \Exception('No parameters provided', 2);
        }

        global $wpdb;

        $first = true;
        $sql = "UPDATE {$this->table}";

        if ($status) {
            $sql .= $wpdb->prepare(' SET status = %d', $status);
            $first = false;
        }

        if ($order_id) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('order_id = %d', $order_id);
            $first = false;
        }

        if ($product_id) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('product_id = %d', $product_id);
            $first = false;
        }

        if ($license_key) {
            $sql .= $first ? ' SET ' : ', ';
            $license_key_encrypted = apply_filters('lmfwc_encrypt', $license_key);
            $license_key_hashed = apply_filters('lmfwc_hash', $license_key);

            $sql .= $wpdb->prepare(
                'license_key = %s, hash = %s',
                $license_key_encrypted,
                $license_key_hashed
            );
            $first = false;
        }

        if ($valid_for) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('valid_for = %d', $valid_for);
            $first = false;
        }

        $sql .= $wpdb->prepare(' WHERE id = %d;', $id);

        $wpdb->query($sql);

        return $this->getLicense($id);
    }

    /**
     * Deletes License Key(s) by an array of IDs
     *
     * @param array $licenses Array of License Key ID's to be deleted
     *
     * @since  1.1.0
     * @throws Exception
     * @return boolean
     */
    public function deleteLicenseKeys($licenses)
    {
        $clean_ids = array();

        if (!is_array($licenses)) {
            throw new \Exception('Input parameter must be an array', 1);
        }

        foreach ($licenses as $id) {
            if (!absint($id)) {
                continue;
            }

            array_push($clean_ids, absint($id));
        }

        if (count($clean_ids) == 0) {
            throw new \Exception('No valid IDs given', 2);
        }

        global $wpdb;

        return $wpdb->query(
            sprintf(
                'DELETE FROM %s WHERE id IN (%s)',
                $this->table,
                implode(', ', $clean_ids)
            )
        );
    }

}