<?php
/**
 * Migrations handler
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.2.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class Migration
{
    public static function up($old_db_version)
    {
        $reg_exp_filename = '/(\d{14})_(.*?)_(.*?)\.php/';
        $migration_mode = 'up';

        foreach (glob(LMFWC_MIGRATIONS_DIR . '*.php') as $filename) {
            if (preg_match($reg_exp_filename, basename($filename), $match)) {
                $file_basename = $match[0];
                $file_datetime = $match[1];
                $file_version = $match[2];
                $file_description = $match[3];

                global $wpdb;

                if (intval($file_version) <= Setup::DB_VERSION
                    && intval($file_version) > $old_db_version
                ) {
                    require_once $filename;
                }
            }
        }

        update_option('lmfwc_db_version', Setup::DB_VERSION, true);
    }

    public static function down($old_db_version)
    {
    }
}