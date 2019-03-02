<?php
/**
 * Generator repository
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce\Repositories;

use \LicenseManagerForWooCommerce\Setup;

defined('ABSPATH') || exit;

/**
 * Generator database connector.
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class Generator
{
    /**
     * Prefixed table name.
     * 
     * @var string
     */
    protected $table;

    /**
     * Adds all filters for interaction with the database table.
     * 
     * @return null
     */
    public function __construct()
    {
        global $wpdb;

        $this->table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

        // SELECT
        add_filter('lmfwc_get_generators', array($this, 'getGenerators'), 10);
        add_filter('lmfwc_get_generator', array($this, 'getGenerator'), 10, 1);

        // INSERT
        add_filter('lmfwc_insert_generator', array($this, 'insertGenerator'), 10, 8);

        // UPDATE
        add_filter('lmfwc_update_generator', array($this, 'updateGenerator'), 10, 9);
        add_filter('lmfwc_update_selective_generator', array($this, 'updateSelectiveGenerator'), 10, 9);

        // DELETE
        add_filter('lmfwc_delete_generators', array($this, 'deleteGenerators'), 10, 1);
    }

    /**
     * Returns all currently available license keys.
     * 
     * @since  1.1.0
     * @return array
     */
    public function getGenerators()
    {
        global $wpdb;

        return $wpdb->get_results(
            "
                SELECT
                    `id`
                    , `name`
                    , `charset`
                    , `chunks`
                    , `chunk_length`
                    , `separator`
                    , `prefix`
                    , `suffix`
                    , `expires_in`
                FROM
                    {$this->table}
                ;
            "
        );
    }

    /**
     * Returns a single license key by its id.
     * 
     * @param integer $id Generator ID
     * 
     * @since  1.1.0
     * @return array
     */
    public function getGenerator($id)
    {
        $clean_id = $id ? absint($id) : null;

        if (!$clean_id) {
            throw new \Exception('Generator ID is missing', 1);
        }

        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "
                    SELECT
                        `id`
                        , `name`
                        , `charset`
                        , `chunks`
                        , `chunk_length`
                        , `separator`
                        , `prefix`
                        , `suffix`
                        , `expires_in`
                    FROM
                        {$this->table}
                    WHERE
                        id = %d
                ",
                $clean_id
            ), ARRAY_A
        );
    }

    /**
     * Save the generator to the database.
     *
     * @param string $name         Generator name.
     * @param string $charset      Character map used for key generation.
     * @param int    $chunks       Number of chunks.
     * @param int    $chunk_length Chunk length.
     * @param string $separator    Separator used.
     * @param string $prefix       License key prefix.
     * @param string $suffix       License key suffix.
     * @param string $expires_in   Validity after purchase (in days).
     *
     * @since  1.1.0
     * @throws \Exception
     * @return integer
     */
    public function insertGenerator(
        $name,
        $charset,
        $chunks,
        $chunk_length,
        $separator,
        $prefix,
        $suffix,
        $expires_in
    ) {
        $clean_name         = $name         ? sanitize_text_field($name)      : null;
        $clean_charset      = $charset      ? sanitize_text_field($charset)   : null;
        $clean_chunks       = $chunks       ? absint($chunks)                 : null;
        $clean_chunk_length = $chunk_length ? absint($chunk_length)           : null;
        $clean_separator    = $separator    ? sanitize_text_field($separator) : null;
        $clean_prefix       = $prefix       ? sanitize_text_field($prefix)    : null;
        $clean_suffix       = $suffix       ? sanitize_text_field($suffix)    : null;
        $clean_expires_in   = $expires_in   ? absint($expires_in)             : null;

        if (!$clean_name) {
            throw new \Exception('Generator name is missing', 1);
        }

        if (!$clean_charset) {
            throw new \Exception('Generator charset is missing', 2);
        }

        if (!$clean_chunks) {
            throw new \Exception('Generator chunks is missing', 3);
        }

        if (!$clean_chunk_length) {
            throw new \Exception('Generator chunk_length is missing', 4);
        }

        global $wpdb;

        $insert = $wpdb->insert(
            $this->table,
            array(
                'name'         => $clean_name,
                'charset'      => $clean_charset,
                'chunks'       => $clean_chunks,
                'chunk_length' => $clean_chunk_length,
                'separator'    => $clean_separator,
                'prefix'       => $clean_prefix,
                'suffix'       => $clean_suffix,
                'expires_in'   => $clean_expires_in
            ),
            array('%s', '%s', '%d', '%d', '%s', '%s', '%s')
        );

        if (!$insert) {
            return 0;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update an existing generator.
     *
     * @param int    $id           Generator ID.
     * @param string $name         Generator name.
     * @param string $charset      Character map used for key generation.
     * @param int    $chunks       Number of chunks.
     * @param int    $chunk_length Chunk length.
     * @param string $separator    Separator used.
     * @param string $prefix       License Key prefix.
     * @param string $suffix       License Key suffix.
     * @param string $expires_in   Number of days for which the license is valid.
     *
     * @since  1.1.0
     * @return boolean
     */
    public function updateGenerator(
        $id,
        $name,
        $charset,
        $chunks,
        $chunk_length,
        $separator,
        $prefix,
        $suffix,
        $expires_in
    ) {
        $clean_id           = $id           ? absint($id)                     : null;
        $clean_name         = $name         ? sanitize_text_field($name)      : null;
        $clean_charset      = $charset      ? sanitize_text_field($charset)   : null;
        $clean_chunks       = $chunks       ? absint($chunks)                 : null;
        $clean_chunk_length = $chunk_length ? absint($chunk_length)           : null;
        $clean_separator    = $separator    ? sanitize_text_field($separator) : null;
        $clean_prefix       = $prefix       ? sanitize_text_field($prefix)    : null;
        $clean_suffix       = $suffix       ? sanitize_text_field($suffix)    : null;
        $clean_expires_in   = $expires_in   ? absint($expires_in)             : null;

        if (!$clean_id) {
            throw new \Exception('Generator ID is missing', 1);
        }

        if (!$clean_name) {
            throw new \Exception('Generator name is missing', 2);
        }

        if (!$clean_charset) {
            throw new \Exception('Generator charset is missing', 3);
        }

        if (!$clean_chunks) {
            throw new \Exception('Generator chunks is missing', 4);
        }

        if (!$clean_chunk_length) {
            throw new \Exception('Generator chunk_length is missing', 5);
        }

        global $wpdb;

        return $wpdb->update(
            $this->table,
            array(
                'name'         => $clean_name,
                'charset'      => $clean_charset,
                'chunks'       => $clean_chunks,
                'chunk_length' => $clean_chunk_length,
                'separator'    => $clean_separator,
                'prefix'       => $clean_prefix,
                'suffix'       => $clean_suffix,
                'expires_in'   => $clean_expires_in
            ),
            array('id' => $clean_id),
            array('%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s'),
            array('%d')
        );
    }

    /**
     * Selectively update parts of an existing Generator.
     *
     * @param integer $id           Generator ID
     * @param string  $name         Generator name
     * @param string  $charset      Character set
     * @param integer $chunks       Number of chunks
     * @param integer $chunk_length Individual chunk length
     * @param string  $separator    Chunk separator
     * @param string  $prefix       License Key prefix
     * @param string  $suffix       License Key suffix
     * @param integer $expires_in   Validity period after purchase (in days)
     *
     * @since  1.1.0
     * @return array
     */
    public function updateSelectiveGenerator(
        $id,
        $name,
        $charset,
        $chunks,
        $chunk_length,
        $separator,
        $prefix,
        $suffix,
        $expires_in
    ) {
        $clean_id           = $id           ? absint($id)                     : null;
        $clean_name         = $name         ? sanitize_text_field($name)      : null;
        $clean_charset      = $charset      ? sanitize_text_field($charset)   : null;
        $clean_chunks       = $chunks       ? absint($chunks)                 : null;
        $clean_chunk_length = $chunk_length ? absint($chunk_length)           : null;
        $clean_separator    = $separator    ? sanitize_text_field($separator) : null;
        $clean_prefix       = $prefix       ? sanitize_text_field($prefix)    : null;
        $clean_suffix       = $suffix       ? sanitize_text_field($suffix)    : null;
        $clean_expires_in   = $expires_in   ? absint($expires_in)             : null;

        if (!$id) {
            throw new \Exception('Generator ID is missing', 1);
        }

        if (!$clean_name
            && !$clean_charset
            && !$clean_chunks
            && !$clean_chunk_length
            && !$clean_separator
            && !$clean_prefix
            && !$clean_suffix
            && !$clean_expires_in
        ) {
            throw new \Exception('No parameters provided', 2);
        }

        global $wpdb;

        $table = $this->table;
        $first = true;

        $sql = "UPDATE {$table}";

        if ($clean_name) {
            $sql .= $wpdb->prepare(' SET name = %s', $clean_name);
            $first = false;
        }

        if ($clean_charset) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('charset = %s', $clean_charset);
            $first = false;
        }

        if ($clean_chunks) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('chunks = %d', $clean_chunks);
            $first = false;
        }

        if ($clean_chunk_length) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('chunk_length = %d', $clean_chunk_length);
            $first = false;
        }

        if ($clean_separator) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('separator = %s', $clean_separator);
            $first = false;
        }

        if ($clean_prefix) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('prefix = %s', $clean_prefix);
            $first = false;
        }

        if ($clean_suffix) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('suffix = %s', $clean_suffix);
            $first = false;
        }

        if ($clean_expires_in) {
            $sql .= $first ? ' SET ' : ', ';
            $sql .= $wpdb->prepare('expires_in = %d', $clean_expires_in);
            $first = false;
        }

        $sql .= $wpdb->prepare(' WHERE id = %d;', $id);

        $wpdb->query($sql);

        return $this->getGenerator($id);
    }

    /**
     * Deletes generators by an array of generator ID's.
     *
     * @param array $generators Array of Generator ID's to be deleted
     *
     * @since  1.1.0
     * @throws Exception
     * @return boolean
     */
    public function deleteGenerators($generators)
    {
        $clean_ids = array();

        if (!is_array($generators)) {
            throw new \Exception('Input parameter must be an array', 1);
        }

        foreach ($generators as $id) {
            if (!absint($id)) {
                continue;
            }

            array_push($clean_ids, absint($id));
        }

        if (count($clean_ids) == 0) {
            throw new \Exception('No valid IDs given', 2);
        }

        global $wpdb;

        return $wpdb->query(
            sprintf(
                'DELETE FROM %s WHERE id IN (%s)',
                $this->table,
                implode(', ', $clean_ids)
            )
        );
    }


}