<?php
/**
 * LicenseManager for WooCommerce Meta Functions
 *
 * Functions related to license key meta information.
 * Available on both the front-end and admin.
 */

use LicenseManagerForWooCommerce\Models\Resources\LicenseMeta as LicenseMetaResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\LicenseMeta as LicenseMetaResourceRepository;

defined('ABSPATH') || exit;

/**
 *
 *
 * @param int                       $licenseId License Key ID
 * @param string                    $metaKey   Meta key to add
 * @param int|string|array|stdClass $metaValue Meta value to add
 *
 * @return mixed|bool
 */
function lmfwc_add_license_meta($licenseId, $metaKey, $metaValue)
{
    $license = LicenseResourceRepository::instance()->find($licenseId);

    if (!$license) {
        return false;
    }

    /** @var LicenseMetaResourceModel $licenseMeta */
    $licenseMeta = LicenseMetaResourceRepository::instance()->insert(
        array(
            'license_id' => $licenseId,
            'meta_key'   => $metaKey,
            'meta_value' => maybe_serialize($metaValue)
        )
    );

    if (!$licenseMeta) {
        return false;
    }

    return $licenseMeta->getMetaValue();
}

/**
 * Retrieves one or multiple license meta values
 *
 * @param int    $licenseId License Key ID
 * @param string $metaKey   Meta key to search by
 * @param bool   $single    Return a single or multiple rows (if found)
 *
 * @return mixed|mixed[]
 */
function lmfwc_get_license_meta($licenseId, $metaKey, $single = false)
{
    if ($single) {
        /** @var LicenseMetaResourceModel $licenseMeta */
        $licenseMeta = LicenseMetaResourceRepository::instance()->findBy(
            array(
                'license_id' => $licenseId,
                'meta_key' => $metaKey
            )
        );

        return $licenseMeta->getMetaValue();
    }

    $licenseMetas = LicenseMetaResourceRepository::instance()->findAllBy(
        array(
            'license_id' => $licenseId,
            'meta_key' => $metaKey
        )
    );
    $result = array();

    /** @var LicenseMetaResourceModel $licenseMeta */
    foreach ($licenseMetas as $licenseMeta) {
        $result[] = $licenseMeta->getMetaValue();
    }

    return $result;
}

/**
 * Updates existing license meta entries.
 *
 * @param int    $licenseId
 * @param string $metaKey
 * @param mixed  $metaValue
 * @param mixed  $previousValue
 *
 * @return bool
 */
function lmfwc_update_license_meta($licenseId, $metaKey, $metaValue, $previousValue = null)
{
    $selectQuery = array(
        'license_id' => $licenseId,
        'meta_key'   => $metaKey
    );
    $updateQueryCondition = array(
        'license_id' => $licenseId,
        'meta_key'   => $metaKey
    );
    $updateQueryData = array(
        'license_id' => $licenseId,
        'meta_key'   => $metaKey,
        'meta_value' => $metaValue
    );

    if ($previousValue !== null) {
        $selectQuery['meta_value'] = $previousValue;
        $updateQueryCondition['meta_value'] = $previousValue;
    }

    $metaLicense = LicenseMetaResourceRepository::instance()->findBy($selectQuery);

    if (!$metaLicense) {
        return false;
    }

    $updateCount = LicenseMetaResourceRepository::instance()->updateBy($updateQueryCondition, $updateQueryData);

    if (!$updateCount) {
        return false;
    }

    return true;
}

