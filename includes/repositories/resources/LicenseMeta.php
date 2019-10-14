<?php

namespace LicenseManagerForWooCommerce\Repositories\Resources;

use stdClass;
use LicenseManagerForWooCommerce\Abstracts\ResourceRepository as AbstractResourceRepository;
use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as ResourceRepositoryInterface;
use LicenseManagerForWooCommerce\Models\Resources\LicenseMeta as LicenseMetaResourceModel;

defined('ABSPATH') || exit;

class LicenseMeta extends AbstractResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @var string
     */
    const TABLE = 'lmfwc_licenses_meta';

    /**
     * Country constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->table      = $wpdb->prefix . self::TABLE;
        $this->primaryKey = 'meta_id';
    }

    /**
     * @param stdClass $dataObject
     *
     * @return mixed|LicenseMetaResourceModel
     */
    public function createResourceModel($dataObject)
    {
        return new LicenseMetaResourceModel($dataObject);
    }
}