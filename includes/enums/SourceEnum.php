<?php

namespace LicenseManager\Enums;

/**
 * Source Enumerator.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

abstract class SourceEnum
{
    const __default = null;

    const GENERATOR = 1;
    const IMPORT = 2;
}
