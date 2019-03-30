<?php

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;

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
    private $tab_whitelist;
    private $section_whitelist;

    const LICENSES_PAGE   = 'lmfwc_licenses';
    const GENERATORS_PAGE = 'lmfwc_generators';
    const SETTINGS_PAGE   = 'lmfwc_settings';

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->tab_whitelist = array(
            'general',
            'rest_api'
        );
        $this->section_whitelist = array(
            ''
        );

        // Plugin pages.
        add_action('admin_menu', array($this, 'createPluginPages'), 9);
        add_action('admin_init', array($this, 'initSettingsAPI'));

        // Screen options
        add_filter('set-screen-option', array($this, 'setScreenOption'), 10, 3);

        // Footer text
        add_filter('admin_footer_text', array($this, 'adminFooterText'), 1);
    }

    public function getPluginPageIDs()
    {
        return array(
            'toplevel_page_lmfwc_licenses',
            'license-manager_page_lmfwc_generators',
            'license-manager_page_lmfwc_settings'
        );
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
            'label' => __('Licenses per page', 'lmfwc'),
            'default' => 10,
            'option' => 'lmfwc_licenses_per_page'
        );

        add_screen_option($option, $args);
    }

    public function licensesPage()
    {

        $licenses = new \LicenseManagerForWooCommerce\Lists\LicensesList();

        $action = $this->getCurrentAction($default = 'list');
        $add_license_url = admin_url(
            sprintf(
                'admin.php?page=%s&action=add&_wpnonce=%s',
                self::LICENSES_PAGE,
                wp_create_nonce('add')
            )
        );

        if ($action === 'add'
            || $action === 'edit'
            || $action === 'activate'
            || $action === 'deactivate'
            || $action === 'delete'
        ) {
            $products = new \WP_Query(
                array(
                    'post_type'      => 'product',
                    'posts_per_page' => -1
                )
            );
        } 

        if ($action === 'edit') {
            $status_whitelist = array(
                LicenseStatusEnum::ACTIVE,
                LicenseStatusEnum::INACTIVE
            );
            $license_id = absint($_GET['id']);
            $license_row = apply_filters('lmfwc_get_license_key', $license_id);
            $license_status = absint($license_row['status']);
            $license_key = apply_filters('lmfwc_decrypt', $license_row['license_key']);
            $valid_for = $license_row['valid_for'];
            $activated = ($license_row['status'] == LicenseStatusEnum::ACTIVE) ? true : false;
            $times_activated_max = $license_row['times_activated_max'];
            $product_id = absint($license_row['product_id']);
            $license_source = absint($license_row['source']);
            $status_active = LicenseStatusEnum::ACTIVE;
            $status_inactive = LicenseStatusEnum::INACTIVE;

            if (!$license_id) {
                wp_die(__('Invalid License Key ID', 'lmfwc'));
            }

            if (!in_array($license_status, $status_whitelist)) {
                wp_die(__('This License Key can no longer be edited', 'lmfwc'));
            }
        }

        include LMFWC_TEMPLATES_DIR . 'licenses-page.php';
    }

    public function settingsPage()
    {
        $tab = $this->getCurrentTab();
        $section = $this->getCurrentSection();

        $url_general = admin_url(sprintf('admin.php?page=%s&tab=general', self::SETTINGS_PAGE));
        $url_rest_api = admin_url(sprintf('admin.php?page=%s&tab=rest_api', self::SETTINGS_PAGE));

        if ($tab == 'rest_api') {
            if (isset($_GET['create_key'])) {
                $action = 'create';
            } elseif (isset($_GET['edit_key'])) {
                $action = 'edit';
            } elseif (isset($_GET['show_key'])) {
                $action = 'show';
            } else {
                $action = 'list';
            }

            if ($action === 'create' || $action === 'edit') {
                $key_id      = isset($_GET['edit_key']) ? absint($_GET['edit_key']) : 0;
                $key_data    = apply_filters('lmfwc_get_api_key', $key_id);
                $user_id     = (int)$key_data['user_id'];
                $permissions = array(
                    'read'       => __('Read', 'lmfwc'),
                    'write'      => __('Write', 'lmfwc'),
                    'read_write' => __('Read/Write', 'lmfwc'),
                );
                $date = sprintf(
                    esc_html__('%1$s at %2$s', 'lmfwc'),
                    date_i18n(wc_date_format(), strtotime($key_data['last_access'])),
                    date_i18n(wc_time_format(), strtotime($key_data['last_access']))
                );

                if ($key_id && $user_id && ! current_user_can('edit_user', $user_id)) {
                    if (get_current_user_id() !== $user_id) {
                        wp_die(esc_html__('You do not have permission to edit this API Key', 'lmfwc'));
                    }
                }
            } elseif ($action === 'list') {
                $keys = new \LicenseManagerForWooCommerce\Lists\APIKeyList();
            } elseif ($action === 'show') {
                $key_data = get_transient('lmfwc_api_key');
                delete_transient('lmfwc_api_key');
            }

            // Add screen option.
            add_screen_option(
                'per_page', array(
                    'default' => 10,
                    'option'  => 'lmfwc_keys_per_page',
                )
            );
        }

        include LMFWC_TEMPLATES_DIR . 'settings-page.php';
    }

    public function generatorsPage()
    {
        $generators = new \LicenseManagerForWooCommerce\Lists\GeneratorsList();


        $action = $this->getCurrentAction($default = 'list');

        if ($action == 'list') {
            $add_generator_url = wp_nonce_url(
                sprintf(
                    admin_url('admin.php?page=%s&action=add'),
                    self::GENERATORS_PAGE
                ),
                'lmwfc_add_generator'
            );
        }

        if ($action == 'edit') {
            if (!array_key_exists('edit', $_GET) && !array_key_exists('id', $_GET)) {
                return;
            }

            if (!$generator = apply_filters('lmfwc_get_generator', $_GET['id'])) {
               return;
            }

            $products = apply_filters('lmfwc_get_assigned_products', $_GET['id']);

        }

        include LMFWC_TEMPLATES_DIR . 'generators-page.php';
    }

    public function generatorsPageScreenOptions()
    {
        $option = 'per_page';
        $args = array(
            'label' => __('Generators per page', 'lmfwc'),
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

        if (!$generator = apply_filters('lmfwc_get_generator', $_GET['id'])) {
           return;
        }

        $products = apply_filters('lmfwc_get_assigned_products', $_GET['id']);

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

    public function adminFooterText($footer_text)
    {
        if (!current_user_can('manage_options') || !function_exists('wc_get_screen_ids')) {
            return $footer_text;
        }

        $current_screen = get_current_screen();

        // Check to make sure we're on a WooCommerce admin page.
        if (isset($current_screen->id) && in_array($current_screen->id, $this->getPluginPageIDs())) {
            // Change the footer text
            $footer_text = sprintf(
                __( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'lmfwc' ),
                sprintf( '<strong>%s</strong>', esc_html__( 'License Manager for WooCommerce', 'lmfwc' ) ),
                '<a href="https://wordpress.org/support/plugin/license-manager-for-woocommerce/reviews/?rate=5#new-post" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'lmfwc' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
            );
        }

        return $footer_text;
    }

    protected function getCurrentTab()
    {
        if (isset($_GET['tab']) && in_array($_GET['tab'], $this->tab_whitelist)) {
            $tab = sanitize_text_field($_GET['tab']);
        } else {
            $tab = 'general';
        }

        return $tab;
    }

    protected function getCurrentSection()
    {
        $section = '';

        return $section;
    }

    /**
     * Returns the string value of the "action" GET parameter
     * 
     * @return string|null
     */
    protected function getCurrentAction($default)
    {
        $action = $default;

        if (!isset($_GET['action']) || !is_string($_GET['action'])) {
            return $action;
        }

        return sanitize_text_field($_GET['action']);
    }

}