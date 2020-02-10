<?php

namespace LicenseManagerForWooCommerce\Settings;

defined('ABSPATH') || exit;

class General
{
    /**
     * @var array
     */
    private $settings;

    /**
     * General constructor.
     */
    public function __construct()
    {
        $this->settings = (array)get_option('lmfwc_settings_general');

        /**
         * @see https://developer.wordpress.org/reference/functions/register_setting/#parameters
         */
        $args = array();

        // Register the initial settings group.
        register_setting('lmfwc_settings_group_general', 'lmfwc_settings_general', $args);

        // Initialize the individual sections
        $this->initSectionLicenseKeys();
        $this->initSectionMyAccount();
        $this->initSectionAPI();
    }

    /**
     * Initializes the "lmfwc_license_keys" section.
     *
     * @return void
     */
    private function initSectionLicenseKeys()
    {
        // Add the settings sections.
        add_settings_section(
            'license_keys_section',
            __('License keys', 'lmfwc'),
            null,
            'lmfwc_license_keys'
        );

        // lmfwc_security section fields.
        add_settings_field(
            'lmfwc_hide_license_keys',
            __('Obscure licenses', 'lmfwc'),
            array($this, 'fieldHideLicenseKeys'),
            'lmfwc_license_keys',
            'license_keys_section'
        );

        add_settings_field(
            'lmfwc_auto_delivery',
            __('Automatic delivery', 'lmfwc'),
            array($this, 'fieldAutoDelivery'),
            'lmfwc_license_keys',
            'license_keys_section'
        );

        add_settings_field(
            'lmfwc_allow_duplicates',
            __('Allow duplicates', 'lmfwc'),
            array($this, 'fieldAllowDuplicates'),
            'lmfwc_license_keys',
            'license_keys_section'
        );

        add_settings_field(
            'lmfwc_enable_stock_manager',
            __('Automatic stock', 'lmfwc'),
            array($this, 'fieldEnableStockManager'),
            'lmfwc_license_keys',
            'license_keys_section'
        );
    }

    /**
     * Initializes the "lmfwc_my_account" section.
     *
     * @return void
     */
    private function initSectionMyAccount()
    {
        // Add the settings sections.
        add_settings_section(
            'my_account_section',
            __('My account', 'lmfwc'),
            null,
            'lmfwc_my_account'
        );

        // lmfwc_my_account section fields.
        add_settings_field(
            'lmfwc_enable_my_account_endpoint',
            __('Enable "License keys"', 'lmfwc'),
            array($this, 'fieldEnableMyAccountEndpoint'),
            'lmfwc_my_account',
            'my_account_section'
        );

        add_settings_field(
            'lmfwc_allow_users_to_activate',
            __('User activation', 'lmfwc'),
            array($this, 'fieldAllowUsersToActivate'),
            'lmfwc_my_account',
            'my_account_section'
        );

        add_settings_field(
            'lmfwc_allow_users_to_deactivate',
            __('User deactivation', 'lmfwc'),
            array($this, 'fieldAllowUsersToDeactivate'),
            'lmfwc_my_account',
            'my_account_section'
        );
    }

    /**
     * Initializes the "lmfwc_rest_api" section.
     *
     * @return void
     */
    private function initSectionAPI()
    {
        // Add the settings sections.
        add_settings_section(
            'lmfwc_rest_api_section',
            __('REST API', 'lmfwc'),
            null,
            'lmfwc_rest_api'
        );

        add_settings_field(
            'lmfwc_disable_api_ssl',
            __('API & SSL', 'lmfwc'),
            array($this, 'fieldEnableApiOnNonSsl'),
            'lmfwc_rest_api',
            'lmfwc_rest_api_section'
        );

        add_settings_field(
            'lmfwc_enabled_api_routes',
            __('Enable/disable API routes', 'lmfwc'),
            array($this, 'fieldEnabledApiRoutes'),
            'lmfwc_rest_api',
            'lmfwc_rest_api_section'
        );
    }

    /**
     * Callback for the "hide_license_keys" field.
     *
     * @return void
     */
    public function fieldHideLicenseKeys()
    {
        $field = 'lmfwc_hide_license_keys';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf('<span>%s</span>', __('Hide license keys in the admin dashboard.', 'lmfwc'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('All license keys will be hidden and only displayed when the \'Show\' action is clicked.', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_auto_delivery" field.
     *
     * @return void
     */
    public function fieldAutoDelivery()
    {
        $field = 'lmfwc_auto_delivery';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Automatically send license keys when an order is set to \'Complete\'.', 'lmfwc')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('If this setting is off, you must manually send out all license keys for completed orders.', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_allow_duplicates" field.
     *
     * @return void
     */
    public function fieldAllowDuplicates()
    {
        $field = 'lmfwc_allow_duplicates';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow duplicate license keys inside the licenses database table.', 'lmfwc')
        );
        $html .= '</label>';

        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_enable_stock_manager" field.
     *
     * @return void
     */
    public function fieldEnableStockManager()
    {
        $field = 'lmfwc_enable_stock_manager';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Enable automatic stock management for WooCommerce products.', 'lmfwc')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('When adding, updating, or deleting license keys the plugin will automatically update the stock of
            WooCommerce products with the "Manage stock?" option enabled.', 'lmfwc'),
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_enable_my_account_endpoint" field.
     *
     * @return void
     */
    public function fieldEnableMyAccountEndpoint()
    {
        $field = 'lmfwc_enable_my_account_endpoint';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Display the \'License keys\' section inside WooCommerce\'s \'My Account\'.', 'lmfwc')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('You might need to save your permalinks after enabling this option.', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_allow_users_to_activate" field.
     */
    public function fieldAllowUsersToActivate()
    {
        $field = 'lmfwc_allow_users_to_activate';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow users to activate their license keys.', 'lmfwc')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('The option will be visible from the \'License keys\' section inside WooCommerce\'s \'My Account\'', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_allow_users_to_deactivate" field.
     */
    public function fieldAllowUsersToDeactivate()
    {
        $field = 'lmfwc_allow_users_to_deactivate';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow users to deactivate their license keys.', 'lmfwc')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('The option will be visible from the \'License keys\' section inside WooCommerce\'s \'My Account\'', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_disable_api_ssl" field.
     *
     * @return void
     */
    public function fieldEnableApiOnNonSsl()
    {
        $field = 'lmfwc_disable_api_ssl';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Enable the plugin API routes over insecure HTTP connections.', 'lmfwc')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('This should only be activated for development purposes.', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_enabled_api_routes" field.
     *
     * @return void
     */
    public function fieldEnabledApiRoutes()
    {
        $field = 'lmfwc_enabled_api_routes';
        $value = array();
        $routes = array(
            array(
                'id'         => '010',
                'name'       => 'v2/licenses',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '011',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '012',
                'name'       => 'v2/licenses',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '013',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'PUT',
                'deprecated' => false,
            ),
            array(
                'id'         => '014',
                'name'       => 'v2/licenses/activate/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '015',
                'name'       => 'v2/licenses/deactivate/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '016',
                'name'       => 'v2/licenses/validate/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '017',
                'name'       => 'v2/generators',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '018',
                'name'       => 'v2/generators/{id}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '019',
                'name'       => 'v2/generators',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '020',
                'name'       => 'v2/generators/{id}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
        );
        $classList = array(
            'GET'  => 'text-success',
            'PUT'  => 'text-primary',
            'POST' => 'text-primary'
        );

        if (array_key_exists($field, $this->settings)) {
            $value = $this->settings[$field];
        }

        $html = '<fieldset>';

        foreach ($routes as $route) {
            $checked = false;

            if (array_key_exists($route['id'], $value) && $value[$route['id']] === '1') {
                $checked = true;
            }

            $html .= sprintf('<label for="%s-%s">', $field, $route['id']);
            $html .= sprintf(
                '<input id="%s-%s" type="checkbox" name="lmfwc_settings_general[%s][%s]" value="1" %s>',
                $field,
                $route['id'],
                $field,
                $route['id'],
                checked(true, $checked, false)
            );
            $html .= sprintf('<code><b class="%s">%s</b> - %s</code>', $classList[$route['method']], $route['method'], $route['name']);

            if (true === $route['deprecated']) {
                $html .= sprintf(
                    '<code class="text-info"><b>%s</b></code>',
                    strtoupper(__('Deprecated', 'lmfwc'))
                );
            }

            $html .= '</label>';
            $html .= '<br>';
        }

        $html .= sprintf(
            '<p class="description">%s</p>',
            sprintf(
                __('The complete <b>API documentation</b> can be found <a href="%s" target="_blank" rel="noopener">here</a>.', 'lmfwc'),
                'https://www.licensemanager.at/docs/rest-api/'
            )
        );
        $html .= '</fieldset>';

        echo $html;
    }
}