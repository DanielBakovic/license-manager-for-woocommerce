<?php
/**
 * Main plugin file.
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Lists\LicensesList;
use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;
use \LicenseManagerForWooCommerce\Enums\LicenseSource as LicenseSourceEnum;
use \LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce FormHandler
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class FormHandler
{
    const TEMP_IMPORT_FILE = 'import.tmp';

    /**
     * FormHandler Constructor.
     */
    public function __construct()
    {
        // Admin POST requests.
        add_action(
            'admin_post_lmfwc_save_generator',
            array($this, 'saveGenerator'),
            10
        );
        add_action(
            'admin_post_lmfwc_update_generator',
            array($this, 'updateGenerator'),
            10
        );
        add_action(
            'admin_post_lmfwc_import_license_keys',
            array($this, 'importLicenseKeys'),
            10
        );
        add_action(
            'admin_post_lmfwc_add_license_key',
            array($this, 'addLicenseKey'),
            10
        );
        add_action(
            'admin_post_lmfwc_update_license_key',
            array($this, 'updateLicenseKey'),
            10
        );
        add_action(
            'admin_post_lmfwc_api_key_update',
            array($this, 'apiKeyUpdate'),
            10
        );

        // AJAX calls.
        add_action(
            'wp_ajax_lmfwc_show_license_key',
            array($this, 'showLicenseKey'),
            10
        );
        add_action(
            'wp_ajax_lmfwc_show_all_license_keys',
            array($this, 'showAllLicenseKeys'),
            10
        );

        // WooCommerce related
        add_action(
            'woocommerce_after_order_itemmeta',
            array($this, 'showOrderedLicenses'),
            10,
            3
        );
    }

    /**
     * Save the generator to the database.
     *
     * @since  1.0.0
     * @return null
     */
    public function saveGenerator()
    {
        // Verify the nonce.
        check_admin_referer('lmfwc_save_generator');

        // Validate request.
        if ($_POST['name'] == '' || !is_string($_POST['name'])) {
            AdminNotice::error(__('Generator name is missing.', 'lmfwc'));

            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=add',
                        AdminMenus::GENERATORS_PAGE
                    )
                )
            );

            exit();
        }

        if ($_POST['charset'] == '' || !is_string($_POST['charset'])) {
            AdminNotice::error(__('The charset is invalid.', 'lmfwc'));
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=add',
                        AdminMenus::GENERATORS_PAGE
                    )
                )
            );
            exit();
        }

        if ($_POST['chunks'] == '' || !is_numeric($_POST['chunks'])) {
            AdminNotice::error(
                __('Only integer values allowed for chunks.', 'lmfwc')
            );

            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=add',
                        AdminMenus::GENERATORS_PAGE
                    )
                )
            );

            exit();
        }

        if ($_POST['chunk_length'] == '' || !is_numeric($_POST['chunk_length'])) {
            AdminNotice::error(
                __('Only integer values allowed for chunk length.', 'lmfwc')
            );

            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=add',
                        AdminMenus::GENERATORS_PAGE
                    )
                )
            );

            exit();
        }

        // Save the generator.
        $result = apply_filters(
            'lmfwc_insert_generator',
            $_POST['name'],
            $_POST['charset'],
            $_POST['chunks'],
            $_POST['chunk_length'],
            $_POST['times_activated_max'],
            $_POST['separator'],
            $_POST['prefix'],
            $_POST['suffix'],
            $_POST['expires_in']
        );

        if ($result) {
            AdminNotice::success(
                __('The generator was added successfully.', 'lmfwc')
            );
        } else {
            AdminNotice::error(
                __('There was a problem adding the generator.', 'lmfwc')
            );
        }

        wp_redirect(
            admin_url(
                sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)
            )
        );

        exit();
    }

    /**
     * Update an existing generator.
     *
     * @since  1.0.0
     * @return null
     */
    public function updateGenerator()
    {
        // Verify the nonce.
        check_admin_referer('lmfwc_update_generator');

        $generator_id = absint($_POST['id']);

        // Validate request.
        if ($_POST['name'] == '' || !is_string($_POST['name'])) {
            AdminNotice::error(__('The Generator name is invalid.', 'lmfwc'));

            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::GENERATORS_PAGE,
                        $generator_id
                    )
                )
            );
            exit();
        }

        if ($_POST['charset'] == '' || !is_string($_POST['charset'])) {
            AdminNotice::error(__('The Generator charset is invalid.', 'lmfwc'));

            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::GENERATORS_PAGE,
                        $generator_id
                    )
                )
            );
            exit();
        }

        if ($_POST['chunks'] == '' || !is_numeric($_POST['chunks'])) {
            AdminNotice::error(__('The Generator chunks are invalid.', 'lmfwc'));

            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::GENERATORS_PAGE,
                        $generator_id
                    )
                )
            );
            exit();
        }

        if ($_POST['chunk_length'] == '' || !is_numeric($_POST['chunk_length'])) {
            AdminNotice::error(
                __('The Generator chunk length is invalid.', 'lmfwc')
            );
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::GENERATORS_PAGE,
                        $generator_id
                    )
                )
            );
            exit();
        }

        // Update the generator.
        $result = apply_filters(
            'lmfwc_update_generator',
            $_POST['id'],
            $_POST['name'],
            $_POST['charset'],
            $_POST['chunks'],
            $_POST['chunk_length'],
            $_POST['times_activated_max'],
            $_POST['separator'],
            $_POST['prefix'],
            $_POST['suffix'],
            $_POST['expires_in']
        );

        // Redirect according to $result.
        if (!$result) {
            AdminNotice::error(
                __('There was a problem updating the generator.', 'lmfwc')
            );
        } else {
            AdminNotice::success(
                __('The Generator was updated successfully.', 'lmfwc')
            );
        }

        wp_redirect(
            admin_url(
                sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)
            )
        );
        exit();
    }

    /**
     * Import licenses from a compatible CSV or TXT file into the database.
     *
     * @since  1.0.0
     * @return null
     */
    public function importLicenseKeys()
    {
        // Check the nonce.
        check_admin_referer('lmfwc_import_license_keys');

        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');

        if (!in_array($ext, array('txt', 'csv'))
            || !in_array($_FILES['file']['type'], $mimes)
        ) {
            AdminNotice::error(
                __('Invalid file type, only TXT and CSV allowed.', 'lmfwc')
            );

            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=add',
                    AdminMenus::LICENSES_PAGE
                )
            );

            exit();
        }

        $file_name = $_FILES['file']['tmp_name'];
        $file_path = LMFWC_ASSETS_DIR . self::TEMP_IMPORT_FILE;

        // File upload file, return with error.
        if (!move_uploaded_file($file_name, $file_path)) {
            return null;
        }

        // Handle TXT file uploads
        if ($ext == 'txt') {
            $license_keys = file(
                LMFWC_ASSETS_DIR . self::TEMP_IMPORT_FILE, FILE_IGNORE_NEW_LINES
            );

            // Check for invalid file contents.
            if (!is_array($license_keys)) {
                AdminNotice::error(__('Invalid file content.', 'lmfwc'));

                wp_redirect(
                    sprintf(
                        'admin.php?page=%s&action=add',
                        AdminMenus::LICENSES_PAGE
                    )
                );
                exit();
            }
        }

        // Handle CSV file uploads
        if ($ext == 'csv') {
            $license_keys = array();

            if (($handle = fopen(LMFWC_ASSETS_DIR . self::TEMP_IMPORT_FILE, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    if ($data && is_array($data) && count($data) > 0) {
                        $license_keys[] = $data[0];
                    }
                }

                fclose($handle);
            }
        }

        if (array_key_exists('activate', $_POST)) {
            $status = LicenseStatusEnum::ACTIVE;
        } else {
            $status = LicenseStatusEnum::INACTIVE;
        }

        // Save the imported keys.
        try {
            $result = apply_filters(
                'lmfwc_insert_imported_license_keys',
                $license_keys,
                $status,
                $_POST['product'],
                $_POST['valid_for'],
                $_POST['times_activated_max'],
                get_current_user_id()
            );
        } catch (\Exception $e) {
            AdminNotice::error(
                __('There was a problem importing the license keys.', 'lmfwc')
            );
            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=add',
                    AdminMenus::LICENSES_PAGE
                )
            );
            exit();
        }

        // Delete the temporary file now that we're done.
        unlink(LMFWC_ASSETS_DIR . self::TEMP_IMPORT_FILE);

        // Redirect according to $result.
        if ($result['failed'] == 0 && $result['added'] == 0) {
            AdminNotice::error(
                __('There was a problem importing the license keys.', 'lmfwc')
            );
            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=add',
                    AdminMenus::LICENSES_PAGE
                )
            );
            exit();
        }

        if ($result['failed'] == 0 && $result['added'] > 0) {
            AdminNotice::success(
                sprintf(
                    __('%d License key(s) added successfully.', 'lmfwc'),
                    intval($result['added'])
                )
            );
            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=add',
                    AdminMenus::LICENSES_PAGE
                )
            );
            exit();
        }

        if ($result['failed'] > 0 && $result['added'] == 0) {
            AdminNotice::error(
                __('There was a problem importing the license keys.', 'lmfwc')
            );
            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=add',
                    AdminMenus::LICENSES_PAGE
                )
            );
            exit();
        }

        if ($result['failed'] > 0 && $result['added'] > 0) {
            AdminNotice::warning(
                sprintf(
                    __('%d key(s) have been imported, while %d key(s) were not imported.', 'lmfwc'),
                    intval($result['added']),
                    intval($result['failed'])
                )
            );

            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=add',
                    AdminMenus::LICENSES_PAGE
                )
            );

            exit();
        }
    }

    /**
     * Add a single license key to the database.
     *
     * @since  1.0.0
     * @return null
     */
    public function addLicenseKey()
    {
        // Check the nonce
        check_admin_referer('lmfwc_add_license_key');

        // Set the proper license key status
        if (array_key_exists('activate', $_POST)) {
            $status = LicenseStatusEnum::ACTIVE;
        } else {
            $status = LicenseStatusEnum::INACTIVE;
        }

        // Insert the license key
        $result = apply_filters(
            'lmfwc_insert_license_key',
            null,
            $_POST['product'],
            $_POST['license_key'],
            $_POST['valid_for'],
            LicenseSourceEnum::IMPORT,
            $status,
            $_POST['times_activated_max'],
            get_current_user_id()
        );

        // Redirect with message
        if ($result) {
            AdminNotice::success(
                __('1 License key(s) added successfully.', 'lmfwc')
            );
        } else {
            AdminNotice::error(
                __('There was a problem adding the license key.', 'lmfwc')
            );
        }

        // Redirect
        wp_redirect(
            sprintf(
                'admin.php?page=%s&action=add',
                AdminMenus::LICENSES_PAGE
            )
        );

        exit();
    }

    /**
     * Updates an existing license keys.
     * 
     * @since  1.1.0
     * @return null
     */
    public function updateLicenseKey()
    {
        // Check the nonce
        check_admin_referer('lmfwc_update_license_key');

        // Update the License
        $result = apply_filters(
            'lmfwc_update_license_key',
            $_POST['license_id'],
            $_POST['product'],
            $_POST['license_key'],
            $_POST['valid_for'],
            $_POST['source'],
            $_POST['status'],
            $_POST['times_activated_max'],
            get_current_user_id()
        );

        // Set the admin notice
        if ($result) {
            AdminNotice::success(
                __('Your license key has been updated successfully.', 'lmfwc')
            );
        } else {
            AdminNotice::error(
                __('There was a problem updating the license key.', 'lmfwc')
            );
        }

        // Redirect
        wp_redirect(
            sprintf(
                'admin.php?page=%s&action=edit&id=%d',
                AdminMenus::LICENSES_PAGE,
                absint($_POST['license_id'])
            )
        );

        exit();
    }

    /**
     * Store a created API key to the database or updates an existing key.
     *
     * @since  1.1.0
     * @return null
     */
    public function apiKeyUpdate()
    {
        // Check the nonce.
        check_admin_referer('lmfwc-api-key-update');

        $error = null;

        if (empty($_POST['description'])) {
            $error = __('Description is missing.', 'lmfwc');
        }

        if (empty($_POST['user']) || $_POST['user'] == -1) {
            $error = __('User is missing.', 'lmfwc');
        }

        if (empty($_POST['permissions'])) {
            $error = __('Permissions is missing.', 'lmfwc');
        }

        $key_id      = absint($_POST['id']);
        $description = sanitize_text_field(wp_unslash($_POST['description']));
        $permissions = (in_array($_POST['permissions'], array('read', 'write', 'read_write'))) ? sanitize_text_field($_POST['permissions']) : 'read';
        $user_id     = absint($_POST['user']);
        $action      = sanitize_text_field(wp_unslash($_POST['lmfwc_action']));

        // Check if current user can edit other users.
        if ($user_id && !current_user_can('edit_user', $user_id)) {
            if (get_current_user_id() !== $user_id) {
                $error = __('You do not have permission to assign API Keys to the selected user.', 'lmfwc');
            }
        }

        if ($error) {
            AdminNotice::error($error);

            wp_redirect(
                sprintf(
                    'admin.php?page=%s&tab=rest_api&create_key=1',
                    AdminMenus::SETTINGS_PAGE
                )
            );

            exit();
        }

        if ($action == 'create') {
            $data = apply_filters(
                'lmfwc_insert_api_key',
                $user_id,
                $description,
                $permissions
            );

            if ($data) {
                AdminNotice::success(
                    __('API Key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'lmfwc')
                );
                set_transient('lmfwc_api_key', $data, 60);
            } else {
                AdminNotice::error(
                    __('There was a problem generating the API key.', 'lmfwc')
                );
            }

            wp_redirect(
                sprintf(
                    'admin.php?page=%s&tab=rest_api&show_key=1',
                    AdminMenus::SETTINGS_PAGE
                )
            );

            exit();
        } elseif ($action == 'edit') {
            $update = apply_filters(
                'lmfwc_update_api_key',
                $key_id,
                $user_id,
                $description,
                $permissions
            );

            if ($update) {
                AdminNotice::success(__('API Key updated successfully.', 'lmfwc'));
            } else {
                AdminNotice::error(
                    __('There was a problem updating the API key.', 'lmfwc')
                );
            }

            wp_redirect(
                sprintf(
                    'admin.php?page=%s&tab=rest_api',
                    AdminMenus::SETTINGS_PAGE
                )
            );

            exit();
        }
    }

    /**
     * Show a single license key.
     *
     * @since  1.0.0
     * @return null
     */
    public function showLicenseKey()
    {
        // Validate request.
        check_ajax_referer('lmfwc_show_license_key', 'show');

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            wp_die(__('Invalid request.', 'lmfwc'));
        }

        $license_row = apply_filters('lmfwc_get_license_key', $_POST['id']);
        $license_key = apply_filters('lmfwc_decrypt', $license_row['license_key']);

        wp_send_json($license_key);

        wp_die();
    }

    /**
     * Show all visible license keys.
     *
     * @since  1.0.0
     * @return null
     */
    public function showAllLicenseKeys()
    {
        // Validate request.
        check_ajax_referer('lmfwc_show_all_license_keys', 'show_all');
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            wp_die(__('Invalid request.', 'lmfwc'));
        }

        $license_keys = array();

        foreach (json_decode($_POST['ids']) as $license_key_id) {
            $license_row = apply_filters(
                'lmfwc_get_license_key',
                $license_key_id
            );
            $license_key = apply_filters(
                'lmfwc_decrypt',
                $license_row['license_key']
            );

            $license_keys[$license_key_id] = $license_key;
        }

        wp_send_json($license_keys);

        wp_die();
    }

    /**
     * Hook into the WordPress Order Item Meta Box and display the license key(s)
     *
     * @param int                   $item_id ID
     * @param WC_Order_Item_Product $item    The WooCommerce Item Product object
     * @param WC_Product_Simple     $product The WooCommerce Product object
     * 
     * @since  1.0.0
     * @return null
     */
    public function showOrderedLicenses($item_id, $item, $product)
    {
        // Not a WC_Order_Item_Product object? Nothing to do...
        if (!($item instanceof \WC_Order_Item_Product)) {
            return;
        }

        $license_keys = apply_filters(
            'lmfwc_get_order_license_keys',
            $item->get_order_id(),
            $product->get_id()
        );

        // No license keys? Nothing to do...
        if (!$license_keys) {
            return;
        }

        $html = __('<p>The following license keys have been sold by this order:</p>', 'lmfwc');
        $html .= '<ul class="lmfwc-license-list">';

        if (!Settings::get('lmfwc_hide_license_keys')) {
            foreach ($license_keys as $license_key) {
                $html .= sprintf(
                    '<li></span> <code class="lmfwc-placeholder">%s</code></li>',
                    apply_filters('lmfwc_decrypt', $license_key->license_key)
                );
            }

            $html .= '</ul>';
        } else {
            foreach ($license_keys as $license_key) {
                $html .= sprintf(
                    '<li><code class="lmfwc-placeholder empty" data-id="%d"></code></li>',
                    $license_key->id
                );
            }

            $html .= '</ul>';
            $html .= '<p>';

            $html .= sprintf(
                '<a class="button lmfwc-license-keys-show-all" data-order-id="%d">%s</a>',
                $item->get_order_id(),
                __('Show License Key(s)', 'lmfwc')
            );

            $html .= sprintf(
                '<a class="button lmfwc-license-keys-hide-all" data-order-id="%d">%s</a>',
                $item->get_order_id(),
                __('Hide License Key(s)', 'lmfwc')
            );

            $html .= sprintf(
                '<img class="lmfwc-spinner" data-id="%d" src="%s">',
                $license_key->id, LicensesList::SPINNER_URL
            );

            $html .= '</p>';
        }

        echo $html;
    }
}