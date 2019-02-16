<?php

namespace LicenseManager\Enums;

defined('ABSPATH') || exit;

/**
 * License Status Enumerator.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class LicenseStatusEnum
{
    const __default = -1;

    const SOLD      = 1;
    const DELIVERED = 2;
    const ACTIVE    = 3;
    const INACTIVE  = 4;

    public static $statuses = array(
        self::SOLD,
        self::DELIVERED,
        self::ACTIVE,
        self::INACTIVE
    );
}
