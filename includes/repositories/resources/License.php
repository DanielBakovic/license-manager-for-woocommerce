<?php

namespace LicenseManagerForWooCommerce\Repositories\Resources;

use stdClass;
use LicenseManagerForWooCommerce\Abstracts\ResourceRepository as AbstractResourceRepository;
use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as ResourceRepositoryInterface;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;

defined('ABSPATH') || exit;

class License extends AbstractResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @var string
     */
    const TABLE = 'lmfwc_licenses';

    /**
     * Country constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->table      = $wpdb->prefix . self::TABLE;
        $this->primaryKey = 'id';
    }

    /**
     * @param stdClass $dataObject
     *
     * @return mixed|LicenseResourceModel
     */
    public function createResourceModel($dataObject)
    {
        return new LicenseResourceModel($dataObject);
    }
}