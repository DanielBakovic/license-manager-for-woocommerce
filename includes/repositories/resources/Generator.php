<?php

namespace LicenseManagerForWooCommerce\Repositories\Resources;

use stdClass;
use LicenseManagerForWooCommerce\Abstracts\ResourceRepository as AbstractResourceRepository;
use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as ResourceRepositoryInterface;
use LicenseManagerForWooCommerce\Models\Resources\Generator as GeneratorResourceModel;

defined('ABSPATH') || exit;

class Generator extends AbstractResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @var string
     */
    const TABLE = 'lmfwc_generators';

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
     * @return mixed|GeneratorResourceModel
     */
    public function createResourceModel($dataObject)
    {
        return new GeneratorResourceModel($dataObject);
    }
}