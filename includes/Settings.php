<?php

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce Settings.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class Settings
{
    private $settings;

    /**
     * Settings Constructor.
     */
    public function __construct()
    {
        $this->init();
        $this->settings = (array)get_option('lmfwc_settings');
    }

    /**
     * Initialize the WordPress Settings API.
     *
     * @since 1.0.0
     */
    private function init()
    {
        // Register the initial settings group.
        register_setting('lmfwc_settings_group', 'lmfwc_settings');

        // Add the settings sections.
        add_settings_section('lmfwc_security', __('Security', 'lmfwc'), array($this, 'lmfwcSecurityHeader'), 'lmfwc');

        // lmfwc_security section fields.
        add_settings_field(
            'lmfwc_hide_license_keys',
            __('Obscure Licenses', 'lmfwc'),
            array($this, 'lmfwcFieldHideLicenseKeys'),
            'lmfwc',
            'lmfwc_security'
        );
        add_settings_field(
            'lmfwc_disable_api_ssl',
            __('API & SSL', 'lmfwc'),
            array($this, 'lmfwcFieldEnableApiOnNonSsl'),
            'lmfwc',
            'lmfwc_security'
        );
        add_settings_field(
            'lmfwc_auto_delivery',
            __('Automatic Delivery', 'lmfwc'),
            array($this, 'lmfwcFieldAutoDelivery'),
            'lmfwc',
            'lmfwc_security'
        );
    }

    /**
     * Callback for the "lmfwc_security" section.
     *
     * @since 1.0.0
     */
    public function lmfwcSecurityHeader()
    {
        _e('Please read the description of each field before making any changes.', 'lmfwc');
    }

    /**
     * Callback for the "lmfwc_hide_license_keys" field.
     *
     * @since 1.0.0
     */
    public function lmfwcFieldHideLicenseKeys()
    {
        $field = 'lmfwc_hide_license_keys';
        (array_key_exists('lmfwc_hide_license_keys', $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings[%s]" value="1"' . checked(true, $value, false) . '/>',
            $field,
            $field
        );
        $html .= sprintf('<span>%s</span>', __('Hide license keys in the admin dashboard.', 'lmfwc'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('All licenses will be hidden and only displayed when the \'Show\' action is clicked.', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Callback for the "lmfwc_disable_api_ssl" field.
     *
     * @since 1.0.0
     */
    public function lmfwcFieldEnableApiOnNonSsl()
    {
        $field = 'lmfwc_disable_api_ssl';
        (array_key_exists('lmfwc_disable_api_ssl', $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings[%s]" value="1"' . checked(true, $value, false) . '/>',
            $field,
            $field
        );
        $html .= sprintf('<span>%s</span>', __('Enable the plugin API endpoints over insecure HTTP connections.', 'lmfwc'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('This should only be activated for development purposes.', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    public function lmfwcFieldAutoDelivery()
    {
        $field = 'lmfwc_auto_delivery';
        (array_key_exists('lmfwc_auto_delivery', $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings[%s]" value="1"' . checked(true, $value, false) . '/>',
            $field,
            $field
        );
        $html .= sprintf('<span>%s</span>', __('Automatically send license keys when an order is set to \'Complete\'.', 'lmfwc'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('If this setting is off, you must manually send out all license keys for completed orders.', 'lmfwc')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Helper function to get a setting by name.
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    public static function get($field)
    {
        $settings = (array)get_option('lmfwc_settings');
        (array_key_exists($field, $settings)) ? $value = true : $value = false;

        return $value;
    }
}