<?php
/**
 * Plugin Name: License Manager for WooCommerce
 * Plugin URI: https://www.bebic.at/license-manager-for-woocommerce
 * Description: Easily sell and manage software license keys through your WooCommerce shop.
 * Version: 1.3.0
 * Author: Dražen Bebić
 * Author URI: https://www.bebic.at/
 * Text Domain: lmfwc
 * Domain Path: /i18n
 * Requires at least: 4.7
 * Tested up to: 5.2
 * WC requires at least: 2.7
 * WC tested up to: 3.7
 */

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

// Define LMFWC_PLUGIN_FILE.
if (!defined('LMFWC_PLUGIN_FILE')) {
    define('LMFWC_PLUGIN_FILE', __FILE__);
    define('LMFWC_PLUGIN_DIR', __DIR__);
}

// Define LMFWC_PLUGIN_URL.
if (!defined('LMFWC_PLUGIN_URL')) {
    define('LMFWC_PLUGIN_URL', plugins_url('', __FILE__) . '/');
}

/**
 * Main instance of LicenseManagerForWooCommerce.
 *
 * Returns the main instance of SN to prevent the need to use globals.
 *
 * @return Main
 */
function lmfwc()
{
    return Main::instance();
}

// Global for backwards compatibility.
$GLOBALS['lmfwc'] = lmfwc();