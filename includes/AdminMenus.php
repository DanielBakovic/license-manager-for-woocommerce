<?php

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Enums\LicenseStatusEnum;

defined('ABSPATH') || exit;

if (class_exists('AdminMenus', false)) {
    return new AdminMenus();
}

/**
 * Setup menus in WP admin.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class AdminMenus
{
    const LICENSES_PAGE       = 'license_manager';
    const ADD_IMPORT_PAGE     = 'license_manager_add_import';
    const GENERATORS_PAGE     = 'license_manager_generators';
    const ADD_GENERATOR_PAGE  = 'license_manager_generators_add';
    const EDIT_GENERATOR_PAGE = 'license_manager_generators_edit';
    const SETTINGS_PAGE       = 'license_manager_settings';

    /**
     * @var \LicenseManagerForWooCommerce\Crypto
     */
    private $crypto;

    /**
     * Class constructor.
     */
    public function __construct(
        \LicenseManagerForWooCommerce\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        // Plugin pages.
        add_action('admin_menu', array($this, 'createPluginPages'), 9);
        add_action('admin_init', array($this, 'initSettingsAPI'));

        // Screen options
        add_filter('set-screen-option', array($this, 'setScreenOption'), 10, 3);
    }

    public function createPluginPages()
    {
        // Licenses List Page
        add_menu_page(
            __('License Manager', 'lmfwc'),
            __('License Manager', 'lmfwc'),
            'manage_options',
            self::LICENSES_PAGE,
            array($this, 'licensesPage'),
            'dashicons-lock',
            10
        );
        $licenses_hook = add_submenu_page(
            self::LICENSES_PAGE,
            __('License Manager', 'lmfwc'),
            __('Licenses', 'lmfwc'),
            'manage_options',
            self::LICENSES_PAGE,
            array($this, 'licensesPage')
        );
        add_action('load-' . $licenses_hook, array($this, 'licensesPageScreenOptions'));
        // Add/Import Page
        add_submenu_page(
            self::LICENSES_PAGE,
            __('License Manager - Import', 'lmfwc'),
            __('Import', 'lmfwc'),
            'manage_options',
            self::ADD_IMPORT_PAGE,
            array($this, 'licensesAddImportPage')
        );
        // Generators List Page
        $generators_hook = add_submenu_page(
            self::LICENSES_PAGE,
            __('License Manager - Generators', 'lmfwc'),
            __('Generators', 'lmfwc'),
            'manage_options',
            self::GENERATORS_PAGE,
            array($this, 'generatorsPage')
        );
        add_action('load-' . $generators_hook, array($this, 'generatorsPageScreenOptions'));
        // Add Generator Page
        add_submenu_page(
            self::LICENSES_PAGE,
            __('License Manager - Add New Generator', 'lmfwc'),
            __('Add New Generator', 'lmfwc'),
            'manage_options',
            self::ADD_GENERATOR_PAGE,
            array($this, 'generatorsAddPage')
        );
        // Edit Generator Page
        add_submenu_page(
            null,
            __('License Manager - Edit Generator', 'lmfwc'),
            __('Edit Generator', 'lmfwc'),
            'manage_options',
            self::EDIT_GENERATOR_PAGE,
            array($this, 'generatorsEditPage')
        );
        // Settings Page
        add_submenu_page(
            self::LICENSES_PAGE,
            __('License Manager - Settings', 'lmfwc'),
            __('Settings', 'lmfwc'),
            'manage_options',
            self::SETTINGS_PAGE,
            array($this, 'settingsPage')
        );
    }

    public function licensesPageScreenOptions()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Licenses per page',
            'default' => 10,
            'option' => 'licenses_per_page'
        );

        add_screen_option($option, $args);
    }

    public function licensesPage()
    {
        $licenses = new \LicenseManagerForWooCommerce\Lists\LicensesList($this->crypto);

        include LMFWC_TEMPLATES_DIR . 'licenses-page.php';
    }

    public function licensesAddImportPage()
    {
        $products = new \WP_Query(
            array(
                'post_type'      => 'product',
                'posts_per_page' => -1
            )
        );

        include LMFWC_TEMPLATES_DIR . 'licenses-add-import-page.php';
    }

    public function settingsPage()
    {
        include LMFWC_TEMPLATES_DIR . 'settings-page.php';
    }

    public function generatorsPage()
    {
        $generators = new \LicenseManagerForWooCommerce\Lists\GeneratorsList();

        include LMFWC_TEMPLATES_DIR . 'generators-page.php';
    }

    public function generatorsPageScreenOptions()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Generators per page',
            'default' => 10,
            'option' => 'generators_per_page'
        );

        add_screen_option($option, $args);
    }

    public function generatorsAddPage()
    {
        include LMFWC_TEMPLATES_DIR . 'generators-add-new.php';
    }

    public function generatorsEditPage()
    {
        if (!array_key_exists('edit', $_GET) && !array_key_exists('id', $_GET)) {
            return;
        }

        if (!$generator = Database::getGenerator(absint($_GET['id']))) {
           return;
        }

        $products = apply_filters('lmfwc_get_assigned_products', array('generator_id' => absint($_GET['id'])));

        include LMFWC_TEMPLATES_DIR . 'generators-edit.php';
    }

    public function initSettingsAPI()
    {
        new Settings();
    }

    public function setScreenOption($status, $option, $value)
    {
        return $value;
    }

}