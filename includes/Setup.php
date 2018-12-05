<?php

namespace LicenseManager;

/**
 * LicenseManager Setup.
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Setup class.
 */
class Setup
{
    /**
     * License table name.
     *
     * @since 1.0.0
     */
    const LICENSES_TABLE_NAME = 'licensemanager_licenses';

    /**
     * Generators table name.
     *
     * @since 1.0.0
     */
    const GENERATORS_TABLE_NAME = 'licensemanager_generators';

    /**
     * Database version.
     *
     * @since 1.0.0
     */
    const DB_VERSION = 100;

    /**
     * Setup Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Installation script.
     *
     * @since 1.0.0
     */
    public static function install()
    {
        self::createTables();
        self::setDefaulOptions();
    }

    /**
     * Uninstallation script.
     *
     * @since 1.0.0
     */
    public function uninstall()
    {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . self::LICENSES_TABLE_NAME,
            $wpdb->prefix . self::GENERATORS_TABLE_NAME
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }

    /**
     * Create the necessary database tables.
     *
     * @since 1.0.0
     */
    public static function createTables()
    {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $table1 = $wpdb->prefix . self::LICENSES_TABLE_NAME;
        $table2 = $wpdb->prefix . self::GENERATORS_TABLE_NAME;

        $tables = "
            CREATE TABLE $table1 (
                `id` BIGINT(20) NOT NULL COMMENT 'Primary Key' AUTO_INCREMENT,
                `order_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'WC_Order ID',
                `product_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'WC_Product ID',
                `license_key` VARCHAR(256) NOT NULL COMMENT 'Encrypted License Key',
                `hash` VARCHAR(256) NOT NULL COMMENT 'Hashed License Key ID',
                `created_at` DATETIME NOT NULL COMMENT 'Creation Date',
                `expires_at` DATETIME NULL COMMENT 'Expiration Date',
                `valid_for` INT(32) NULL DEFAULT NULL COMMENT 'License Validity (in days)',
                `source` VARCHAR(256) NOT NULL COMMENT 'Import or Generator',
                `status` TINYINT(1) NOT NULL COMMENT 'Sold, Delivered, Active, Inactive',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            CREATE TABLE $table2 (
                `id` INT(20) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(256) NOT NULL,
                `charset` VARCHAR(256) NULL,
                `chunks` INT(10) NOT NULL,
                `chunk_length` INT(10) NOT NULL,
                `separator` VARCHAR(256) NOT NULL,
                `prefix` VARCHAR(256) NULL,
                `suffix` VARCHAR(256) NULL,
                `expires_in` INT(10) NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        \dbDelta($tables);
    }

    public static function setDefaulOptions()
    {
        // The defaults for the Setting API.
        $defaults = array(
            '_lima_hide_license_keys' => 1,
            '_lima_auto_delivery' => 1
        );

        update_option('_lima_settings', $defaults, '', 'yes');

        // Cryptographic secrets.
        if (!file_exists(LM_ETC_DIR . 'secret.txt')) {
            file_put_contents(LM_ETC_DIR . 'secret.txt', bin2hex(random_bytes(16)));
        }
    }
}