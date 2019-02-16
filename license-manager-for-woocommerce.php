<?php
/**
 * Plugin Name: License Manager for WooCommerce
 * Plugin URI: https://www.bebic.at/license-manager-for-woocommerce
 * Description: Sell software licenses through your WooCommerce shop.
 * Version: 1.0.0
 * Author: Dražen Bebić
 * Author URI: https://www.bebic.at/
 * Text Domain: lima
 * Domain Path: /i18n
 * Requires at least: 4.7
 * Tested up to: 5.0
 * WC requires at least: 2.7
 * WC tested up to: 3.5
 */

namespace LicenseManager;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

// Define LM_PLUGIN_FILE.
if (!defined('LM_PLUGIN_FILE')) {
    define('LM_PLUGIN_FILE', __FILE__);
}

// Define LM_PLUGIN_URL.
if (!defined('LM_PLUGIN_URL')) {
    define('LM_PLUGIN_URL', plugins_url('', __FILE__) . '/');
}

/**
 * Main instance of LicenseManager.
 *
 * Returns the main instance of SN to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return LicenseManager
 */
function licensemanager() {
    return \LicenseManager\Main::instance();
}

// Global for backwards compatibility.
$GLOBALS['licensemanager'] = licensemanager();