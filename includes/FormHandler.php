<?php

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Lists\LicensesList;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce FormHandler.
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class FormHandler
{
    const TEMP_TXT_FILE = 'import.tmp.txt';

    /**
     * FormHandler Constructor.
     */
    public function __construct() {
        // Admin POST requests.
        add_action('admin_post_lmfwc_save_generator',      array($this, 'saveGenerator'   ), 10);
        add_action('admin_post_lmfwc_update_generator',    array($this, 'updateGenerator' ), 10);
        add_action('admin_post_lmfwc_import_license_keys', array($this, 'importLicenses'  ), 10);
        add_action('admin_post_lmfwc_add_license_key',     array($this, 'addLicense'      ), 10);
        add_action('admin_post_lmfwc_api_key_update',      array($this, 'apiKeyUpdate'    ), 10);

        // AJAX calls.
        add_action('wp_ajax_lmfwc_show_license_key',      array($this, 'showLicenseKey'    ), 10);
        add_action('wp_ajax_lmfwc_show_all_license_keys', array($this, 'showAllLicenseKeys'), 10);

        // WooCommerce
        add_action('woocommerce_after_order_itemmeta', array($this, 'showOrderedLicenses'), 10, 3);
    }

    /**
     * Save the generator to the database.
     *
     * @since 1.0.0
     *
     * @param string $args['name']         - Generator name.
     * @param string $args['charset']      - Character map used for key generation.
     * @param int    $args['chunks']       - Number of chunks.
     * @param int    $args['chunk_length'] - Chunk length.
     * @param string $args['separator']    - Separator used.
     * @param string $args['prefix']       - License key prefix.
     * @param string $args['suffis']       - License key suffix.
     * @param string $args['expires_in']   - Number of days for which the license is valid.
     */
    public function saveGenerator($args)
    {
        // Verify the nonce.
        check_admin_referer('lmfwc_save_generator');

        // Validate request.
        if ($_POST['name'] == '' || !is_string($_POST['name'])) {
            AdminNotice::add('error', __('Generator name is missing.', 'lmfwc'), 18);
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::ADD_GENERATOR_PAGE)));
            exit();
        }

        if ($_POST['charset'] == '' || !is_string($_POST['charset'])) {
            AdminNotice::add('error', __('The charset is invalid.', 'lmfwc'), 18);
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::ADD_GENERATOR_PAGE)));
            exit();
        }

        if ($_POST['chunks'] == '' || !is_numeric($_POST['chunks'])) {
            AdminNotice::add('error', __('Only integer values allowed for chunks.', 'lmfwc'), 18);
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::ADD_GENERATOR_PAGE)));
            exit();
        }

        if ($_POST['chunk_length'] == '' || !is_numeric($_POST['chunk_length'])) {
            AdminNotice::add('error', __('Only integer values allowed for chunk length.', 'lmfwc'), 18);
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::ADD_GENERATOR_PAGE)));
            exit();
        }

        // Save the generator.
        $result = apply_filters(
            'lmfwc_insert_generator',
            $_POST['name'],
            $_POST['charset'],
            $_POST['chunks'],
            $_POST['chunk_length'],
            $_POST['separator'],
            $_POST['prefix'],
            $_POST['suffix'],
            $_POST['expires_in']
        );

        if ($result) {
            AdminNotice::add('success', __('The generator was added successfully.', 'lmfwc'));
        } else {
            AdminNotice::addErrorSupportForum(19);
        }

        wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)));

        exit();
    }

    /**
     * Update an existing generator.
     *
     * @since 1.0.0
     */
    public function updateGenerator()
    {
        // Verify the nonce.
        check_admin_referer('lmfwc_update_generator');

        $generator_id = absint($_POST['id']);

        // Validate request.
        if ($_POST['name'] == '' || !is_string($_POST['name'])) {
            AdminNotice::add(
                'error',
                __('The Generator name is invalid.', 'lmfwc'),
                30
            );
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::EDIT_GENERATOR_PAGE,
                        $generator_id
                    )
                )
            );
            exit();
        }

        if ($_POST['charset'] == '' || !is_string($_POST['charset'])) {
            AdminNotice::add(
                'error',
                __('The Generator charset is invalid.', 'lmfwc'),
                31
            );
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::EDIT_GENERATOR_PAGE,
                        $generator_id
                    )
                )
            );
            exit();
        }

        if ($_POST['chunks'] == '' || !is_numeric($_POST['chunks'])) {
            AdminNotice::add(
                'error',
                __('The Generator chunks are invalid.', 'lmfwc'),
                32
            );
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::EDIT_GENERATOR_PAGE,
                        $generator_id
                    )
                )
            );
            exit();
        }

        if ($_POST['chunk_length'] == '' || !is_numeric($_POST['chunk_length'])) {
            AdminNotice::add(
                'error',
                __('The Generator chunk length is invalid.', 'lmfwc'),
                33
            );
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::EDIT_GENERATOR_PAGE,
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
            $_POST['separator'],
            $_POST['prefix'],
            $_POST['suffix'],
            $_POST['expires_in']
        );

        // Redirect according to $result.
        if (!$result) {
            AdminNotice::addErrorSupportForum(34);
        } else {
            AdminNotice::add(
                'success',
                __('The Generator was updated successfully.')
            );
        }

        wp_redirect(
            admin_url(
                sprintf(
                    'admin.php?page=%s&action=edit&id=%d',
                    AdminMenus::EDIT_GENERATOR_PAGE,
                    $generator_id
                )
            )
        );
        exit();
    }

    /**
     * Import licenses from a compatible CSV or TXT file into the database.
     *
     * @since 1.0.0
     */
    public function importLicenses()
    {
        // Check the nonce.
        check_admin_referer('lmfwc-import');

        // Check the file extension, return if not .txt
        if (!pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) == 'txt' || $_FILES['file']['type'] != 'text/plain') {
            return null;
        }

        // File upload file, return with error.
        if (!move_uploaded_file($_FILES['file']['tmp_name'], LMFWC_ETC_DIR . self::TEMP_TXT_FILE)) {
            return null;
        }

        // Check for invalid file contents.
        if (!is_array($license_keys = file(LMFWC_ETC_DIR . self::TEMP_TXT_FILE, FILE_IGNORE_NEW_LINES))) {
            return null;
        }

        // Save the imported keys.
        $result = apply_filters('lmfwc_insert_imported_license_keys', array(
            'license_keys' => $license_keys,
            'activate'     => array_key_exists('activate', $_POST) ? true : false,
            'product_id'   => intval($_POST['product'])
        ));

        // Delete the temporary file now that we're done.
        unlink(LMFWC_ETC_DIR . self::TEMP_TXT_FILE);

        // Redirect according to $result.
        if ($result['failed'] == 0 && $result['added'] == 0) {
            AdminNotice::addErrorSupportForum(3);
            wp_redirect(sprintf('admin.php?page=%s', AdminMenus::ADD_IMPORT_PAGE));
            wp_die();
        }

        if ($result['failed'] == 0 && $result['added'] > 0) {
            AdminNotice::add(
                'success',
                sprintf(
                    __('%d key(s) have been imported successfully.', 'lmfwc'),
                    intval($result['added'])
                )
            );
            wp_redirect(sprintf('admin.php?page=%s', AdminMenus::ADD_IMPORT_PAGE));
            wp_die();
        }

        if ($result['failed'] > 0 && $result['added'] == 0) {
            AdminNotice::addErrorSupportForum(4);
            wp_redirect(sprintf('admin.php?page=%s', AdminMenus::ADD_IMPORT_PAGE));
            wp_die();
        }

        if ($result['failed'] > 0 && $result['added'] > 0) {
            AdminNotice::add(
                'warning',
                sprintf(
                    __('%d key(s) have been imported and %d key(s) were not imported.', 'lmfwc'),
                    intval($result['added']),
                    intval($result['failed'])
                )
            );
            wp_redirect(sprintf('admin.php?page=%s', AdminMenus::ADD_IMPORT_PAGE));
            wp_die();
        }
    }

    /**
     * Add a single license key to the database.
     *
     * @since 1.0.0
     */
    public function addLicense()
    {
        // Check the nonce.
        check_admin_referer('lmfwc-add');

        // Save the license key.
        $result = apply_filters('lmfwc_insert_added_license_key', array(
            'license_key' => sanitize_text_field($_POST['license_key']),
            'activate'    => array_key_exists('activate', $_POST) ? true : false,
            'product_id'  => intval($_POST['product']),
            'valid_for'   => ($_POST['valid_for']) ? intval($_POST['valid_for']) : null
        ));

        if ($result) {
            AdminNotice::add(
                'success',
                __('Your license key has been added successfully.', 'lmfwc')
            );
        } else {
            AdminNotice::addErrorSupportForum(5);
        }

        wp_redirect(sprintf('admin.php?page=%s', AdminMenus::ADD_IMPORT_PAGE));
    }

    /**
     * Store a created API key to the database or updates an existing key.
     *
     * @since 1.1.0
     */
    public function apiKeyUpdate()
    {
        // Check the nonce.
        check_admin_referer('lmfwc-api-key-update');

        $error = null;

        if (empty($_POST['description'])) {
            $error = __('Description is missing.', 'lmfwc');
            $code = 10;
        }
        if (empty($_POST['user']) || $_POST['user'] == -1) {
            $error = __('User is missing.', 'lmfwc');
            $code = 11;
        }
        if (empty($_POST['permissions'])) {
            $error = __('Permissions is missing.', 'lmfwc');
            $code = 12;
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
                $code = 13;
            }
        }

        if ($error) {
            AdminNotice::add('error', $error, $code);
            wp_redirect(sprintf('admin.php?page=%s&tab=rest_api&create_key=1', AdminMenus::SETTINGS_PAGE));
            exit();
        }

        if ($action == 'create') {
            if ($data = apply_filters('lmfwc_insert_api_key', $user_id, $description, $permissions)) {
                AdminNotice::add(
                    'success',
                    __('API Key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'lmfwc')
                );
                set_transient('lmfwc_api_key', $data, 60);
            } else {
                AdminNotice::addErrorSupportForum(14);
            }

            wp_redirect(sprintf('admin.php?page=%s&tab=rest_api&show_key=1', AdminMenus::SETTINGS_PAGE));
            exit();
        } elseif ($action == 'edit') {

            if (apply_filters('lmfwc_update_api_key', $key_id, $user_id, $description, $permissions)) {
                AdminNotice::add('success', __('API Key updated successfully.', 'lmfwc'));
            } else {
                AdminNotice::addErrorSupportForum(16);
            }

            wp_redirect(sprintf('admin.php?page=%s&tab=rest_api', AdminMenus::SETTINGS_PAGE));
            exit();
        }
    }

    /**
     * Show a single license key.
     *
     * @since 1.0.0
     */
    public function showLicenseKey()
    {
        // Validate request.
        check_ajax_referer('lmfwc_show_license_key', 'show');
        if ($_SERVER['REQUEST_METHOD'] != 'POST') wp_die(__('Invalid request.', 'lmfwc'));

        $license_key = Database::getLicenseKey(intval($_POST['id']));

        wp_send_json($license_key);

        wp_die();
    }

    /**
     * Show all visible license keys.
     *
     * @since 1.0.0
     */
    public function showAllLicenseKeys()
    {
        // Validate request.
        check_ajax_referer('lmfwc_show_all_license_keys', 'show_all');
        if ($_SERVER['REQUEST_METHOD'] != 'POST') wp_die(__('Invalid request.', 'lmfwc'));

        $license_keys = array();

        foreach (json_decode($_POST['ids']) as $license_key_id) {
            $license_keys[$license_key_id] = Database::getLicenseKey(intval($license_key_id));
        }

        wp_send_json($license_keys);

        wp_die();
    }

    /**
     * Hook into the WordPress Order Item Meta Box and display the license key(s)
     *
     * @since 1.0.0
     *
     * @param int                   $item_id
     * @param WC_Order_Item_Product $item
     * @param WC_Product_Simple     $product
     */
    public function showOrderedLicenses($item_id, $item, $product) {
        // Not a WC_Order_Item_Product object? Nothing to do...
        if (!($item instanceof \WC_Order_Item_Product)) return;

        // No license keys? Nothing to do...
        if (!$license_keys = Database::getOrderedLicenseKeys($item->get_order_id(), $item->get_product_id())) return;

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