<?php
/**
 * API Key repository
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
 * API Key database connector.
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class ApiKey
{
    /**
     * Prefixed table name.
     * 
     * @var string
     */
    protected $table;

    /**
     * Contains allowed values for the permissions column
     * 
     * @var array
     */
    protected $permission_whitelist;

    /**
     * Adds all filters for interaction with the database table.
     * 
     * @return null
     */
    public function __construct()
    {
        global $wpdb;

        $this->table = $wpdb->prefix . Setup::API_KEYS_TABLE_NAME;
        $this->permission_whitelist = array('read', 'write', 'read_write');

        // SELECT
        add_filter('lmfwc_get_api_key', array($this, 'getApiKey'), 10, 1);

        // INSERT
        add_filter('lmfwc_insert_api_key', array($this, 'insertApiKey'), 10, 3);

        // UPDATE
        add_filter('lmfwc_update_api_key', array($this, 'updateApiKey'), 10, 4);

        // DELETE
        add_filter('lmfwc_delete_api_keys', array($this, 'deleteApiKeys'), 10, 1);
    }

    /**
     * Retrieves license key with the given ID. If no ID is given return an empty
     * API Key array.
     *
     * @param int $id ID of the License Key
     *
     * @since  1.1.0
     * @return array
     */
    public function getApiKey($id)
    {
        global $wpdb;

        $empty = array(
            'id'        => 0,
            'user_id'       => '',
            'description'   => '',
            'permissions'   => '',
            'truncated_key' => '',
            'last_access'   => '',
        );

        if (!$id) {
            return $empty;
        }

        $table = Setup::API_KEYS_TABLE_NAME;

        $key = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT
                    id, user_id, description, permissions, truncated_key, last_access
                FROM
                    {$this->table}
                WHERE
                    id = %d",
                $id
            ),
            ARRAY_A
        );

        if (is_null($key)) {
            return $empty;
        }

        return $key;
    }

    /**
     * Adds a new API key.
     *
     * @param int    $user_id     WordPress User ID
     * @param string $description Friendly name to describe the API key
     * @param string $permissions Permissions granted to this key
     *
     * @since  1.1.0
     * @return array
     */
    public function insertApiKey($user_id, $description, $permissions)
    {
        $clean_user_id     = $user_id     ? absint($user_id)                  : null;
        $clean_description = $description ? sanitize_text_field($description) : null;
        $clean_permissions = $permissions ? sanitize_text_field($permissions) : null;

        if (!$clean_user_id) {
            throw new LMFWC_Exception('API Key User ID is missing');
        }

        if (!$clean_description) {
            throw new LMFWC_Exception('API Key Description is missing');
        }

        if (!$clean_permissions) {
            throw new LMFWC_Exception('API Key Permissions are missing');
        }

        if (!in_array($clean_permissions, $this->permission_whitelist)) {
            throw new LMFWC_Exception('API Key Permissions are invalid');
        }

        global $wpdb;

        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();

        $key_id = $wpdb->insert(
            $this->table,
            array(
                'user_id'         => $user_id,
                'description'     => $description,
                'permissions'     => $permissions,
                'consumer_key'    => wc_api_hash($consumer_key),
                'consumer_secret' => $consumer_secret,
                'truncated_key'   => substr($consumer_key, -7),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );

        return array(
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
            'key_id' => $wpdb->insert_id
        );
    }

    /**
     * Updates an existing API Key.
     *
     * @param integer $id          The API Key ID
     * @param integer $user_id     The User ID of the owner
     * @param string  $description Friendly name for the key
     * @param string  $permissions The permissions given to this key
     * 
     * @since  1.1.0
     * @return integer
     */
    public function updateApiKey($id, $user_id, $description, $permissions)
    {
        $clean_id          = $id          ? absint($id)                       : null;
        $clean_user_id     = $user_id     ? absint($user_id)                  : null;
        $clean_description = $description ? sanitize_text_field($description) : null;
        $clean_permissions = $permissions ? sanitize_text_field($permissions) : null;

        if (!$clean_id) {
            throw new LMFWC_Exception('API Key ID is missing');
        }

        if (!$clean_user_id) {
            throw new LMFWC_Exception('API Key User ID is missing');
        }

        if (!$clean_description) {
            throw new LMFWC_Exception('API Key Description is missing');
        }

        if (!$clean_permissions) {
            throw new LMFWC_Exception('API Key Permissions are missing');
        }

        if (!in_array($clean_permissions, $this->permission_whitelist)) {
            throw new LMFWC_Exception('API Key Permissions are invalid');
        }

        global $wpdb;

        return $wpdb->update(
            $this->table,
            array(
                'user_id'     => $user_id,
                'description' => $description,
                'permissions' => $permissions,
            ),
            array('id' => $id),
            array('%d', '%s', '%s'),
            array('%d')
        );
    }

    /**
     * Deletes API Keys by an array of ID's.
     *
     * @param array $keys Array of API Key ID's to be deleted
     *
     * @since  1.1.0
     * @return boolean
     */
    public function deleteApiKeys($keys)
    {
        $clean_ids = array();

        if (!is_array($keys)) {
            throw new LMFWC_Exception('Input parameter must be an array');
        }

        foreach ($keys as $id) {
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