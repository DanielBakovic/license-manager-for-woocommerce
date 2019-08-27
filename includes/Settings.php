<?php

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

class Settings
{
    /**
     * Settings Constructor.
     */
    public function __construct()
    {
        // Initialize the settings classes
        new Settings\General();
    }

    /**
     * Helper function to get a setting by name.
     *
     * @param string $field
     *
     * @return bool
     */
    public static function get($field)
    {
        $settings = (array)get_option('lmfwc_settings');
        (array_key_exists($field, $settings)) ? $value = true : $value = false;

        return $value;
    }
}