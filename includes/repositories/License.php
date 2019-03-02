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
        add_filter('lmfwc_get_licenses', array($this, 'getLicenses'), 10);
        add_filter('lmfwc_get_license', array($this, 'getLicense'), 10, 1);

        // INSERT

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
    public function getLicenses()
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
     * @return array
     */
    public function getLicense($id)
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