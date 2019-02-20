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
     * @var \LicenseManagerForWooCommerce\Crypto
     */
    private $crypto;

    /**
     * FormHandler Constructor.
     */
    public function __construct(
        \LicenseManagerForWooCommerce\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        // Admin POST requests.
        add_action('admin_post_lmfwc_save_generator',      array($this, 'saveGenerator'   ), 10);
        add_action('admin_post_lmfwc_update_generator',    array($this, 'updateGenerator' ), 10);
        add_action('admin_post_lmfwc_import_license_keys', array($this, 'importLicenses'  ), 10);
        add_action('admin_post_lmfwc_add_license_key',     array($this, 'addLicense'      ), 10);

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
        $redirect_url = 'admin.php?page=%s&validation_error=%s';

        if ($_POST['name'] == '' || !is_string($_POST['name'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::ADD_GENERATOR_PAGE, 'invalid_name'));
            wp_die();
        }

        if ($_POST['charset'] == '' || !is_string($_POST['charset'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::ADD_GENERATOR_PAGE, 'invalid_charset'));
            wp_die();
        }

        if ($_POST['chunks'] == '' || !is_numeric($_POST['chunks'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::ADD_GENERATOR_PAGE, 'invalid_chunks'));
            wp_die();
        }

        if ($_POST['chunk_length'] == '' || !is_numeric($_POST['chunk_length'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::ADD_GENERATOR_PAGE, 'invalid_chunk_length'));
            wp_die();
        }

        // Save the generator.
        $result = apply_filters('lmfwc_insert_generator', array(
            'name'         => sanitize_text_field($_POST['name']),
            'charset'      => sanitize_text_field($_POST['charset']),
            'chunks'       => absint($_POST['chunks']),
            'chunk_length' => absint($_POST['chunk_length']),
            'separator'    => sanitize_text_field($_POST['separator']),
            'prefix'       => sanitize_text_field($_POST['prefix']),
            'suffix'       => sanitize_text_field($_POST['suffix']),
            'expires_in'   => absint($_POST['expires_in'])
        ));

        // Redirect according to $result.
        $redirect_url = 'admin.php?page=%s&action=edit&id=%d&status=%s';

        if ($result) {
            wp_redirect(sprintf($redirect_url, AdminMenus::GENERATORS_PAGE, absint($_POST['id']), 'true'));
        } else {
            wp_redirect(sprintf($redirect_url, AdminMenus::GENERATORS_PAGE, absint($_POST['id']), 'failed'));
        }

        wp_die();
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

        // Validate request.
        $redirect_url = 'admin.php?page=%s&action=edit&id=%d&validation_error=%s"';

        if ($_POST['name'] == '' || !is_string($_POST['name'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::EDIT_GENERATOR_PAGE, absint($_POST['id']), 'invalid_name'));
            wp_die();
        }

        if ($_POST['charset'] == '' || !is_string($_POST['charset'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::EDIT_GENERATOR_PAGE, absint($_POST['id']), 'invalid_charset'));
            wp_die();
        }

        if ($_POST['chunks'] == '' || !is_numeric($_POST['chunks'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::EDIT_GENERATOR_PAGE, absint($_POST['id']), 'invalid_chunks'));
            wp_die();
        }

        if ($_POST['chunk_length'] == '' || !is_numeric($_POST['chunk_length'])) {
            wp_redirect(sprintf($redirect_url, AdminMenus::EDIT_GENERATOR_PAGE, absint($_POST['id']), 'invalid_chunk_length'));
            wp_die();
        }

        // Update the generator.
        $result = apply_filters('lmfwc_update_generator', array(
            'id'           => absint($_POST['id']),
            'name'         => sanitize_text_field($_POST['name']),
            'charset'      => sanitize_text_field($_POST['charset']),
            'chunks'       => absint($_POST['chunks']),
            'chunk_length' => absint($_POST['chunk_length']),
            'separator'    => sanitize_text_field($_POST['separator']),
            'prefix'       => sanitize_text_field($_POST['prefix']),
            'suffix'       => sanitize_text_field($_POST['suffix']),
            'expires_in'   => absint($_POST['expires_in'])
        ));

        // Redirect according to $result.
        $redirect_url = 'admin.php?page=%s&action=edit&id=%d&status=%s';

        if ($result) {
            wp_redirect(sprintf($redirect_url, AdminMenus::EDIT_GENERATOR_PAGE, absint($_POST['id']), 'true'));
        } else {
            wp_redirect(sprintf($redirect_url, AdminMenus::EDIT_GENERATOR_PAGE, absint($_POST['id']), 'failed'));
        }

        wp_die();
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

        wp_die();
    }

    public function showLicenseKey()
    {
        // Validate request.
        check_ajax_referer('lmfwc_show_license_key', 'show');
        if ($_SERVER['REQUEST_METHOD'] != 'POST') wp_die(__('Invalid request.', 'lmfwc'));

        $license_key = Database::getLicenseKey(intval($_POST['id']));

        wp_send_json($license_key);

        wp_die();
    }

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
                    $this->crypto->decrypt($license_key->license_key)
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