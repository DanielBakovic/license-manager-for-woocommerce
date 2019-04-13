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
use \LicenseManagerForWooCommerce\Logger;
use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;
use \LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;
use \LicenseManagerForWooCommerce\Enums\LicenseSource as LicenseSourceEnum;

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
    const UNDEFINED = -1;

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
        add_filter(
            'lmfwc_get_license_keys',
            array($this, 'getLicenseKeys'),
            10
        );
        add_filter(
            'lmfwc_get_license_key',
            array($this, 'getLicenseKey'),
            10,
            1
        );
        add_filter(
            'lmfwc_get_license_key_count',
            array($this, 'getLicenseKeyCount'),
            10,
            1
        );
        add_filter(
            'lmfwc_get_license_key_info',
            array($this, 'getLicenseKeyInfo'),
            10,
            1
        );
        add_filter(
            'lmfwc_get_available_stock',
            array($this, 'getAvailableStock'),
            10,
            1
        );
        add_filter(
            'lmfwc_get_order_license_keys',
            array($this, 'getOrderLicenseKeys'),
            10,
            2
        );
        add_filter(
            'lmfwc_get_product_license_keys',
            array($this, 'getProductLicenseKeys'),
            10,
            2
        );
        add_filter(
            'lmfwc_license_key_exists',
            array($this, 'licenseKeyExists'),
            10,
            1
        );

        // INSERT
        add_filter(
            'lmfwc_insert_license_key',
            array($this, 'insertLicenseKey'),
            10,
            8
        );
        add_filter(
            'lmfwc_insert_generated_license_keys',
            array($this, 'insertGeneratedLicenseKeys'),
            10,
            7
        );
        add_filter(
            'lmfwc_insert_imported_license_keys',
            array($this, 'insertImportedLicenseKeys'),
            10,
            6
        );

        // UPDATE
        add_filter(
            'lmfwc_update_license_key',
            array($this, 'updateLicenseKey'),
            10,
            8
        );
        add_filter(
            'lmfwc_update_license_key_status',
            array($this, 'updateLicenseKeyStatus'),
            10,
            2
        );
        add_filter(
            'lmfwc_update_selective_license_key',
            array($this, 'updateSelectiveLicenseKey'),
            10,
            7
        );
        add_action(
            'lmfwc_sell_imported_license_keys',
            array($this, 'sellImportedLicenseKeys'),
            10,
            3
        );
        add_filter(
            'lmfwc_toggle_license_key_status',
            array($this, 'toggleLicenseKeyStatus'),
            10,
            4
        );
        add_filter(
            'lmfwc_activate_license_key',
            array($this, 'activateLicenseKey'),
            10,
            2
        );

        // DELETE
        add_filter(
            'lmfwc_delete_license_keys',
            array($this, 'deleteLicenseKeys'),
            10,
            1
        );
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
                    , `expires_at`
                    , `valid_for`
                    , `source`
                    , `status`
                    , `times_activated`
                    , `times_activated_max`
                    , `created_at`
                    , `created_by`
                    , `updated_at`
                    , `updated_by`
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
            throw new LMFWC_Exception('Invalid License Key ID');
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
                        , `expires_at`
                        , `valid_for`
                        , `source`
                        , `status`
                        , `times_activated`
                        , `times_activated_max`
                        , `created_at`
                        , `created_by`
                        , `updated_at`
                        , `updated_by`
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
     * Returns the number of license keys available.
     *
     * @param Enums\LicenseStatus $status The new license key status
     *
     * @since  1.1.0
     * @throws Exception
     * @return integer
     */
    public function getLicenseKeyCount($status = null)
    {
        global $wpdb;

        $clean_status = $status ? absint($status) : null;

        if ($clean_status && !in_array($clean_status, LicenseStatusEnum::$status)) {
            throw new LMFWC_Exception('License Status is invalid');
        }

        if (!$clean_status) {
            return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
        } else {
            return $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE status = %d",
                    $clean_status
                )
            );
        }
    }

    /**
     * Retrieves additional entries of the given license key
     * 
     * @param string $license_key Unencrypted license key
     * 
     * @since  1.2.0
     * @throws Exception
     * @return array
     */
    public function getLicenseKeyInfo($license_key)
    {
        $clean_license_key = $license_key ? sanitize_text_field($license_key) : null;

        if (!$clean_license_key) {
            throw new LMFWC_Exception('Invalid License Key');
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
                        , `expires_at`
                        , `valid_for`
                        , `source`
                        , `status`
                        , `times_activated`
                        , `times_activated_max`
                        , `created_at`
                        , `created_by`
                        , `updated_at`
                        , `updated_by`
                    FROM
                        {$this->table}
                    WHERE
                        hash = %s
                    ;
                ",
                apply_filters('lmfwc_hash', $clean_license_key)
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
     * @return integer
     */
    public function getAvailableStock($product_id)
    {
        $clean_product_id = $product_id ? absint($product_id) : null;

        if (!$clean_product_id) {
            throw new LMFWC_Exception('Product ID is invalid.');
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
     * Retrieves all license keys related to a order/product combination.
     *
     * @param int $order_id   WooCommerce Order ID
     * @param int $product_id WooCOmmerce Product ID
     *
     * @since  1.0.0
     * @throws Exception
     * @return array
     */
    public function getOrderLicenseKeys($order_id, $product_id)
    {
        $clean_order_id   = $order_id   ? absint($order_id)   : null;
        $clean_product_id = $product_id ? absint($product_id) : null;

        if (!$clean_order_id) {
            throw new LMFWC_Exception('Order ID is invalid');
        }

        if (!$clean_product_id) {
            throw new LMFWC_Exception('Product ID is invalid');
        }

        if ($clean_order_id) {
            new \WC_Order($clean_order_id);
        }

        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "
                    SELECT
                        `id`
                        , `order_id`
                        , `product_id`
                        , `license_key`
                        , `hash`
                        , `expires_at`
                        , `valid_for`
                        , `source`
                        , `status`
                        , `times_activated`
                        , `times_activated_max`
                        , `created_at`
                        , `created_by`
                        , `updated_at`
                        , `updated_by`
                    FROM
                        {$this->table}
                    WHERE
                        1=1
                        AND order_id = %d
                        AND product_id = %d
                ;",
                $clean_order_id,
                $clean_product_id
            ),
            OBJECT
        );
    }

    /**
     * Retrieves all license keys related to a specific product.
     *
     * @since 1.0.0
     *
     * @param int $product_id
     * @param int $status
     *
     * @return array
     */
    public function getProductLicenseKeys($product_id, $status)
    {
        $clean_product_id = $product_id ? absint($product_id) : null;
        $clean_status     = $status     ? absint($status)     : null;

        if (!$clean_product_id) {
            throw new LMFWC_Exception('Product ID is invalid');
        }

        if (!$clean_status) {
            throw new LMFWC_Exception('Status is invalid');
        }

        if (!in_array($clean_status, LicenseStatusEnum::$status)) {
            throw new LMFWC_Exception('Status is invalid');
        }

        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "
                    SELECT
                        `id`
                        , `order_id`
                        , `product_id`
                        , `license_key`
                        , `hash`
                        , `expires_at`
                        , `valid_for`
                        , `source`
                        , `status`
                        , `times_activated`
                        , `times_activated_max`
                        , `created_at`
                        , `created_by`
                        , `updated_at`
                        , `updated_by`
                    FROM
                        {$this->table}
                    WHERE
                        1=1
                        AND product_id = %d
                        AND status = %d
                    ;
                ",
                $clean_product_id,
                $clean_status
            ),
            OBJECT
        );
    }

    /**
     * Check if the license key already exists in the database.
     *
     * @param string $license_key Unencrypted License Key
     *
     * @since  1.1.0
     * @throws Exception
     * @return boolean
     */
    public function licenseKeyExists($license_key)
    {
        $clean_license_key = $license_key ? sanitize_text_field($license_key) : null;

        if (!$clean_license_key) {
            throw new LMFWC_Exception('License Key is invalid.');
        }

        $hashed_license_key = apply_filters('lmfwc_hash', $clean_license_key);

        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "
                    SELECT
                        license_key
                    FROM
                        `{$this->table}`
                    WHERE
                        hash = '%s'
                    ;
                ",
                $hashed_license_key
            )
        );

        return $result != null;
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
     * @param integer $created_by  WordPress User ID
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
        $status,
        $times_activated_max,
        $created_by
    ) {
        $clean_order_id            = $order_id            ? absint($order_id)                 : null;
        $clean_product_id          = $product_id          ? absint($product_id)               : null;
        $clean_license_key         = $license_key         ? sanitize_text_field($license_key) : null;
        $clean_valid_for           = $valid_for           ? absint($valid_for)                : null;
        $clean_source              = $source              ? absint($source)                   : null;
        $clean_status              = $status              ? absint($status)                   : null;
        $clean_times_activated_max = $times_activated_max ? absint($times_activated_max)      : null;
        $clean_created_by          = $created_by          ? absint($created_by)               : null;

        if ($clean_order_id) {
            new \WC_Order($clean_order_id);
        }

        if (!$clean_license_key || strlen($clean_license_key) == 0) {
            throw new LMFWC_Exception('Invalid License Key');
        }

        if (!$clean_source) {
            throw new LMFWC_Exception('Source Enumerator is missing');
        }

        if (!in_array($clean_source, LicenseSourceEnum::$sources)) {
            throw new LMFWC_Exception('Source Enumerator is invalid');
        }

        if (!$clean_status) {
            throw new LMFWC_Exception('Status Enumerator is missing');
        }

        if (!in_array($clean_status, LicenseStatusEnum::$status)) {
            throw new LMFWC_Exception('Status Enumerator is invalid');
        }

        if (!$clean_created_by || !get_userdata($clean_created_by)) {
            throw new LMFWC_Exception('Created by User ID is invalid');
        }

        $license_key_encrypted = apply_filters('lmfwc_encrypt', $clean_license_key);
        $license_key_hashed = apply_filters('lmfwc_hash', $clean_license_key);
        $created_at = gmdate('Y-m-d H:i:s');

        global $wpdb;

        $insert = $wpdb->insert(
            $this->table,
            array(
                'order_id'            => $clean_order_id,
                'product_id'          => $clean_product_id,
                'license_key'         => $license_key_encrypted,
                'hash'                => $license_key_hashed,
                'expires_at'          => null,
                'valid_for'           => $clean_valid_for,
                'source'              => $clean_source,
                'status'              => $clean_status,
                'times_activated_max' => $clean_times_activated_max,
                'created_at'          => $created_at,
                'created_by'          => $clean_created_by
            ),
            array('%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d')
        );

        if (!$insert) {
            return 0;
        }

        return $wpdb->insert_id;
    }

    /**
     * Save the license keys for a given product to the database.
     *
     * @param integer   $order_id     WooCoomerce Order ID
     * @param integer   $product_id   WooCommerce Product ID
     * @param array     $license_keys License Keys to be added
     * @param integer   $expires_in   Validity period after purchase (in days)
     * @param integer   $status       Status enumerator value
     * @param stdObject $generator    Generator to use to create new keys
     *
     * @since  1.1.0
     * @throws Exception
     * @return integer
     */
    public function insertGeneratedLicenseKeys(
        $order_id,
        $product_id,
        $license_keys,
        $expires_in,
        $status,
        $created_by,
        $generator
    ) {
        $clean_order_id     = $order_id     ? absint($order_id)     : null;
        $clean_product_id   = $product_id   ? absint($product_id)   : null;
        $clean_license_keys = array();
        $clean_expires_in   = $expires_in   ? absint($expires_in)   : null;
        $clean_status       = $status       ? absint($status)       : null;
        $clean_created_by   = $created_by   ? absint($created_by)   : null;

        if (!$clean_status
            || !in_array($clean_status, LicenseStatusEnum::$status)
        ) {
            throw new LMFWC_Exception('License Status is invalid.');
        }

        if (!is_array($license_keys)) {
            throw new LMFWC_Exception('License Keys must be provided as array');
        }

        foreach ($license_keys as $license_key) {
            array_push($clean_license_keys, sanitize_text_field($license_key));
        }

        if (count($clean_license_keys) === 0) {
            throw new LMFWC_Exception('No License Keys were provided');
        }

        if (!$clean_created_by || !get_userdata($clean_created_by)) {
            throw new LMFWC_Exception('Created by User ID is invalid');
        }

        global $wpdb;

        $gm_date = new \DateTime('now', new \DateTimeZone('GMT'));
        $created_at = $gm_date->format('Y-m-d H:i:s');
        $invalid_keys_amount = 0;

        if ($clean_expires_in && $status == LicenseStatusEnum::SOLD) {
            $date_interval = 'P' . $clean_expires_in . 'D';
            $date_expires_at = new \DateInterval($date_interval);
            $expires_at = $gm_date->add($date_expires_at)->format('Y-m-d H:i:s');
        } else {
            $expires_at = null;
        }

        // Add the keys to the database table.
        foreach ($clean_license_keys as $license_key) {
            // Key exists, up the invalid keys count.
            if (apply_filters('lmfwc_license_key_exists', $license_key)) {
                $invalid_keys_amount++;
                continue;
            }

            // Key doesn't exist, add it to the database table.
            $encrypted_license_key = apply_filters('lmfwc_encrypt', $license_key);
            $hashed_license_key = apply_filters('lmfwc_hash', $license_key);

            // Save to database.
            $wpdb->insert(
                $this->table,
                array(
                    'order_id'            => $clean_order_id,
                    'product_id'          => $clean_product_id,
                    'license_key'         => $encrypted_license_key,
                    'hash'                => $hashed_license_key,
                    'expires_at'          => $expires_at,
                    'valid_for'           => $clean_expires_in,
                    'source'              => LicenseSourceEnum::GENERATOR,
                    'status'              => $clean_status,
                    'times_activated_max' => $generator->times_activated_max,
                    'created_at'          => gmdate('Y-m-d H:i:s'),
                    'created_by'          => $clean_created_by
                ),
                array('%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d')
            );
        }

        // There have been duplicate keys, regenerate and add them.
        if ($invalid_keys_amount > 0) {
            $new_keys = apply_filters(
                'lmfwc_create_license_keys', array(
                'amount'       => $invalid_keys_amount,
                'charset'      => $generator->charset,
                'chunks'       => $generator->chunks,
                'chunk_length' => $generator->chunk_length,
                'separator'    => $generator->separator,
                'prefix'       => $generator->prefix,
                'suffix'       => $generator->suffix,
                'expires_in'   => $clean_expires_in
                )
            );
            $this->insertGeneratedLicenseKeys(
                $clean_order_id,
                $clean_product_id,
                $new_keys['licenses'],
                $clean_expires_in,
                $clean_status,
                $generator
            );
        } else {
            // Keys have been generated and saved, this order is now complete.
            update_post_meta($clean_order_id, 'lmfwc_order_complete', 1);
        }
    }

    /**
     * Imports an array of un-encrypted license keys.
     *
     * @param array   $license_keys        License Keys to be added
     * @param integer $status              License Status Enumerator value 
     * @param integer $product_id          WooCommerce Product ID
     * @param integer $valid_for           Validity after purchase (in days)
     * @param integer $times_activated_max Maximum activation count
     * @param integer $created_by          WordPress User ID
     *
     * @since  1.1.0
     * @throws Exception
     * @return array
     */
    public function insertImportedLicenseKeys(
        $license_keys,
        $status,
        $product_id,
        $valid_for,
        $times_activated_max,
        $created_by
    ) {
        $clean_license_keys        = array();
        $clean_status              = $status              ? absint($status)              : null;
        $clean_product_id          = $product_id          ? absint($product_id)          : null;
        $clean_valid_for           = $valid_for           ? absint($valid_for)           : null;
        $clean_times_activated_max = $times_activated_max ? absint($times_activated_max) : null;
        $clean_created_by          = $created_by          ? absint($created_by)          : null;
        $result                    = array();

        if (!is_array($license_keys)) {
            throw new LMFWC_Exception('License Keys must be an array');
        }

        if (!$clean_status) {
            throw new LMFWC_Exception('Status enumerator is missing');
        }

        if (!in_array($clean_status, LicenseStatusEnum::$status)) {
            throw new LMFWC_Exception('Status enumerator is invalid');
        }

        if (!$clean_created_by || !get_userdata($clean_created_by)) {
            throw new LMFWC_Exception('Created by User ID is invalid');
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
            $insert = $wpdb->insert(
                $this->table,
                array(
                    'order_id'            => null,
                    'product_id'          => $clean_product_id,
                    'license_key'         => apply_filters('lmfwc_encrypt', $license_key),
                    'hash'                => apply_filters('lmfwc_hash', $license_key),
                    'expires_at'          => null,
                    'valid_for'           => $clean_valid_for,
                    'source'              => LicenseSourceEnum::IMPORT,
                    'status'              => $clean_status,
                    'times_activated_max' => $clean_times_activated_max,
                    'created_at'          => $created_at,
                    'created_by'          => $clean_created_by
                ),
                array('%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d')
            );

            if ($insert) {
                $result['added']++;
            } else {
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * Updates the license key is a whole. All parameters are required.
     * 
     * @param integer $id                  ID of the edited License Key
     * @param integer $product_id          WooCommerce Product ID
     * @param string  $license_key         Decrypted License Key
     * @param integer $valid_for           Validity in days
     * @param integer $source              Source enumerator
     * @param integer $status              Status enumerator
     * @param integer $times_activated_max Maximum activation count
     * @param integer $updated_by          WordPress User ID
     *
     * @since  1.1.0
     * @throws Exception
     * @return integer
     */
    public function updateLicenseKey(
        $id,
        $product_id,
        $license_key,
        $valid_for,
        $source,
        $status,
        $times_activated_max,
        $updated_by
    ) {
        $clean_id                  = $id                  ? absint($id)                       : null;
        $clean_product_id          = $product_id          ? absint($product_id)               : null;
        $clean_license_key         = $license_key         ? sanitize_text_field($license_key) : null;
        $clean_valid_for           = $valid_for           ? absint($valid_for)                : null;
        $clean_source              = $source              ? absint($source)                   : null;
        $clean_status              = $status              ? absint($status)                   : null;
        $clean_times_activated_max = $times_activated_max ? absint($times_activated_max)      : null;
        $clean_updated_by          = $updated_by          ? absint($updated_by)               : null;

        if (!$clean_id) {
            throw new LMFWC_Exception('Invalid License Key ID');
        }

        if (!$clean_license_key || strlen($clean_license_key) == 0) {
            throw new LMFWC_Exception('Invalid License Key');
        }

        if (!in_array($clean_source, LicenseSourceEnum::$sources)) {
            throw new LMFWC_Exception('Source Enumerator is invalid');
        }

        if (!in_array($clean_status, LicenseStatusEnum::$status)) {
            throw new LMFWC_Exception('Status Enumerator is invalid');
        }

        if (!$clean_updated_by || !get_userdata($clean_updated_by)) {
            throw new LMFWC_Exception('Updated by User ID is invalid');
        }

        global $wpdb;

        $encrypted_license_key = apply_filters('lmfwc_encrypt', $clean_license_key);
        $hashed_license_key = apply_filters('lmfwc_hash', $clean_license_key);

        return $wpdb->update(
            $this->table,
            array(
                'product_id'          => $clean_product_id,
                'license_key'         => $encrypted_license_key,
                'hash'                => $hashed_license_key,
                'valid_for'           => $clean_valid_for,
                'source'              => $clean_source,
                'status'              => $clean_status,
                'times_activated_max' => $clean_times_activated_max,
                'updated_at'          => gmdate('Y-m-d H:i:s'),
                'updated_by'          => $clean_updated_by
            ),
            array('id' => $clean_id),
            array('%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d'),
            array('%d')
        );
    }

    /**
     * Updates only the status of a single license key.
     * 
     * @param integer $id     License Key ID
     * @param integer $status Status enumerator
     * 
     * @since  1.1.0
     * @throws Exception
     * @return integer
     */
    public function updateLicenseKeyStatus($id, $status)
    {
        $clean_id     = $id     ? absint($id)     : null;
        $clean_status = $status ? absint($status) : null;

        if (!$clean_id) {
            throw new LMFWC_Exception('Invalid License Key ID');
        }

        if (!in_array($clean_status, LicenseStatusEnum::$status)) {
            throw new LMFWC_Exception('Status Enumerator is invalid');
        }

        global $wpdb;

        return $wpdb->update(
            $this->table,
            array('status' => $clean_status),
            array('id' => $clean_id),
            array('%d'),
            array('%d')
        );
    }

    /**
     * Selectively update parts of an existing License Key. Not all parameters are
     * required.
     *
     * @param integer $id          The License Key ID
     * @param integer $order_id    The WooCommerce Order ID
     * @param integer $product_id  The WooCommerce Product ID
     * @param string  $license_key License Key
     * @param integer $valid_for   Validity in days
     * @param integer $status      Status enumerator
     * @param integer $updated_by  WordPress User ID
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
        $status,
        $updated_by
    ) {
        $clean_updated_by = $updated_by ? absint($updated_by) : null;

        if (!$clean_updated_by || !get_userdata($clean_updated_by)) {
            throw new LMFWC_Exception('Updated by User ID is invalid');
        }

        if ($id && $id != self::UNDEFINED) {
            $clean_id = absint($id);
        } elseif (is_null($id)) {
            $clean_id = null;
        } elseif ($id == self::UNDEFINED) {
            $clean_id = self::UNDEFINED;
        }

        if ($order_id && $order_id != self::UNDEFINED) {
            $clean_order_id = absint($order_id);
        } elseif (is_null($order_id)) {
            $clean_order_id = null;
        } elseif ($order_id == self::UNDEFINED) {
            $clean_order_id = self::UNDEFINED;
        }

        if ($product_id && $product_id != self::UNDEFINED) {
            $clean_product_id = absint($product_id);
        } elseif (is_null($product_id)) {
            $clean_product_id = null;
        } elseif ($product_id == self::UNDEFINED) {
            $clean_product_id = self::UNDEFINED;
        }

        if ($license_key && $license_key != self::UNDEFINED) {
            $clean_license_key = sanitize_text_field($license_key);
        } elseif (is_null($license_key)) {
            $clean_license_key = null;
        } elseif ($license_key == self::UNDEFINED) {
            $clean_license_key = self::UNDEFINED;
        }

        if ($valid_for && $valid_for != self::UNDEFINED) {
            $clean_valid_for = absint($valid_for);
        } elseif (is_null($valid_for)) {
            $clean_valid_for = null;
        } elseif ($valid_for == self::UNDEFINED) {
            $clean_valid_for = self::UNDEFINED;
        }

        if ($status && $status != self::UNDEFINED) {
            $clean_status = absint($status);
        } elseif (is_null($status)) {
            $clean_status = null;
        } elseif ($status == self::UNDEFINED) {
            $clean_status = self::UNDEFINED;
        }

        if (!$clean_id) {
            throw new LMFWC_Exception('Invalid License Key ID');
        }

        if (!$clean_license_key) {
            throw new LMFWC_Exception('Invalid License Key');
        }

        if ($order_id == self::UNDEFINED
            && $product_id == self::UNDEFINED
            && $license_key == self::UNDEFINED
            && $valid_for == self::UNDEFINED
            && $status == self::UNDEFINED
        ) {
            throw new LMFWC_Exception('No parameters provided');
        }

        global $wpdb;

        $sql = $wpdb->prepare(
            "
                UPDATE
                    {$this->table}
                SET
                    `updated_at` = %s,
                    `updated_by` = %d
            ",
            gmdate('Y-m-d H:i:s'),
            $clean_updated_by
        );

        // Order ID
        if ($clean_order_id != self::UNDEFINED) {
            if (is_null($clean_order_id)) {
                $sql .= ', `order_id` = NULL';
            } else {
                $sql .= $wpdb->prepare(', `order_id` = %d', $clean_order_id);
            }
        }

        // Product ID
        if ($clean_product_id != self::UNDEFINED) {
            if (is_null($clean_product_id)) {
                $sql .= ', `product_id` = NULL';
            } else {
                $sql .= $wpdb->prepare('`product_id` = %d', $clean_product_id);
            }
        }

        // License Key
        if ($clean_license_key != self::UNDEFINED) {
            $license_key_encrypted = apply_filters('lmfwc_encrypt', $clean_license_key);
            $license_key_hashed = apply_filters('lmfwc_hash', $clean_license_key);

            $sql .= $wpdb->prepare(
                ', `license_key` = %s, `hash` = %s',
                $license_key_encrypted,
                $license_key_hashed
            );
        }

        // Valid for
        if ($clean_valid_for != self::UNDEFINED) {
            if (is_null($clean_valid_for)) {
                $sql .= ', `valid_for` = NULL';
            } else {
                $sql .= $wpdb->prepare(', `valid_for` = %d', $clean_valid_for);
            }
        }

        // Status
        if ($clean_status != self::UNDEFINED) {
            if (is_null($clean_status)) {
                $sql .= ', `status` = NULL';
            } else {
                $sql .= $wpdb->prepare(', `status` = %d', $clean_status);
            }
        }

        $sql .= $wpdb->prepare(' WHERE id = %d;', $id);

        $wpdb->query($sql);

        return $this->getLicenseKey($id);
    }

    /**
     * Mark the imported license keys as sold.
     *
     * @param array   $license_keys License Keys
     * @param integer $order_id     WooCommerce Order ID
     * @param integer $amount       License Key amount
     *
     * @since  1.1.0
     * @throws Exception
     * @return integer
     */
    public function sellImportedLicenseKeys($license_keys, $order_id, $amount)
    {
        $clean_license_keys = $license_keys;
        $clean_order_id     = $order_id ? absint($order_id) : null;
        $clean_amount       = $amount   ? absint($amount)   : null;

        if (!is_array($license_keys) || count($license_keys) <= 0) {
            throw new LMFWC_Exception('License Keys are invalid.');
        }

        if (!$clean_order_id) {
            throw new LMFWC_Exception('Order ID is invalid.');
        }

        if (!$clean_order_id) {
            throw new LMFWC_Exception('Amount is invalid.');
        }

        global $wpdb;

        for ($i = 0; $i < $clean_amount; $i++) {
            $date       = new \DateTime();
            $valid_for  = intval($clean_license_keys[$i]->valid_for);
            $expires_at = null;

            if (is_numeric($valid_for)) {
                $date_interval = new \DateInterval('P' . $valid_for . 'D');
                $expires_at = $date->add($date_interval)->format('Y-m-d H:i:s');
            }

            $wpdb->update(
                $this->table,
                array(
                    'order_id'   => $clean_order_id,
                    'expires_at' => $expires_at,
                    'status'     => LicenseStatusEnum::SOLD
                ),
                array('id' => $clean_license_keys[$i]->id),
                array('%d', '%s', '%d'),
                array('%d')
            );
        }
    }

    /**
     * Toggles the license key activation status
     *
     * @param string  $column_name The column name by which to compare
     * @param string  $operator    The operator to use
     * @param mixed   $value       Value of the column by which to compare
     * @param integer $status      New license key status
     *
     * @since  1.1.0
     * @throws Exception
     * @return boolean
     */
    public function toggleLicenseKeyStatus($column_name, $operator, $value, $status)
    {
        $clean_column_name = $column_name ? sanitize_text_field($column_name) : null;
        $clean_operator    = $operator    ? sanitize_text_field($operator)    : null;
        $clean_status      = $status      ? absint($status)                   : null;

        if (!$clean_column_name) {
            throw new LMFWC_Exception('Invalid column name.');
        }

        if (!$clean_operator) {
            throw new LMFWC_Exception('Invalid operator.');
        }

        if (!in_array($clean_status, LicenseStatusEnum::$status)) {
            throw new LMFWC_Exception('Invalid status.');
        }

        global $wpdb;

        if ($clean_operator == 'in') {
            $result = $wpdb->query(
                sprintf(
                    'UPDATE %s SET status = %d WHERE %s IN (%s)',
                    $this->table,
                    $clean_status,
                    $clean_column_name,
                    implode(', ', $value)
                )
            );
        } elseif ($clean_operator == 'eq') {
            $result = $wpdb->query(
                sprintf(
                    'UPDATE %s SET status = %d WHERE %s = %d',
                    $this->table,
                    $clean_status,
                    $clean_column_name,
                    intval($value)
                )
            );
        }

        return $result;
    }

    /**
     * Activates or Deactivates license keys.
     *
     * @param integer $id         License Key ID
     * @param integer $updated_by WordPress User ID
     *
     * @since  1.2.0
     * @throws Exception
     * @return array
     */
    public function activateLicenseKey($id, $updated_by)
    {
        $clean_id         = $id         ? absint($id)         : null;
        $clean_updated_by = $updated_by ? absint($updated_by) : null;

        if (!$clean_id) {
            throw new LMFWC_Exception('Invalid License Key ID');
        }

        if (!$clean_updated_by || !get_userdata($clean_updated_by)) {
            throw new LMFWC_Exception('Updated by User ID is invalid');
        }

        global $wpdb;

        $license = $this->getLicenseKey($clean_id);
        $current = intval($license['times_activated']);

        $wpdb->update(
            $this->table,
            array(
                'times_activated' => ++$current,
                'updated_at'      => gmdate('Y-m-d H:i:s'),
                'updated_by'      => $clean_updated_by
            ),
            array('id' => $clean_id),
            array('%d', '%s', '%d'),
            array('%d')
        );

        return $this->getLicenseKey($clean_id); 
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
            throw new LMFWC_Exception('Input parameter must be an array');
        }

        foreach ($licenses as $id) {
            if (!absint($id)) {
                continue;
            }

            array_push($clean_ids, absint($id));
        }

        if (count($clean_ids) == 0) {
            throw new LMFWC_Exception('No valid IDs given');
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