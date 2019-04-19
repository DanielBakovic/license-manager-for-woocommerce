<?php
/**
 * Main plugin file.
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
final class Main
{
    /**
     * LicenseManagerForWooCommerce version.
     *
     * @var string
     */
    public $version = '1.2.3';

    /**
     * The single instance of the class.
     *
     * @var   LicenseManagerForWooCommerce
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * LicenseManagerForWooCommerce Constructor.
     * 
     * @return null
     */
    private function __construct()
    {
        $this->_defineConstants();
        $this->_initHooks();

        add_action('init', array($this, 'loadPluginTextDomain'));
        add_action('init', array($this, 'init'));
        add_action('woocommerce_email_classes', array($this, 'registerEmails'), 90, 1);

        new API\Authentication();
    }

    /**
     * Main LicenseManagerForWooCommerce Instance.
     *
     * Ensures only one instance of LicenseManagerForWooCommerce is loaded or can be
     * loaded.
     *
     * @since  1.0.0
     * @static
     * @return LicenseManagerForWooCommerce - Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Define plugin constants.
     * 
     * @return null
     */
    private function _defineConstants()
    {
        $this->_define('ABSPATH_LENGTH',        strlen(ABSPATH));
        $this->_define('LMFWC_VERSION',         $this->version);
        $this->_define('LMFWC_ABSPATH',         dirname(LMFWC_PLUGIN_FILE) . '/');
        $this->_define('LMFWC_PLUGIN_BASENAME', plugin_basename(LMFWC_PLUGIN_FILE));

        // Directories
        $this->_define('LMFWC_ASSETS_DIR',     LMFWC_ABSPATH       . 'assets/');
        $this->_define('LMFWC_LOG_DIR',        LMFWC_ABSPATH       . 'logs/');
        $this->_define('LMFWC_TEMPLATES_DIR',  LMFWC_ABSPATH       . 'templates/');
        $this->_define('LMFWC_MIGRATIONS_DIR', LMFWC_ABSPATH       . 'migrations/');
        $this->_define('LMFWC_CSS_DIR',        LMFWC_ASSETS_DIR    . 'css/');

        // URL's
        $this->_define('LMFWC_ASSETS_URL', LMFWC_PLUGIN_URL . 'assets/');
        $this->_define('LMFWC_ETC_URL',    LMFWC_ASSETS_URL . 'etc/');
        $this->_define('LMFWC_CSS_URL',    LMFWC_ASSETS_URL . 'css/');
        $this->_define('LMFWC_JS_URL',     LMFWC_ASSETS_URL . 'js/');
        $this->_define('LMFWC_IMG_URL',    LMFWC_ASSETS_URL . 'img/');
    }


    /**
     * Include JS and CSS files.
     * 
     * @return null
     */
    public function adminEnqueueScripts()
    {
        // Select2
        wp_register_style(
            'lmfwc_select2_cdn',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'
        );
        wp_register_style(
            'lmfwc_select2',
            LMFWC_CSS_URL . 'select2.css'
        );
        wp_register_script(
            'lmfwc_select2_cdn',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'
        );

        // CSS
        wp_enqueue_style('lmfwc_admin_css', LMFWC_CSS_URL . 'main.css');

        // JavaScript
        wp_enqueue_script('lmfwc_admin_js', LMFWC_JS_URL . 'script.js');

        if (isset($_GET['page']) && 
            (
                $_GET['page'] == AdminMenus::LICENSES_PAGE
                || $_GET['page'] == AdminMenus::GENERATORS_PAGE
                || $_GET['page'] == AdminMenus::SETTINGS_PAGE
            )
        ) {
            wp_enqueue_script('lmfwc_select2_cdn');
            wp_enqueue_style('lmfwc_select2_cdn');
            wp_enqueue_style('lmfwc_select2');
        }

        // Licenses page
        if (isset($_GET['page']) && $_GET['page'] == AdminMenus::LICENSES_PAGE) {
            wp_enqueue_script('lmfwc_licenses_page_js', LMFWC_JS_URL . 'licenses_page.js');
        }

        // Generators page
        if (isset($_GET['page']) && $_GET['page'] == AdminMenus::GENERATORS_PAGE) {
            wp_enqueue_script('lmfwc_generators_page_js', LMFWC_JS_URL . 'generators_page.js');
        }

        // Settings page
        if (isset($_GET['page']) && $_GET['page'] == AdminMenus::SETTINGS_PAGE) {
            wp_enqueue_script('lmfwc_settings_page_js', LMFWC_JS_URL . 'settings_page.js');
        }

        // Script localization
        wp_localize_script(
            'lmfwc_admin_js', 'license', array(
                'show'     => wp_create_nonce('lmfwc_show_license_key'),
                'show_all' => wp_create_nonce('lmfwc_show_all_license_keys'),
            )
        );
    }

    /**
     * Add additional links to the plugin row meta.
     * 
     * @param array  $links Array of already present links
     * @param string $file  File name
     * 
     * @return array
     */
    public function pluginRowMeta($links, $file)
    {
        if (strpos($file, 'license-manager-for-woocommerce.php') !== false ) {
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
            
            $links = array_merge($links, $new_links);
        }

        return $links;
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     * 
     * @return null
     */
    private function _define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Hook into actions and filters.
     *
     * @since  1.0.0
     * @return null;
     */
    private function _initHooks()
    {
        register_activation_hook(
            LMFWC_PLUGIN_FILE,
            array('\LicenseManagerForWooCommerce\Setup', 'install')
        );
        register_deactivation_hook(
            LMFWC_PLUGIN_FILE,
            array('\LicenseManagerForWooCommerce\Setup', 'deactivate')
        );
        register_uninstall_hook(
            LMFWC_PLUGIN_FILE,
            array('\LicenseManagerForWooCommerce\Setup', 'uninstall')
        );

        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_filter('plugin_row_meta', array($this, 'pluginRowMeta'), 10, 2);
    }

    /**
     * Adds the i18n translations to the plugin
     * 
     * @return null
     */
    public function loadPluginTextDomain()
    {
        load_plugin_textdomain('lmfwc', false, 'license-manager-for-woocommerce/i18n/');
    }

    /**
     * Init LicenseManagerForWooCommerce when WordPress Initialises.
     * 
     * @return null
     */
    public function init()
    {
        Setup::migrate();

        $this->publicHooks();

        new Crypto();
        new Export();
        new ProductManager();
        new AdminMenus();
        new AdminNotice();
        new Generator();
        new OrderManager();
        new Repositories\ApiKey();
        new Repositories\Generator();
        new Repositories\License();
        new Repositories\PostMeta();
        new Repositories\Posts();
        new Repositories\Users();
        new FormHandler();
        new API\Setup();
    }

    public function registerEmails($emails)
    {
        new Emails\TemplateParts();

        $plugin_emails = array(
            //'LMFWC_Customer_Preorder_Complete' => new \LicenseManagerForWooCommerce\Emails\CustomerPreorderComplete(),
            'LMFWC_Customer_Deliver_License_Keys' => new Emails\CustomerDeliverLicenseKeys()
        );

        return array_merge($emails, $plugin_emails);
    }

    /**
     * Defines all public hooks
     * 
     * @param string $text The new text to be used
     * 
     * @return string
     */
    protected function publicHooks()
    {
        add_filter(
            'lmfwc_license_keys_table_heading',
            function($text) {
                $default = __('Your license key(s)', 'lmfwc');

                if (!$text) {
                    return $default;
                }

                return sanitize_text_field($text);
            },
            10,
            1
        );
    }
}
