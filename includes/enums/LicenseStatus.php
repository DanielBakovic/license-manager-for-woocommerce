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
    const __default = -1;

    const SOLD      = 1;
    const DELIVERED = 2;
    const ACTIVE    = 3;
    const INACTIVE  = 4;
    const USED      = 5;

    public static $status = array(
        self::SOLD,
        self::DELIVERED,
        self::ACTIVE,
        self::INACTIVE,
        self::USED
    );

    public static $enum_array = array(
        'sold',
        'delivered',
        'active',
        'inactive',
        'used'
    );

    public static $values = array(
        'sold' => self::SOLD,
        'delivered' => self::DELIVERED,
        'active' => self::ACTIVE,
        'inactive' => self::INACTIVE,
        'used' => self::USED
    );
}
