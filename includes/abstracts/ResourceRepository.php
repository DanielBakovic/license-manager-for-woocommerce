<?php

namespace LicenseManagerForWooCommerce\Abstracts;

use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as RepositoryInterface;

defined('ABSPATH') || exit;

abstract class ResourceRepository extends Singleton implements RepositoryInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * Adds a new entry to the table.
     *
     * @param array $data
     *
     * @return mixed|void
     */
    public function insert($data)
    {
        global $wpdb;

        $meta = array(
            'created_at' => gmdate('Y-m-d H:i:s'),
            'created_by' => get_current_user_id()
        );

        $insert = $wpdb->insert($this->table, array_merge($data, $meta));

        if (!$insert) {
            return false;
        }

        return $this->find($wpdb->insert_id);
    }

    /**
     * Retrieves a single table row by its ID.
     *
     * @param int $id
     *
     * @return bool|ResourceModel
     */
    public function find($id)
    {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = %d;",
                $id
            )
        );

        if (!$result) {
            return false;
        }

        return $this->createResourceModel($result);
    }

    /**
     * Retrieves a single table row by the query parameter.
     *
     * @param array $query
     *
     * @return bool|ResourceModel
     */
    public function findBy($query)
    {
        if (!$query || !is_array($query) || count($query) <= 0) {
            return false;
        }

        global $wpdb;

        $sqlQuery = "SELECT * FROM {$this->table} WHERE 1=1 ";
        $sqlQuery .= $this->parseQueryConditions($query);
        $sqlQuery .= ';';

        $result = $wpdb->get_row($sqlQuery);

        if (!$result) {
            return false;
        }

        return $this->createResourceModel($result);
    }

    /**
     * Retrieves all table rows as an array.
     *
     * @return mixed|void
     */
    public function findAll()
    {
        global $wpdb;

        $returnValue = array();
        $result = $wpdb->get_results("SELECT {$this->primaryKey} FROM {$this->table};");

        if (!$result) {
            return false;
        }

        foreach ($result as $row) {
            $returnValue[] = $this->find($row->id);
        }

        return $returnValue;
    }

    /**
     * Retrieves multiple table rows as an array, filtered by the query.
     *
     * @param array       $query
     * @param null|string $orderBy
     * @param null|string $sort
     *
     * @return bool|ResourceModel[]
     */
    public function findAllBy($query, $orderBy = null, $sort = null)
    {
        if (!$query || !is_array($query) || count($query) <= 0) {
            return false;
        }

        global $wpdb;

        $result   = array();
        $sqlQuery = "SELECT * FROM {$this->table} WHERE 1=1 ";
        $sqlQuery .= $this->parseQueryConditions($query);

        if ($orderBy && is_string($orderBy)) {
            $sqlQuery .= "ORDER BY {$orderBy} ";
        }

        if ($sort && is_string($sort)) {
            $sqlQuery .= "{$sort} ";
        }

        $sqlQuery .= ';';

        foreach ($wpdb->get_results($sqlQuery) as $row) {
            $result[] = $this->createResourceModel($row);
        }

        return $result;
    }

    /**
     * Updates a single table row by its ID.
     *
     * @param int   $id
     * @param array $data
     *
     * @return mixed|void
     */
    public function update($id, $data)
    {
        global $wpdb;

        $meta = array(
            'updated_at' => gmdate('Y-m-d H:i:s'),
            'updated_by' => get_current_user_id()
        );

        $updated = $wpdb->update(
            $this->table,
            array_merge($data, $meta),
            array('id' => $id)
        );

        if (!$updated) {
            return false;
        }

        return $this->find($id);
    }

    /**
     * Updates one or multiple table rows by the query.
     *
     * @param array $query
     * @param array $data
     *
     * @return bool|int
     */
    public function updateBy($query, $data)
    {
        if (!$query || !is_array($query) || count($query) <= 0) {
            return false;
        }

        if (!$data || !is_array($data) || count($data) <= 0) {
            return false;
        }

        global $wpdb;

        $sqlQuery = "UPDATE {$this->table} SET ";

        $sqlQuery .= $wpdb->prepare(' updated_at = %s,', gmdate('Y-m-d H:i:s'));
        $sqlQuery .= $wpdb->prepare(' updated_by = %d,', get_current_user_id());

        foreach ($data as $columnName => $value) {
            if (is_numeric($value)) {
                $sqlQuery .= " {$columnName} = {$value},";
            }

            elseif (is_string($value)) {
                $sqlQuery .= " {$columnName} = '{$value}',";
            }

            elseif ($value === null) {
                $sqlQuery .= " {$columnName} = NULL,";
            }
        }

        $sqlQuery = rtrim($sqlQuery, ',');

        $sqlQuery .= ' WHERE 1=1 ';
        $sqlQuery .= $this->parseQueryConditions($query);

        $sqlQuery .= ';';

        return $wpdb->query($sqlQuery);
    }

    /**
     * Removes multiple table rows by their ID's.
     *
     * @param array $ids
     *
     * @return bool|int
     */
    public function delete($ids)
    {
        global $wpdb;

        $ids = implode(', ', array_map('absint', $ids));
        $sqlQuery = "DELETE FROM {$this->table} WHERE {$this->primaryKey} IN ({$ids});";

        return $wpdb->query($sqlQuery);
    }

    /**
     * Deletes one or more table rows by the query parameter.
     *
     * @param array $query
     *
     * @return bool|int
     */
    public function deleteBy($query)
    {
        if (!$query || !is_array($query) || count($query) <= 0) {
            return false;
        }

        global $wpdb;

        $sqlQuery = "DELETE FROM {$this->table} WHERE 1=1 ";
        $sqlQuery .= $this->parseQueryConditions($query);
        $sqlQuery .= ';';

        return $wpdb->query($sqlQuery);
    }

    /**
     * Retrieves the total count of table entries.
     *
     * @return int
     */
    public function count()
    {
        global $wpdb;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table};");

        return intval($count);
    }

    /**
     * Retrieves the total count of table entries, filtered by the query parameter.
     *
     * @param array $query
     *
     * @return int
     */
    public function countBy($query)
    {
        if (!$query || !is_array($query) || count($query) <= 0) {
            return false;
        }

        global $wpdb;

        $sqlQuery = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1 ";
        $sqlQuery .= $this->parseQueryConditions($query);
        $sqlQuery .= ';';

        $count = $wpdb->get_var($sqlQuery);

        return intval($count);
    }

    /**
     * Performs a general query on the table.
     *
     * @param string $sqlQuery
     * @param string $output
     *
     * @return array|object|null
     */
    public function query($sqlQuery, $output = OBJECT)
    {
        global $wpdb;

        return $wpdb->get_results($sqlQuery, $output);
    }

    /**
     * Truncates the table.
     *
     * @return bool|int
     */
    public function truncate()
    {
        global $wpdb;

        return $wpdb->query("TRUNCATE TABLE {$this->table};");
    }

    /**
     * @param array $query
     *
     * @return string
     */
    private function parseQueryConditions($query)
    {
        $result = '';

        foreach ($query as $columnName => $value) {
            if (is_array($value)) {
                $valuesIn = implode(', ', array_map('absint', $value));
                $result .= "AND {$columnName} IN ({$valuesIn}) ";
            }

            elseif (is_string($value)) {
                $result .= "AND {$columnName} = '{$value}' ";
            }

            elseif (is_numeric($value)) {
                $value = absint($value);
                $result .= "AND {$columnName} = {$value} ";
            }

            elseif ($value === null) {
                $result .= "AND {$columnName} IS NULL ";
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Checks whether an array has string keys.
     *
     * @param array $array
     * @return bool
     */
    private function hasStringKeys($array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Determines if the given string is a valid MySQL logical operator.
     * @see https://www.scommerce-mage.com/blog/magento2-condition-type-search-filter.html
     *
     * @param string $string
     *
     * @return bool
     */
    private function isLogicalOperator($string)
    {
        return in_array(strtoupper($string), array('AND', 'OR', 'IN', 'NOT IN', 'NOT LIKE'));
    }
}