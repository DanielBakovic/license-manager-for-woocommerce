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
            '_lima_hide_licence_keys',
            __('Hide or Show Licences', 'lima'),
            array($this, 'limaFieldHideLicenceKeys'),
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
     * Callback for the "_lima_hide_licence_keys" field.
     *
     * @since 1.0.0
     *
     */
    public function limaFieldHideLicenceKeys()
    {
        $field = '_lima_hide_licence_keys';
        (array_key_exists('_lima_hide_licence_keys', $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="_lima_settings[%s]" value="1"' . checked(true, $value, false) . '/>',
            $field,
            $field
        );
        $html .= sprintf('<span>%s</span>', __('Hide licence keys in the admin dashboard.', 'lima'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description" id="tagline-description">%s</p>',
            __('All licences will be hidden and only displayed when the \'Show\' action is clicked.', 'lima')
        );
        $html .= '</fieldset>';

        echo $html;
    }

    /**
     * Helper function which determines whether licence keys in the admin dashboard are hidden or not.
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    public static function hideLicenceKeys()
    {
        $settings = (array)get_option('_lima_settings');
        $field    = "_lima_hide_licence_keys";
        (array_key_exists('_lima_hide_licence_keys', $settings)) ? $value = true : $value = false;

        return $value;
    }
}