<?php

namespace LicenseManagerForWooCommerce\Interfaces;

use stdClass;
use LicenseManagerForWooCommerce\Abstracts\ResourceModel as AbstractResourceModel;

defined('ABSPATH') || exit;

interface ResourceRepository
{
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function insert($data);

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function findBy($query);

    /**
     * @return mixed
     */
    public function findAll();

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function findAllBy($query);

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed
     */
    public function update($id, $data);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function delete($ids);

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function deleteBy($query);

    /**
     * @return mixed
     */
    public function count();

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function countBy($query);

    /**
     * @param string $queryString
     *
     * @return mixed
     */
    public function query($queryString);

    /**
     * @return mixed
     */
    public function truncate();

    /**
     * @param stdClass $dataObject
     *
     * @return AbstractResourceModel
     */
    public function createResourceModel($dataObject);
}