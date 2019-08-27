<?php

namespace LicenseManagerForWooCommerce;

use LicenseManagerForWooCommerce\Enums\LicenseSource;
use LicenseManagerForWooCommerce\Lists\LicensesList;
use LicenseManagerForWooCommerce\Models\Resources\ApiKey as ApiKeyResourceModel;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\ApiKey as ApiKeyResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\Generator as GeneratorResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use WC_Order_Item_Product;
use WC_Product_Simple;

defined('ABSPATH') || exit;

class FormHandler
{
    /**
     * Temporary import file name
     */
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
        $generator = GeneratorResourceRepository::instance()->insert(
            array(
                'name'                => $_POST['name'],
                'charset'             => $_POST['charset'],
                'chunks'              => $_POST['chunks'],
                'chunk_length'        => $_POST['chunk_length'],
                'times_activated_max' => $_POST['times_activated_max'],
                'separator'           => $_POST['separator'],
                'prefix'              => $_POST['prefix'],
                'suffix'              => $_POST['suffix'],
                'expires_in'          => $_POST['expires_in']
            )
        );

        if ($generator) {
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
     */
    public function updateGenerator()
    {
        // Verify the nonce.
        check_admin_referer('lmfwc_update_generator');

        $generatorId = absint($_POST['id']);

        // Validate request.
        if ($_POST['name'] == '' || !is_string($_POST['name'])) {
            AdminNotice::error(__('The Generator name is invalid.', 'lmfwc'));

            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::GENERATORS_PAGE,
                        $generatorId
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
                        $generatorId
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
                        $generatorId
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
                        $generatorId
                    )
                )
            );
            exit();
        }

        // Update the generator.
        $generator = GeneratorResourceRepository::instance()->update(
            $_POST['id'],
            array(
                'name'                => $_POST['name'],
                'charset'             => $_POST['charset'],
                'chunks'              => $_POST['chunks'],
                'chunk_length'        => $_POST['chunk_length'],
                'times_activated_max' => $_POST['times_activated_max'],
                'separator'           => $_POST['separator'],
                'prefix'              => $_POST['prefix'],
                'suffix'              => $_POST['suffix'],
                'expires_in'          => $_POST['expires_in']
            )
        );

        // Redirect according to $result.
        if (!$generator) {
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
     */
    public function importLicenseKeys()
    {
        // Check the nonce.
        check_admin_referer('lmfwc_import_license_keys');

        $licenseKeys = null;
        $ext         = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $mimes       = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');

        if (!in_array($ext, array('txt', 'csv'))
            || !in_array($_FILES['file']['type'], $mimes)
        ) {
            AdminNotice::error(
                __('Invalid file type, only TXT and CSV allowed.', 'lmfwc')
            );

            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=import',
                    AdminMenus::LICENSES_PAGE
                )
            );

            exit();
        }

        $fileName = $_FILES['file']['tmp_name'];
        $filePath = LMFWC_ASSETS_DIR . self::TEMP_IMPORT_FILE;

        // File upload file, return with error.
        if (!move_uploaded_file($fileName, $filePath)) {
            return null;
        }

        // Handle TXT file uploads
        if ($ext == 'txt') {
            $licenseKeys = file(
                LMFWC_ASSETS_DIR . self::TEMP_IMPORT_FILE, FILE_IGNORE_NEW_LINES
            );

            // Check for invalid file contents.
            if (!is_array($licenseKeys)) {
                AdminNotice::error(__('Invalid file content.', 'lmfwc'));

                wp_redirect(
                    sprintf(
                        'admin.php?page=%s&action=import',
                        AdminMenus::LICENSES_PAGE
                    )
                );
                exit();
            }
        }

        // Handle CSV file uploads
        if ($ext == 'csv') {
            $licenseKeys = array();

            if (($handle = fopen(LMFWC_ASSETS_DIR . self::TEMP_IMPORT_FILE, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    if ($data && is_array($data) && count($data) > 0) {
                        $licenseKeys[] = $data[0];
                    }
                }

                fclose($handle);
            }
        }

        // Save the imported keys.
        try {
            $result = apply_filters(
                'lmfwc_insert_imported_license_keys',
                $licenseKeys,
                $_POST['status'],
                $_POST['order_id'],
                $_POST['product_id'],
                $_POST['valid_for'],
                $_POST['times_activated_max']
            );
        } catch (\Exception $e) {
            AdminNotice::error(
                __('There was a problem importing the license keys.', 'lmfwc')
            );
            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=import',
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
                    'admin.php?page=%s&action=import',
                    AdminMenus::LICENSES_PAGE
                )
            );
            exit();
        }

        if ($result['failed'] == 0 && $result['added'] > 0) {
            AdminNotice::success(
                sprintf(
                    __('%d license key(s) added successfully.', 'lmfwc'),
                    intval($result['added'])
                )
            );
            wp_redirect(
                sprintf(
                    'admin.php?page=%s&action=import',
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
                    'admin.php?page=%s&action=import',
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
                    'admin.php?page=%s&action=import',
                    AdminMenus::LICENSES_PAGE
                )
            );

            exit();
        }
    }

    /**
     * Add a single license key to the database.
     */
    public function addLicenseKey()
    {
        // Check the nonce
        check_admin_referer('lmfwc_add_license_key');

        /** @var LicenseResourceModel $license */
        $license = LicenseResourceRepository::instance()->insert(
            array(
                'order_id'            => $_POST['order_id'],
                'product_id'          => $_POST['product_id'],
                'license_key'         => apply_filters('lmfwc_encrypt', $_POST['license_key']),
                'hash'                => apply_filters('lmfwc_hash', $_POST['license_key']),
                'valid_for'           => $_POST['valid_for'],
                'source'              => LicenseSource::IMPORT,
                'status'              => $_POST['status'],
                'times_activated_max' => $_POST['times_activated_max']
            )
        );

        // Redirect with message
        if ($license) {
            AdminNotice::success(
                __('1 license key(s) added successfully.', 'lmfwc')
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
     */
    public function updateLicenseKey()
    {
        // Check the nonce
        check_admin_referer('lmfwc_update_license_key');

        /** @var LicenseResourceModel $license */
        $license = LicenseResourceRepository::instance()->update(
            $_POST['license_id'],
            array(
                'order_id'            => $_POST['order_id'],
                'product_id'          => $_POST['product_id'],
                'license_key'         => apply_filters('lmfwc_encrypt', $_POST['license_key']),
                'hash'                => apply_filters('lmfwc_hash', $_POST['license_key']),
                'valid_for'           => $_POST['valid_for'],
                'source'              => $_POST['source'],
                'status'              => $_POST['status'],
                'times_activated_max' => $_POST['times_activated_max']
            )
        );

        // Set the admin notice
        if ($license) {
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
            $error = __('Permissions are missing.', 'lmfwc');
        }

        $keyId       = absint($_POST['id']);
        $description = sanitize_text_field(wp_unslash($_POST['description']));
        $permissions = (in_array($_POST['permissions'], array('read', 'write', 'read_write'))) ? sanitize_text_field($_POST['permissions']) : 'read';
        $userId      = absint($_POST['user']);
        $action      = sanitize_text_field(wp_unslash($_POST['lmfwc_action']));

        // Check if current user can edit other users.
        if ($userId && !current_user_can('edit_user', $userId)) {
            if (get_current_user_id() !== $userId) {
                $error = __('You do not have permission to assign API keys to the selected user.', 'lmfwc');
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

        if ($action === 'create') {
            $consumerKey    = 'ck_' . wc_rand_hash();
            $consumerSecret = 'cs_' . wc_rand_hash();

            /** @var ApiKeyResourceModel $apiKey */
            $apiKey = ApiKeyResourceRepository::instance()->insert(
                array(
                    'user_id'         => $userId,
                    'description'     => $description,
                    'permissions'     => $permissions,
                    'consumer_key'    => wc_api_hash($consumerKey),
                    'consumer_secret' => $consumerSecret,
                    'truncated_key'   => substr($consumerKey, -7),
                )
            );

            if ($apiKey) {
                AdminNotice::success(
                    __('API key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'lmfwc')
                );
                set_transient('lmfwc_consumer_key', $consumerKey, 60);
                set_transient('lmfwc_api_key', $apiKey, 60);
            }

            else {
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
        }

        elseif ($action === 'edit') {
            $apiKey = ApiKeyResourceRepository::instance()->update(
                $keyId,
                array(
                    'user_id'     => $userId,
                    'description' => $description,
                    'permissions' => $permissions
                )
            );

            if ($apiKey) {
                AdminNotice::success(__('API key updated successfully.', 'lmfwc'));
            }

            else {
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
     */
    public function showLicenseKey()
    {
        // Validate request.
        check_ajax_referer('lmfwc_show_license_key', 'show');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die(__('Invalid request.', 'lmfwc'));
        }

        /** @var LicenseResourceModel $license */
        $license = LicenseResourceRepository::instance()->findBy(array('id' => $_POST['id']));

        wp_send_json($license->getDecryptedLicenseKey());

        wp_die();
    }

    /**
     * Shows all visible license keys.
     */
    public function showAllLicenseKeys()
    {
        // Validate request.
        check_ajax_referer('lmfwc_show_all_license_keys', 'show_all');

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            wp_die(__('Invalid request.', 'lmfwc'));
        }

        $licenseKeysIds = array();

        foreach (json_decode($_POST['ids']) as $licenseKeyId) {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->find($licenseKeyId);

            $licenseKeysIds[$licenseKeyId] = $license->getDecryptedLicenseKey();
        }

        wp_send_json($licenseKeysIds);
    }

    /**
     * Hook into the WordPress Order Item Meta Box and display the license key(s).
     *
     * @param int                   $itemId
     * @param WC_Order_Item_Product $item
     * @param WC_Product_Simple     $product
     */
    public function showOrderedLicenses($itemId, $item, $product)
    {
        // Not a WC_Order_Item_Product object? Nothing to do...
        if (!($item instanceof WC_Order_Item_Product)) {
            return;
        }

        /** @var LicenseResourceModel[] $licenses */
        $licenses = LicenseResourceRepository::instance()->findAllBy(
            array(
                'order_id' => $item->get_order_id(),
                'product_id' => $product->get_id()
            )
        );

        // No license keys? Nothing to do...
        if (!$licenses) {
            return;
        }

        $html = sprintf('<p>%s:</p>', __('The following license keys have been sold by this order', 'lmfwc'));
        $html .= '<ul class="lmfwc-license-list">';

        if (!Settings::get('lmfwc_hide_license_keys')) {
            /** @var LicenseResourceModel $license */
            foreach ($licenses as $license) {
                $html .= sprintf(
                    '<li></span> <code class="lmfwc-placeholder">%s</code></li>',
                    $license->getDecryptedLicenseKey()
                );
            }

            $html .= '</ul>';
        }

        else {
            /** @var LicenseResourceModel $license */
            foreach ($licenses as $license) {
                $html .= sprintf(
                    '<li><code class="lmfwc-placeholder empty" data-id="%d"></code></li>',
                    $license->getId()
                );
            }

            $html .= '</ul>';
            $html .= '<p>';

            $html .= sprintf(
                '<a class="button lmfwc-license-keys-show-all" data-order-id="%d">%s</a>',
                $item->get_order_id(),
                __('Show license key(s)', 'lmfwc')
            );

            $html .= sprintf(
                '<a class="button lmfwc-license-keys-hide-all" data-order-id="%d">%s</a>',
                $item->get_order_id(),
                __('Hide license key(s)', 'lmfwc')
            );

            $html .= sprintf(
                '<img class="lmfwc-spinner" alt="%s" src="%s">',
                __('Please wait...', 'lmfwc'),
                LicensesList::SPINNER_URL
            );

            $html .= '</p>';
        }

        echo $html;
    }
}