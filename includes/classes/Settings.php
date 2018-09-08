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
    /**
     * Settings Constructor.
     */
    public function __construct()
    {
        $this->init();
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
            '_lima_encrypt_license_keys',
            __('Encryption', 'lima'),
            array($this, 'limaFieldEncryptLicenseKeys'),
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
     * Callback for the "_lima_encrypt_license_keys" field.
     *
     * @since 1.0.0
     *
     */
    public function limaFieldEncryptLicenseKeys()
    {
        $settings = (array)get_option('_lima_settings');
        $field    = "_lima_encrypt_license_keys";
        (array_key_exists('_lima_encrypt_license_keys', $settings)) ? $value = true : $value = false;

        $html = "<fieldset><label for='$field'>";
        $html .= "<input id='$field' type='checkbox' name='_lima_settings[$field]' value='1' ". checked(true, $value, false) . "/>" . __('Encrypt keys before saving in the database.', 'lima') . "</label>";
        $html .= "<p class=\"description\" id=\"tagline-description\">" . __('It\'s advised to keep this setting on, as all the license keys will be stored in plain-text in the database otherwise.', 'lima') . "</p></fieldset>";

        echo $html;
    }

    /**
     * Helper function which determines whether encryption is enabled or not.
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    public static function useEncryption()
    {
        $settings = (array)get_option('_lima_settings');
        $field    = "_lima_encrypt_license_keys";
        (array_key_exists('_lima_encrypt_license_keys', $settings)) ? $value = true : $value = false;

        return $value;
    }
}