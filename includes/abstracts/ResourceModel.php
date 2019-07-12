<?php

namespace LicenseManagerForWooCommerce\Abstracts;

defined('ABSPATH') || exit;

abstract class ResourceModel
{
    public function toArray()
    {
        return get_object_vars($this);
    }
}