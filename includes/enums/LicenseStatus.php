<?php

namespace LicenseManagerForWooCommerce\Enums;

defined('ABSPATH') || exit;

/**
 * License Status Enumerator.
 *
 * @version 1.1.0
 * @since   1.0.0
 */
abstract class LicenseStatus
{
    const SOLD      = 1;
    const DELIVERED = 2;
    const ACTIVE    = 3;
    const INACTIVE  = 4;

    public static $status = array(
        self::SOLD,
        self::DELIVERED,
        self::ACTIVE,
        self::INACTIVE
    );

    public static $enum_array = array(
        'sold',
        'delivered',
        'active',
        'inactive'
    );

    public static $values = array(
        'sold' => self::SOLD,
        'delivered' => self::DELIVERED,
        'active' => self::ACTIVE,
        'inactive' => self::INACTIVE
    );

    public static function getExportLabel($status)
    {
        $labels = array(
            self::SOLD => 'SOLD',
            self::DELIVERED => 'DELIVERED',
            self::ACTIVE => 'ACTIVE',
            self::INACTIVE => 'INACTIVE'
        );

        return $labels[$status];
    }
}
