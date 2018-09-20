<?php

namespace LicenseManager\Classes;

/**
 * LicenseManager Settings.
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Settings class.
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
        $this->settings = (array)get_option('_lima_settings');
    }

    /**
     * Initialize the WordPress Settings API.
     *
     * @since 1.0.0
     *
     */
    private function init()
    {
        // Register the initial settings group.
        register_setting('_lima_settings_group', '_lima_settings');

        // Add the settings sections.
        add_settings_section('_lima_security', __('Security', 'lima'), array($this, 'limaSecurityHeader'), '_lima');

        // _lima_security section fields.
        add_settings_field(
            '_lima_hide_license_keys',
            __('Hide or Show Licenses', 'lima'),
            array($this, 'limaFieldHideLicenseKeys'),
            '_lima',
            '_lima_security'
        );
        add_settings_field(
            '_lima_auto_delivery',
            __('Automatic Delivery', 'lima'),
            array($this, 'limaFieldAutoDelivery'),
            '_lima',
            '_lima_security'
        );
    }

    /**
     * Callback for the "_lima_security" section.
     *
     * @since 1.0.0
     *
     */
    public function limaSecurityHeader()
    {
        _e('Please do not change any settings before carefully reading the comments under each field.', 'lima');
    }

    /**
     * Callback for the "_lima_hide_license_keys" field.
     *
     * @since 1.0.0
     *
     */
    public function limaFieldHideLicenseKeys()
    {
        $field = '_lima_hide_license_keys';
        (array_key_exists('_lima_hide_license_keys', $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="_lima_settings[%s]" value="1"' . checked(true, $value, false) . '/>',
            $field,
            $field
        );
        $html .= sprintf('<span>%s</span>', __('Hide license keys in the admin dashboard.', 'lima'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description" id="tagline-description">%s</p>',
            __('All licenses will be hidden and only displayed when the \'Show\' action is clicked.', 'lima')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    public function limaFieldAutoDelivery()
    {
        $field = '_lima_auto_delivery';
        (array_key_exists('_lima_auto_delivery', $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="_lima_settings[%s]" value="1"' . checked(true, $value, false) . '/>',
            $field,
            $field
        );
        $html .= sprintf('<span>%s</span>', __('Automatically send license keys when an order is set to \'Complete\'.', 'lima'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description" id="tagline-description">%s</p>',
            __('If this setting is off, you must manually send out all license keys for completed orders.', 'lima')
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
        $settings = (array)get_option('_lima_settings');
        (array_key_exists($field, $settings)) ? $value = true : $value = false;

        return $value;
    }
}