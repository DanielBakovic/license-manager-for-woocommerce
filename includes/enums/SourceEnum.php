<?php

namespace LicenseManager\Enums;

defined('ABSPATH') || exit;

/**
 * Source Enumerator.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class SourceEnum
{
    const __default = -1;

    const GENERATOR = 1;
    const IMPORT = 2;
}
