<?php

namespace LicenseManager;

defined('ABSPATH') || exit;

/**
 * LicenseManager
 *
 * @package LicenseManager
 * @since 1.0.0
 * @author Dražen Bebić
 * @link https://www.bebic.at/license-manager-for-woocommerce
 */
final class Main
{
    /**
     * LicenseManager version.
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * The single instance of the class.
     *
     * @var LicenseManager
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * LicenseManager Constructor.
     */
    private function __construct()
    {
        $this->defineConstants();
        $this->initHooks();

        add_action('init', array($this, 'init'));
    }

    /**
     * Main LicenseManager Instance.
     *
     * Ensures only one instance of LicenseManager is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return LicenseManager - Main instance.
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
        $this->define('ABSPATH_LENGTH',     strlen(ABSPATH));
        $this->define('LM_VERSION',         $this->version);
        $this->define('LM_ABSPATH',         dirname(LM_PLUGIN_FILE) . '/' );
        $this->define('LM_PLUGIN_BASENAME', plugin_basename(LM_PLUGIN_FILE));

        // Directories
        $this->define('LM_ASSETS_DIR',    LM_ABSPATH       . 'assets/');
        $this->define('LM_LOG_DIR',       LM_ABSPATH       . 'logs/');
        $this->define('LM_TEMPLATES_DIR', LM_ABSPATH       . 'templates/');
        $this->define('LM_ETC_DIR',       LM_ASSETS_DIR    . 'etc/');
        $this->define('LM_METABOX_DIR',   LM_TEMPLATES_DIR . 'meta-box/');

        // URL's
        $this->define('LM_ASSETS_URL', LM_PLUGIN_URL . 'assets/');
        $this->define('LM_ETC_URL',    LM_ASSETS_URL . 'etc/');
        $this->define('LM_CSS_URL',    LM_ASSETS_URL . 'css/');
        $this->define('LM_JS_URL',     LM_ASSETS_URL . 'js/');
        $this->define('LM_IMG_URL',    LM_ASSETS_URL . 'img/');
    }


    /**
     * Include JS and CSS files.
     */
    public function adminEnqueueScripts()
    {
        // CSS
        wp_enqueue_style('lima_admin_css', LM_CSS_URL . 'main.css');

        // JavaScript
        wp_enqueue_script('lima_admin_js', LM_JS_URL  . 'script.js');

        // Script localization
        wp_localize_script('lima_admin_js', 'license', array(
            'show'     => wp_create_nonce('lima_show_license_key'),
            'show_all' => wp_create_nonce('lima_show_all_license_keys'),
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
                    __('Documentation', 'lima')
                ),
                'donate' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    'https://www.bebic.at/license-manager-for-woocommerce/donate',
                    __('Donate', 'lima')
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
        register_activation_hook(LM_PLUGIN_FILE, array('\LicenseManager\Setup', 'install'));
        register_uninstall_hook(LM_PLUGIN_FILE, array('\LicenseManager\Setup', 'uninstall'));

        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_filter('plugin_row_meta', array($this, 'pluginRowMeta'), 10, 2);
    }

    /**
     * Init LicenseManager when WordPress Initialises.
     */
    public function init()
    {
        $crypto = new Crypto();

        new ProductManager();
        new AdminMenus($crypto);
        new AdminNotice();
        new Generator();
        new OrderManager($crypto);
        new Database($crypto);
        new FormHandler($crypto);
    }

}
