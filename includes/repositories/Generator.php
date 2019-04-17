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
use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;

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
    const UNDEFINED = -1;

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
        add_filter('lmfwc_get_generator',  array($this, 'getGenerator'),  10, 1);

        // INSERT
        add_filter('lmfwc_insert_generator', array($this, 'insertGenerator'), 10, 9);

        // UPDATE
        add_filter('lmfwc_update_generator',           array($this, 'updateGenerator'),          10, 10);
        add_filter('lmfwc_update_selective_generator', array($this, 'updateSelectiveGenerator'), 10, 10);

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
                    , `times_activated_max`
                    , `separator`
                    , `prefix`
                    , `suffix`
                    , `expires_in`
                FROM
                    {$this->table}
                ;
            ",
            OBJECT
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
            throw new LMFWC_Exception('Generator ID is invalid');
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
                        , `times_activated_max`
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
            ),
            OBJECT
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
        $times_activated_max,
        $separator,
        $prefix,
        $suffix,
        $expires_in
    ) {
        $clean_name                = $name                ? sanitize_text_field($name)      : null;
        $clean_charset             = $charset             ? sanitize_text_field($charset)   : null;
        $clean_chunks              = $chunks              ? absint($chunks)                 : null;
        $clean_chunk_length        = $chunk_length        ? absint($chunk_length)           : null;
        $clean_times_activated_max = $times_activated_max ? absint($times_activated_max)    : null;
        $clean_separator           = $separator           ? sanitize_text_field($separator) : null;
        $clean_prefix              = $prefix              ? sanitize_text_field($prefix)    : null;
        $clean_suffix              = $suffix              ? sanitize_text_field($suffix)    : null;
        $clean_expires_in          = $expires_in          ? absint($expires_in)             : null;

        if (!$clean_name) {
            throw new LMFWC_Exception('Generator name is invalid');
        }

        if (!$clean_charset) {
            throw new LMFWC_Exception('Generator charset is invalid');
        }

        if (!$clean_chunks) {
            throw new LMFWC_Exception('Generator chunks is invalid');
        }

        if (!$clean_chunk_length) {
            throw new LMFWC_Exception('Generator chunk_length is invalid');
        }

        global $wpdb;

        $insert = $wpdb->insert(
            $this->table,
            array(
                'name'                => $clean_name,
                'charset'             => $clean_charset,
                'chunks'              => $clean_chunks,
                'chunk_length'        => $clean_chunk_length,
                'times_activated_max' => $clean_times_activated_max,
                'separator'           => $clean_separator,
                'prefix'              => $clean_prefix,
                'suffix'              => $clean_suffix,
                'expires_in'          => $clean_expires_in
            ),
            array('%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s')
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
        $times_activated_max,
        $separator,
        $prefix,
        $suffix,
        $expires_in
    ) {
        $clean_id                  = $id                  ? absint($id)                     : null;
        $clean_name                = $name                ? sanitize_text_field($name)      : null;
        $clean_charset             = $charset             ? sanitize_text_field($charset)   : null;
        $clean_chunks              = $chunks              ? absint($chunks)                 : null;
        $clean_chunk_length        = $chunk_length        ? absint($chunk_length)           : null;
        $clean_times_activated_max = $times_activated_max ? absint($times_activated_max)    : null;
        $clean_separator           = $separator           ? sanitize_text_field($separator) : null;
        $clean_prefix              = $prefix              ? sanitize_text_field($prefix)    : null;
        $clean_suffix              = $suffix              ? sanitize_text_field($suffix)    : null;
        $clean_expires_in          = $expires_in          ? absint($expires_in)             : null;

        if (!$clean_id) {
            throw new LMFWC_Exception('Generator ID is invalid');
        }

        if (!$clean_name) {
            throw new LMFWC_Exception('Generator name is invalid');
        }

        if (!$clean_charset) {
            throw new LMFWC_Exception('Generator charset is invalid');
        }

        if (!$clean_chunks) {
            throw new LMFWC_Exception('Generator chunks is invalid');
        }

        if (!$clean_chunk_length) {
            throw new LMFWC_Exception('Generator chunk_length is invalid');
        }

        global $wpdb;

        return $wpdb->update(
            $this->table,
            array(
                'name'                => $clean_name,
                'charset'             => $clean_charset,
                'chunks'              => $clean_chunks,
                'chunk_length'        => $clean_chunk_length,
                'times_activated_max' => $clean_times_activated_max,
                'separator'           => $clean_separator,
                'prefix'              => $clean_prefix,
                'suffix'              => $clean_suffix,
                'expires_in'          => $clean_expires_in
            ),
            array('id' => $clean_id),
            array('%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s'),
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
        $times_activated_max,
        $separator,
        $prefix,
        $suffix,
        $expires_in
    ) {
        if ($id && $id != self::UNDEFINED) {
            $clean_id = absint($id);
        } elseif (is_null($id)) {
            $clean_id = null;
        } elseif ($id == self::UNDEFINED) {
            $clean_id = self::UNDEFINED;
        }

        if ($name && $name != self::UNDEFINED) {
            $clean_name = sanitize_text_field($name);
        } elseif (is_null($name)) {
            $clean_name = null;
        } elseif ($name == self::UNDEFINED) {
            $clean_name = self::UNDEFINED;
        }

        if ($charset && $charset != self::UNDEFINED) {
            $clean_charset = sanitize_text_field($charset);
        } elseif (is_null($charset)) {
            $clean_charset = null;
        } elseif ($charset == self::UNDEFINED) {
            $clean_charset = self::UNDEFINED;
        }

        if ($chunks && $chunks != self::UNDEFINED) {
            $clean_chunks = absint($chunks);
        } elseif (is_null($chunks)) {
            $clean_chunks = null;
        } elseif ($chunks == self::UNDEFINED) {
            $clean_chunks = self::UNDEFINED;
        }

        if ($chunk_length && $chunk_length != self::UNDEFINED) {
            $clean_chunk_length = absint($chunk_length);
        } elseif (is_null($chunk_length)) {
            $clean_chunk_length = null;
        } elseif ($chunk_length == self::UNDEFINED) {
            $clean_chunk_length = self::UNDEFINED;
        }

        if ($times_activated_max && $times_activated_max != self::UNDEFINED) {
            $clean_times_activated_max = absint($times_activated_max);
        } elseif (is_null($times_activated_max)) {
            $clean_times_activated_max = null;
        } elseif ($times_activated_max == self::UNDEFINED) {
            $clean_times_activated_max = self::UNDEFINED;
        }

        if ($separator && $separator != self::UNDEFINED) {
            $clean_separator = sanitize_text_field($separator);
        } elseif (is_null($separator)) {
            $clean_separator = null;
        } elseif ($separator == self::UNDEFINED) {
            $clean_separator = self::UNDEFINED;
        }

        if ($prefix && $prefix != self::UNDEFINED) {
            $clean_prefix = sanitize_text_field($prefix);
        } elseif (is_null($prefix)) {
            $clean_prefix = null;
        } elseif ($prefix == self::UNDEFINED) {
            $clean_prefix = self::UNDEFINED;
        }

        if ($suffix && $suffix != self::UNDEFINED) {
            $clean_suffix = sanitize_text_field($suffix);
        } elseif (is_null($suffix)) {
            $clean_suffix = null;
        } elseif ($suffix == self::UNDEFINED) {
            $clean_suffix = self::UNDEFINED;
        }

        if ($expires_in && $expires_in != self::UNDEFINED) {
            $clean_expires_in = absint($expires_in);
        } elseif (is_null($expires_in)) {
            $clean_expires_in = null;
        } elseif ($expires_in == self::UNDEFINED) {
            $clean_expires_in = self::UNDEFINED;
        }

        if (!$id) {
            throw new LMFWC_Exception('Generator ID is invalid');
        }

        if ($clean_name == self::UNDEFINED
            && $clean_charset == self::UNDEFINED
            && $clean_chunks == self::UNDEFINED
            && $clean_chunk_length == self::UNDEFINED
            && $clean_times_activated_max == self::UNDEFINED
            && $clean_separator == self::UNDEFINED
            && $clean_prefix == self::UNDEFINED
            && $clean_suffix == self::UNDEFINED
            && $clean_expires_in == self::UNDEFINED
        ) {
            throw new LMFWC_Exception('No parameters provided');
        }

        if (!$clean_name && $clean_name != self::UNDEFINED) {
            throw new LMFWC_Exception('Generator name is invalid');
        }

        if (!$clean_charset && $clean_charset != self::UNDEFINED) {
            throw new LMFWC_Exception('Generator character map is invalid');
        }

        if (!$clean_chunks && $clean_chunks != self::UNDEFINED) {
            throw new LMFWC_Exception('Generator number of chunks is invalid');
        }

        if (!$clean_chunk_length && $clean_chunk_length != self::UNDEFINED) {
            throw new LMFWC_Exception('Generator chunk length is invalid');
        }

        global $wpdb;

        $first = true;

        $sql = "UPDATE {$this->table}";

        if ($clean_name != self::UNDEFINED) {
            $sql .= $wpdb->prepare(' SET `name` = %s', $clean_name);
            $first = false;
        }

        if ($clean_charset != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_charset)) {
                $sql .= '`charset` = NULL';
            } else {
                $sql .= $wpdb->prepare('`charset` = %s', $clean_charset);
            }

            $first = false;
        }

        if ($clean_chunks != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_chunks)) {
                $sql .= '`chunks` = NULL';
            } else {
                $sql .= $wpdb->prepare('`chunks` = %d', $clean_chunks);
            }

            $first = false;
        }

        if ($clean_chunk_length != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_chunk_length)) {
                $sql .= '`chunk_length` = NULL';
            } else {
                $sql .= $wpdb->prepare('`chunk_length` = %d', $clean_chunk_length);
            }

            $first = false;
        }

        if ($clean_times_activated_max != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_times_activated_max)) {
                $sql .= '`times_activated_max` = NULL';
            } else {
                $sql .= $wpdb->prepare('`times_activated_max` = %d', $clean_times_activated_max);
            }

            $first = false;
        }

        if ($clean_separator != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_separator)) {
                $sql .= '`separator` = NULL';
            } else {
                $sql .= $wpdb->prepare('`separator` = %s', $clean_separator);
            }

            $first = false;
        }

        if ($clean_prefix != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_prefix)) {
                $sql .= '`prefix` = NULL';
            } else {
                $sql .= $wpdb->prepare('`prefix` = %s', $clean_prefix);
            }

            $first = false;
        }

        if ($clean_suffix != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_suffix)) {
                $sql .= '`suffix` = NULL';
            } else {
                $sql .= $wpdb->prepare('`suffix` = %s', $clean_suffix);
            }

            $first = false;
        }

        if ($clean_expires_in != self::UNDEFINED) {
            $sql .= $first ? ' SET ' : ', ';
            
            if (is_null($clean_expires_in)) {
                $sql .= '`expires_in` = NULL';
            } else {
                $sql .= $wpdb->prepare('`expires_in` = %d', $clean_expires_in);
            }

            $first = false;
        }

        $sql .= $wpdb->prepare(' WHERE `id` = %d;', $id);

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
            throw new LMFWC_Exception('Input parameter must be an array');
        }

        foreach ($generators as $id) {
            if (!absint($id)) {
                continue;
            }

            array_push($clean_ids, absint($id));
        }

        if (count($clean_ids) == 0) {
            throw new LMFWC_Exception('No valid IDs given');
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