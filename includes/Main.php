<?php

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce
 *
 * @package LicenseManagerForWooCommerce
 * @version 1.0.0
 * @since 1.0.0
 * @author Dražen Bebić
 * @link https://www.bebic.at/license-manager-for-woocommerce
 */
final class Main
{
    /**
     * LicenseManagerForWooCommerce version.
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * The single instance of the class.
     *
     * @var LicenseManagerForWooCommerce
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * LicenseManagerForWooCommerce Constructor.
     */
    private function __construct()
    {
        $this->defineConstants();
        $this->initHooks();

        add_action('init', array($this, 'init'));
    }

    /**
     * Main LicenseManagerForWooCommerce Instance.
     *
     * Ensures only one instance of LicenseManagerForWooCommerce is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return LicenseManagerForWooCommerce - Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Define plugin constants.
     */
    private function defineConstants()
    {
        $this->define('ABSPATH_LENGTH',        strlen(ABSPATH));
        $this->define('LMFWC_VERSION',         $this->version);
        $this->define('LMFWC_ABSPATH',         dirname(LMFWC_PLUGIN_FILE) . '/' );
        $this->define('LMFWC_PLUGIN_BASENAME', plugin_basename(LMFWC_PLUGIN_FILE));

        // Directories
        $this->define('LMFWC_ASSETS_DIR',    LMFWC_ABSPATH       . 'assets/');
        $this->define('LMFWC_LOG_DIR',       LMFWC_ABSPATH       . 'logs/');
        $this->define('LMFWC_TEMPLATES_DIR', LMFWC_ABSPATH       . 'templates/');
        $this->define('LMFWC_ETC_DIR',       LMFWC_ASSETS_DIR    . 'etc/');
        $this->define('LMFWC_METABOX_DIR',   LMFWC_TEMPLATES_DIR . 'meta-box/');

        // URL's
        $this->define('LMFWC_ASSETS_URL', LMFWC_PLUGIN_URL . 'assets/');
        $this->define('LMFWC_ETC_URL',    LMFWC_ASSETS_URL . 'etc/');
        $this->define('LMFWC_CSS_URL',    LMFWC_ASSETS_URL . 'css/');
        $this->define('LMFWC_JS_URL',     LMFWC_ASSETS_URL . 'js/');
        $this->define('LMFWC_IMG_URL',    LMFWC_ASSETS_URL . 'img/');
    }


    /**
     * Include JS and CSS files.
     */
    public function adminEnqueueScripts()
    {
        // CSS
        wp_enqueue_style('lmfwc_admin_css', LMFWC_CSS_URL . 'main.css');

        // JavaScript
        wp_enqueue_script('lmfwc_admin_js', LMFWC_JS_URL  . 'script.js');

        // Script localization
        wp_localize_script('lmfwc_admin_js', 'license', array(
            'show'     => wp_create_nonce('lmfwc_show_license_key'),
            'show_all' => wp_create_nonce('lmfwc_show_all_license_keys'),
        ));
    }

    /**
     * Add additional links to the plugin row meta.
     */
    public function pluginRowMeta($links, $file)
    {
        if (strpos($file, 'license-manager-for-woocommerce.php' ) !== false ) {
            $new_links = array(
                'github' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    'https://github.com/drazenbebic/license-manager',
                    'GitHub'
                ),
                'docs' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    'https://www.bebic.at/license-manager-for-woocommerce/docs',
                    __('Documentation', 'lmfwc')
                ),
                'donate' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    'https://www.bebic.at/license-manager-for-woocommerce/donate',
                    __('Donate', 'lmfwc')
                )
            );
            
            $links = array_merge( $links, $new_links );
        }

        return $links;
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define( $name, $value ) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Hook into actions and filters.
     *
     * @since 1.0.0
     */
    private function initHooks()
    {
        register_activation_hook(LMFWC_PLUGIN_FILE, array('\LicenseManagerForWooCommerce\Setup', 'install'));
        register_uninstall_hook(LMFWC_PLUGIN_FILE, array('\LicenseManagerForWooCommerce\Setup', 'uninstall'));

        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_filter('plugin_row_meta', array($this, 'pluginRowMeta'), 10, 2);
    }

    /**
     * Init LicenseManagerForWooCommerce when WordPress Initialises.
     */
    public function init()
    {
        $crypto = new Crypto();

        new ProductManager();
        new AdminMenus();
        new AdminNotice();
        new Generator();
        new OrderManager();
        new Database();
        new FormHandler();
    }

}
