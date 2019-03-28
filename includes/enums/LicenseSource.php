<?php

namespace LicenseManagerForWooCommerce\Enums;

defined('ABSPATH') || exit;

/**
 * Source Enumerator.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class LicenseSource
{
    const __default = -1;

    const GENERATOR = 1;
    const IMPORT = 2;
    const API = 3;

    public static $sources = array(
        self::GENERATOR,
        self::IMPORT,
        self::API
    );

    public static function getExportLabel($source)
    {
        $labels = array(
            self::GENERATOR => 'GENERATOR',
            self::IMPORT => 'IMPORT',
            self::API => 'API'
        );

        return $labels[$source];
    }
}
