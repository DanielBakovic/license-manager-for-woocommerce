<?php

namespace LicenseManager;

use \LicenseManager\Enums\LicenseStatusEnum;
use \LicenseManager\Enums\SourceEnum;

defined('ABSPATH') || exit;

/**
 * LicenseManager Database connector.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class Database
{
    /**
     * @var \LicenseManager\Crypto
     */
    private $crpyto;

    /**
     * Database Constructor.
     */
    public function __construct(
        \LicenseManager\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        // Get
        add_filter('lima_get_assigned_products',         array($this, 'getAssignedProducts'), 10, 1);
        add_filter('lima_get_available_stock',           array($this, 'getAvailableStock'),   10, 1);

        // Insert
        add_action('lima_insert_generated_license_keys', array($this, 'insertGeneratedLicenseKeys'), 10, 1);
        add_filter('lima_insert_imported_license_keys',  array($this, 'insertImportedLicenseKeys'),  10, 1);
        add_filter('lima_insert_added_license_key',      array($this, 'insertAddedLicenseKey'),      10, 1);
        add_filter('lima_insert_generator',              array($this, 'insertGenerator'),            10, 1);

        // Update
        add_action('lima_sell_imported_license_keys',    array($this, 'sellImportedLicenseKeys'), 10, 1);
        add_filter('lima_toggle_license_key_status',     array($this, 'toggleLicenseKeyStatus'),  10, 1);
        add_filter('lima_update_generator',              array($this, 'updateGenerator'),         10, 1);

        // Delete
        add_filter('lima_delete_license_keys',           array($this, 'deleteLicenseKeys'        ), 10, 1);
        add_filter('lima_delete_generators',             array($this, 'deleteGenerators'         ), 10, 1);

        // Misc.
        add_filter('lima_license_key_exists',            array($this, 'licenseKeyExists'         ), 10, 1);
    }

    // GET

    /**
     * Retrieve assigned products for a specific generator.
     *
     * @since 1.0.0
     *
     * @param int $args['generator_id']
     *
     * @return boolean
     */
    public function getAssignedProducts($args)
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare("
                SELECT
                    post_id
                FROM
                    {$wpdb->postmeta}
                WHERE
                    1 = 1
                    AND meta_key = %s
                    AND meta_value = %d
                ",
                '_lima_licensed_product_assigned_generator',
                absint($args['generator_id'])
            ),
            OBJECT
        );

        if ($results) {
            $products = [];

            foreach ($results as $row) {
                $products[] = wc_get_product($row->post_id);
            }
        } else {
            $products = null;
        }

        return $products;
    }

    /**
     * Retrieves the number of available license keys for a given product.
     *
     * @since 1.0.0
     *
     * @param int $args['product_id']
     *
     * @return int
     */
    public function getAvailableStock($args)
    {
        global $wpdb;

        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        return $wpdb->get_var(
            $wpdb->prepare("
                SELECT
                    COUNT(*)
                FROM
                    {$table}
                WHERE
                    1=1
                    AND product_id = %d
                    AND status = %d
                ",
                intval($args['product_id']),
                LicenseStatusEnum::ACTIVE
            )
        );
    }

    // INSERT

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
            if (apply_filters('lima_license_key_exists', $license_key)) {
                $invalid_keys_amount++;
            // Key doesn't exist, add it to the database table.
            } else {
                // Save to database.
                $wpdb->insert(
                    $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
                    array(
                        'order_id'    => $args['order_id'],
                        'product_id'  => $args['product_id'],
                        'license_key' => $this->crypto->encrypt($license_key),
                        'hash'        => $this->crypto->hash($license_key),
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
            $new_keys = apply_filters('lima_create_license_keys', array(
                'amount'       => $invalid_keys_amount,
                'charset'      => $args['charset'],
                'chunks'       => $args['chunks'],
                'chunk_length' => $args['chunk_length'],
                'separator'    => $args['separator'],
                'prefix'       => $args['prefix'],
                'suffix'       => $args['suffix'],
                'expires_in'   => $args['expires_in']
            ));
            $this->insertGeneratedLicenseKeys(array(
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
            ));
        } else {
            // Keys have been generated and saved, this order is now complete.
            update_post_meta($args['order_id'], '_lima_order_complete', 1);
        }
    }

    /**
     * Imports an array of un-encrypted license keys into the database.
     *
     * @since 1.0.0
     *
     * @param array   $args['license_keys']
     * @param boolean $args['activate'] 
     * @param int     $args['product_id']
     *
     * @return array
     */
    public function insertImportedLicenseKeys($args)
    {
        global $wpdb;

        $created_at       = date('Y-m-d H:i:s');
        $result['added']  = 0;
        $result['failed'] = 0;
        $args['activate'] ? $status = LicenseStatusEnum::ACTIVE : $status = LicenseStatusEnum::INACTIVE;

        // Add the keys to the database table.
        foreach ($args['license_keys'] as $license_key) {
            if ($wpdb->insert(
                    $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
                    array(
                        'order_id'    => null,
                        'product_id'  => $args['product_id'],
                        'license_key' => $this->crypto->encrypt($license_key),
                        'hash'        => $this->crypto->hash($license_key),
                        'created_at'  => $created_at,
                        'expires_at'  => null,
                        'source'      => SourceEnum::IMPORT,
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
     * Saves an un-encrypted license keys into the database.
     *
     * @since 1.0.0
     *
     * @param string  $args['license_key']
     * @param boolean $args['activate']
     * @param int     $args['product_id']
     * @param int     $args['valid_for']
     *
     * @return array
     */
    public function insertAddedLicenseKey($args)
    {
        global $wpdb;

        $created_at = date('Y-m-d H:i:s');
        $args['activate'] ? $status = LicenseStatusEnum::ACTIVE : $status = LicenseStatusEnum::INACTIVE;

        return $wpdb->insert(
            $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
            array(
                'order_id'    => null, // Because it's only added, not bought.
                'product_id'  => $args['product_id'],
                'license_key' => $this->crypto->encrypt($args['license_key']),
                'hash'        => $this->crypto->hash($args['license_key']),
                'created_at'  => $created_at,
                'expires_at'  => null, // Because it's only added, not bought.
                'valid_for'   => $args['valid_for'],
                'source'      => SourceEnum::IMPORT,
                'status'      => $status
            ),
            array('%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d')
        );
    }

    /**
     * Save the generator to the database.
     *
     * @since 1.0.0
     *
     * @param string $args['name']         - Generator name.
     * @param string $args['charset']      - Character map used for key generation.
     * @param int    $args['chunks']       - Number of chunks.
     * @param int    $args['chunk_length'] - Chunk length.
     * @param string $args['separator']    - Separator used.
     * @param string $args['prefix']       - License key prefix.
     * @param string $args['suffis']       - License key suffix.
     * @param string $args['expires_in']   - Number of days for which the license is valid.
     */
    public function insertGenerator($args)
    {
        global $wpdb;

        return $wpdb->insert(
            $wpdb->prefix . Setup::GENERATORS_TABLE_NAME,
            array(
                'name'         => sanitize_text_field($args['name']),
                'charset'      => sanitize_text_field($args['charset']),
                'chunks'       => intval($args['chunks']),
                'chunk_length' => intval($args['chunk_length']),
                'separator'    => sanitize_text_field($args['separator']),
                'prefix'       => sanitize_text_field($args['prefix']),
                'suffix'       => sanitize_text_field($args['suffix']),
                'expires_in'   => sanitize_text_field($args['expires_in'])
            ),
            array('%s', '%s', '%d', '%d', '%s', '%s', '%s')
        );
    }

    // UPDATE

    /**
     * Sell license keys already present in the database.
     *
     * @since 1.0.0
     *
     * @param array  $args['license_keys']
     * @param int    $args['order_id']
     * @param int    $args['amount']
     */
    public function sellImportedLicenseKeys($args)
    {
        global $wpdb;

        for ($i = 0; $i < $args['amount']; $i++) {
            $date       = new \DateTime();
            $valid_for  = $args['license_keys'][$i]->valid_for;
            $expires_at = null;

            if (is_numeric($valid_for)) {
                $expires_at = $date->add(new \DateInterval('P' . intval($valid_for) . 'D'))->format('Y-m-d H:i:s');
            }

            $wpdb->update(
                $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
                array(
                    'order_id'   => intval($args['order_id']),
                    'expires_at' => $expires_at,
                    'status'     => LicenseStatusEnum::SOLD
                ),
                array('id' => $args['license_keys'][$i]->id),
                array('%d', '%s', '%d'),
                array('%d')
            );
        }
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

        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;
        $sql   = "SELECT license_key FROM `{$table}` WHERE hash = '%s';";

        return $wpdb->get_var($wpdb->prepare($sql, $this->crypto->hash($license_key))) != null;
    }

    /**
     * Update an existing generator.
     *
     * @since 1.0.0
     *
     * @param int    $args['id']           - Generator ID.
     * @param string $args['name']         - Generator name.
     * @param string $args['charset']      - Character map used for key generation.
     * @param int    $args['chunks']       - Number of chunks.
     * @param int    $args['chunk_length'] - Chunk length.
     * @param string $args['separator']    - Separator used.
     * @param string $args['prefix']       - License key prefix.
     * @param string $args['suffis']       - License key suffix.
     * @param string $args['expires_in']   - Number of days for which the license is valid.
     */
    public function updateGenerator($args)
    {
        global $wpdb;

        return $wpdb->update(
            $wpdb->prefix . Setup::GENERATORS_TABLE_NAME,
            array(
                'name'         => sanitize_text_field($args['name']),
                'charset'      => sanitize_text_field($args['charset']),
                'chunks'       => intval($args['chunks']),
                'chunk_length' => intval($args['chunk_length']),
                'separator'    => sanitize_text_field($args['separator']),
                'prefix'       => sanitize_text_field($args['prefix']),
                'suffix'       => sanitize_text_field($args['suffix']),
                'expires_in'   => sanitize_text_field($args['expires_in'])
            ),
            array('id' => intval($args['id'])),
            array('%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s'),
            array('%d')
        );
    }

    /**
     * Deletes license keys.
     *
     * @since 1.0.0
     *
     * @param int $args['id']
     *
     * @return boolean
     */
    public function deleteGenerators($args)
    {
        global $wpdb;

        return $wpdb->query(
            sprintf(
                'DELETE FROM %s WHERE id IN (%s)',
                $wpdb->prefix . Setup::GENERATORS_TABLE_NAME,
                implode(', ', $args['ids'])
            )
        );
    }

    /**
     * Retrieves all license keys related to a specific order.
     *
     * @since 1.0.0
     *
     * @param int $order_id
     *
     * @return array
     */
    public static function getLicenseKeysByOrderId($order_id, $status)
    {
        global $wpdb;
        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE order_id = %d AND status = %d",
                intval($order_id),
                intval($status)
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
    public static function getLicenseKeysByProductId($product_id, $status)
    {
        global $wpdb;
        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE product_id = %d AND status = %d",
                intval($product_id),
                intval($status)
            ),
            OBJECT
        );
    }

    /**
     * Retrieves all license keys related to a order/product combination.
     *
     * @since 1.0.0
     *
     * @param int $product_id
     * @param int $status
     *
     * @return array
     */
    public static function getOrderedLicenseKeys($order_id, $product_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE order_id = %d AND product_id = %d",
                intval($order_id),
                intval($product_id)
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

    public static function getLicenseKey($id)
    {
        global $wpdb;

        $table       = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;
        $crypto      = new Crypto();
        $license_key = $wpdb->get_var($wpdb->prepare("SELECT license_key FROM $table WHERE id = %d", $id));

        if ($license_key) {
            return $crypto->decrypt($license_key);
        } else {
            return $license_key;
        }

        return $license_key;
    }

    /**
     * Deletes license keys.
     *
     * @since 1.0.0
     *
     * @param int $args['id']
     *
     * @return boolean
     */
    public static function deleteLicenseKeys($args)
    {
        global $wpdb;

        return $wpdb->query(sprintf(
            'DELETE FROM %s WHERE id IN (%s)',
            $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
            implode(', ', $args['ids'])
        ));
    }

    /**
     * Activates or Deactivates license keys.
     *
     * @since 1.0.0
     *
     * @param int    $args['status']
     * @param string $args['column_name']
     * @param string $args['operator']
     * @param array  $args['value']
     *
     * @return boolean
     */
    public function toggleLicenseKeyStatus($args)
    {
        global $wpdb;

        if ($args['operator'] == 'in') {
            $result = $wpdb->query(
                sprintf(
                    'UPDATE %s SET status = %d WHERE %s IN (%s)',
                    $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
                    intval($args['status']),
                    sanitize_text_field($args['column_name']),
                    implode(', ', $args['value'])
                )
            );
        } elseif ($args['operator'] == 'eq') {
            $result = $wpdb->query(
                sprintf(
                    'UPDATE %s SET status = %d WHERE %s = %d',
                    $wpdb->prefix . Setup::LICENSES_TABLE_NAME,
                    intval($args['status']),
                    sanitize_text_field($args['column_name']),
                    intval($args['value'])
                )
            );
        }

        return $result;
    }

    /**
     * Returns the number of license keys available.
     *
     * @param $status \LicenseManager\Enums\LicenseStatusEnum
     *
     * @return int
     */
    public static function getLicenseKeyCount($status = 0)
    {
        global $wpdb;

        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        if (!in_array($status, LicenseStatusEnum::$statuses) & $status != 0) {
            return 0;
        }

        if ($status == 0) {
            return $wpdb->get_var("SELECT COUNT(*) FROM $table");
        } else {
            return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE status = %d", intval($status)));
        }
    }

    /**
     * Returns distinct values from a specific column/table.
     *
     * @param $column string
     * @param $table string
     *
     * @return (array)\stdObject
     */
    public static function getDistinct($column, $table)
    {
        global $wpdb;

        $table = $wpdb->prefix . $table;

        return $wpdb->get_results("SELECT DISTINCT {$column} FROM {$table}", OBJECT);
    }

}