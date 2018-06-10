<?php

namespace LicenseManager\Classes;

/**
 * LicenseManager setup
 *
 * @package LicenseManager
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Main LicenseManager Class.
 *
 * @class LicenseManager
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
        $this->init();
        $this->initHooks();
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
        $this->define('ABSPATH_LENGTH', strlen(ABSPATH));
        $this->define('LM_ABSPATH', dirname(LM_PLUGIN_FILE) . '/' );
        $this->define('LM_PLUGIN_BASENAME', plugin_basename(LM_PLUGIN_FILE));
        $this->define('LM_VERSION', $this->version);
        $this->define('LM_LOG_DIR', LM_ABSPATH . 'logs/');
        $this->define('LM_TEMPLATES_DIR', LM_ABSPATH . 'templates/');
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
        register_activation_hook(LM_PLUGIN_FILE, array('\LicenseManager\Classes\Setup', 'install'));
        register_deactivation_hook(LM_PLUGIN_FILE, array('\LicenseManager\Classes\Setup', 'uninstall'));
    }

    /**
     * Init LicenseManager when WordPress Initialises.
     */
    public function init()
    {
        new AdminMenus();
        new Generator();
        new OrderManager();
        new Database();
        new FormHandler();
    }

}
