<?php
/**
 * LicenseManager for WooCommerce Core Functions
 *
 * General core functions available on both the front-end and admin.
 */

use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;

defined('ABSPATH') || exit;

/**
 * Checks if a license key already exists inside the database table.
 *
 * @param string   $licenseKey
 * @param null|int $licenseKeyId
 *
 * @return bool
 */
function lmfwc_duplicate($licenseKey, $licenseKeyId = null) {
    $duplicate = false;

    $query = array(
        'hash' => apply_filters('lmfwc_hash', $licenseKey)
    );

    if ($licenseKeyId !== null && is_numeric($licenseKeyId)) {
        $query['id'] = $licenseKeyId;
    }

    if (LicenseResourceRepository::instance()->findBy($query)) {
        $duplicate = true;
    }

    return $duplicate;
}
add_filter('lmfwc_duplicate', 'lmfwc_duplicate', 10, 2);

