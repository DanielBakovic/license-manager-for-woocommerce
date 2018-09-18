<?php

namespace LicenseManager\Classes\Abstracts;

/**
 * License Status Enumerator.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

abstract class LicenseStatusEnum
{
    const __default = null;

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
