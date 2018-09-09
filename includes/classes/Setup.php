<?php

namespace LicenseManager\Classes;

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
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `order_id` int(20) NOT NULL,
                `product_id` bigint(20) NOT NULL,
                `license_key` varchar(256) NOT NULL,
                `created_at` datetime NOT NULL,
                `expires_at` datetime NULL,
                `status` smallint(5) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            CREATE TABLE $table2 (
                `id` int(20) NOT NULL AUTO_INCREMENT,
                `name` varchar(256) NOT NULL,
                `charset` varchar(256) NULL,
                `chunks` int(10) NOT NULL,
                `chunk_length` int(10) NOT NULL,
                `separator` varchar(256) NOT NULL,
                `prefix` varchar(256) NULL,
                `suffix` varchar(256) NULL,
                `expires_in` int(10) NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        \dbDelta($tables);
    }

    public static function setDefaulOptions()
    {
        // The defaults for the Setting API.
        $defaults = array(
            '_lima_encrypt_license_keys' => 1
        );

        update_option('_lima_settings', $defaults, '', 'yes');

        // Cryptographic secrets.
        if (!file_exists(LM_ETC_DIR . 'secret.txt')) {
            file_put_contents(LM_ETC_DIR . 'secret.txt', bin2hex(random_bytes(16)));
        }
    }
}