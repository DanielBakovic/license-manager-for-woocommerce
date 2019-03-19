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
            6
        );
        add_filter(
            'lmfwc_insert_generated_license_keys',
            array($this, 'insertGeneratedLicenseKeys'),
            10,
            6
        );
        add_filter(
            'lmfwc_insert_imported_license_keys',
            array($this, 'insertImportedLicenseKeys'),
            10,
            4
        );

        // UPDATE
        add_filter(
            'lmfwc_update_license_key',
            array($this, 'updateLicenseKey'),
            10,
            6
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
            6
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
            throw new \Exception('Invalid License Key ID', 1);
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
     * Returns the number of license keys available.
     *
     * @param LicenseStatusEnum $status The new license key status
     *
     * @since  1.1.0
     * @throws Exception
     * @return integer
     */
    public function getLicenseKeyCount($status = null)
    {
        global $wpdb;

        $clean_status = $status ? absint($status) : null;

        if ($clean_status && !in_array($clean_status, LicenseStatusEnum::$statuses)) {
            throw new \Exception('License Status is invalid', 1);
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
            throw new \Exception('Product ID is invalid.', 1);
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
            throw new \Exception('Order ID is invalid', 1);
        }

        if (!$clean_product_id) {
            throw new \Exception("Product ID is invalid", 2);
        }

        if ($clean_order_id) {
            new \WC_Order($clean_order_id);
        }

        if ($clean_product_id) {
            new \WC_Product($clean_product_id);
        }

        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "
                    SELECT
                        id
                        , order_id
                        , product_id
                        , license_key
                        , hash
                        , created_at
                        , expires_at
                        , valid_for
                        , source
                        , status
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
    public static function getProductLicenseKeys($product_id, $status)
    {
        $clean_product_id = $product_id ? absint($product_id) : null;
        $clean_status     = $status     ? absint($status)     : null;

        if (!$clean_product_id) {
            throw new \Exception('Product ID is invalid', 1);
        }

        if (!$clean_status) {
            throw new \Exception('Status is invalid', 2);
        }

        if ($clean_product_id) {
            new \WC_Product($product_id);
        }

        if (!in_array($clean_status, LicenseStatusEnum::$statuses)) {
            throw new \Exception('Status is invalid', 3);
        }

        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "
                    SELECT
                        id
                        , order_id
                        , product_id
                        , license_key
                        , hash
                        , created_at
                        , expires_at
                        , valid_for
                        , source
                        , status
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
            throw new \Exception('License Key is invalid.', 1);
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

        if (!$clean_license_key || strlen($clean_license_key) == 0) {
            throw new \Exception('Invalid License Key', 1);
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
        $generator
    ) {
        $clean_order_id     = $order_id     ? absint($order_id)     : null;
        $clean_product_id   = $product_id   ? absint($product_id)   : null;
        $clean_license_keys = array();
        $clean_expires_in   = $expires_in   ? absint($expires_in)   : null;
        $clean_status       = $status       ? absint($status)       : null;

        if (!$clean_status
            || !in_array($clean_status, LicenseStatusEnum::$statuses)
        ) {
            throw new \Exception('License Status is invalid.', 1);
        }

        if (!is_array($license_keys)) {
            throw new \Exception('License Keys must be provided as array', 2);
        }

        foreach ($license_keys as $license_key) {
            array_push($clean_license_keys, sanitize_text_field($license_key));
        }

        if (count($clean_license_keys) === 0) {
            throw new \Exception('No License Keys were provided', 3);
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
                    'order_id'    => $clean_order_id,
                    'product_id'  => $clean_product_id,
                    'license_key' => $encrypted_license_key,
                    'hash'        => $hashed_license_key,
                    'created_at'  => $created_at,
                    'expires_at'  => $expires_at,
                    'valid_for'   => $clean_expires_in,
                    'source'      => SourceEnum::GENERATOR,
                    'status'      => $clean_status
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%d')
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
     * @param array   $license_keys License Keys to be added
     * @param integer $status       License Status Enumerator value 
     * @param int     $product_id   WooCommerce Product ID
     *
     * @since  1.1.0
     * @throws Exception
     * @return array
     */
    public function insertImportedLicenseKeys(
        $license_keys,
        $status,
        $product_id,
        $valid_for
    ) {
        $clean_license_keys = array();
        $clean_status       = $status     ? absint($status)     : null;
        $clean_product_id   = $product_id ? absint($product_id) : null;
        $clean_valid_for    = $valid_for  ? absint($valid_for)  : null;
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
            $insert = $wpdb->insert(
                $this->table,
                array(
                    'order_id'    => null,
                    'product_id'  => $clean_product_id,
                    'license_key' => apply_filters('lmfwc_encrypt', $license_key),
                    'hash'        => apply_filters('lmfwc_hash', $license_key),
                    'created_at'  => $created_at,
                    'expires_at'  => null,
                    'valid_for'   => $clean_valid_for,
                    'source'      => SourceEnum::IMPORT,
                    'status'      => $clean_status
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d')
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
     * @param integer $id          ID of the edited License Key
     * @param integer $product_id  WooCommerce Product ID
     * @param string  $license_key Decrypted License Key
     * @param integer $valid_for   Validity in days
     * @param integer $source      Source enumerator
     * @param integer $status      Status enumerator
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
        $status
    ) {
        $clean_id          = $id          ? absint($id)                       : null;
        $clean_product_id  = $product_id  ? absint($product_id)               : null;
        $clean_license_key = $license_key ? sanitize_text_field($license_key) : null;
        $clean_valid_for   = $valid_for   ? absint($valid_for)                : null;
        $clean_source      = $source      ? absint($source)                   : null;
        $clean_status      = $status      ? absint($status)                   : null;

        if (!$clean_id) {
            throw new \Exception('Invalid License Key ID', 1);
        }

        if ($clean_product_id) {
            new \WC_Product($clean_product_id);
        }

        if (!$clean_license_key || strlen($clean_license_key) == 0) {
            throw new \Exception('Invalid License Key', 2);
        }

        if (!in_array($clean_source, SourceEnum::$sources)) {
            throw new \Exception('Source Enumerator is invalid', 3);
        }

        if (!in_array($clean_status, LicenseStatusEnum::$statuses)) {
            throw new \Exception('Status Enumerator is invalid', 4);
        }

        global $wpdb;

        $encrypted_license_key = apply_filters('lmfwc_encrypt', $clean_license_key);
        $hashed_license_key = apply_filters('lmfwc_hash', $clean_license_key);

        return $wpdb->update(
            $this->table,
            array(
                'product_id'  => $clean_product_id,
                'license_key' => $encrypted_license_key,
                'hash'        => $hashed_license_key,
                'valid_for'   => $clean_valid_for,
                'source'      => $clean_source,
                'status'      => $clean_status
            ),
            array('id' => $clean_id),
            array('%d', '%s', '%s', '%d', '%d', '%d'),
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
            throw new \Exception('Invalid License Key ID', 1);
        }

        if (!in_array($clean_status, LicenseStatusEnum::$statuses)) {
            throw new \Exception('Status Enumerator is invalid', 2);
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

        if (!$clean_id) {
            throw new \Exception('Invalid License Key ID', 1);
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
        $clean_license_keys = array();
        $clean_order_id     = $order_id ? absint($order_id) : null;
        $clean_amount       = $amount   ? absint($amount)   : null;

        if (!is_array($license_keys) || count($license_keys) <= 0) {
            throw new \Exception('License Keys are invalid.', 1);
        }

        if (!$clean_order_id) {
            throw new \Exception('Order ID is invalid.', 2);
        }

        if (!$clean_order_id) {
            throw new \Exception('Amount is invalid.', 3);
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
     * Activates or Deactivates license keys.
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
            throw new \Exception('Invalid column name.', 1);
        }

        if (!$clean_operator) {
            throw new \Exception('Invalid operator.', 2);
        }

        if (!in_array($clean_status, LicenseStatusEnum::$statuses)) {
            throw new \Exception('Invalid status.', 3);
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