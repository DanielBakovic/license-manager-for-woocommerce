<?php

namespace LicenseManager\Classes;

/**
 * LicenseManager FormHandler.
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * FormHandler class.
 */
class FormHandler
{
    const TEMP_TXT_FILE = 'import.tmp.txt';

    private $crypto;

    /**
     * FormHandler Constructor.
     */
    public function __construct(
        \LicenseManager\Classes\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        // Admin POST requests.
        add_action('admin_post_lima_save_generator',      array($this, 'saveGenerator' ), 10);
        add_action('admin_post_lima_import_license_keys', array($this, 'importLicenses'), 10);
        add_action('admin_post_lima_add_license_key',     array($this, 'addLicense'    ), 10);

        // AJAX calls.
        add_action('wp_ajax_lima_show_license_key', array($this, 'showLicenseKey'), 10);

        // Meta box handlers
        add_action('save_post', array($this, 'assignGeneratorToProduct'), 10);

        // WooCommerce
        add_action('woocommerce_after_order_itemmeta', array($this, 'addLicenseKeysToOrder'), 10, 3);
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
     *
     */
    public static function saveGenerator($args)
    {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . \LicenseManager\Classes\Setup::GENERATORS_TABLE_NAME,
            array(
                'name'         => $_POST['name'],
                'charset'      => $_POST['charset'],
                'chunks'       => $_POST['chunks'],
                'chunk_length' => $_POST['chunk_length'],
                'separator'    => $_POST['separator'],
                'prefix'       => $_POST['prefix'],
                'suffix'       => $_POST['suffix'],
                'expires_in'   => $_POST['expires_in']
            ),
            array('%s', '%s', '%d', '%d', '%s', '%s', '%s')
        );

        wp_redirect(admin_url('admin.php?page=license_manager_generators'));
        exit;
    }

    /**
     * Import licenses from a compatible CSV or TXT file into the database.
     *
     * @since 1.0.0
     *
     */
    public function importLicenses()
    {
        // Check the nonce.
        check_admin_referer('lima-import');

        // Check the file extension, return if not .txt
        if (!pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) == 'txt' || $_FILES['file']['type'] != 'text/plain') {
            return null;
        }

        // File upload file, return with error.
        if (!move_uploaded_file($_FILES['file']['tmp_name'], LM_ETC_DIR . self::TEMP_TXT_FILE)) {
            return null;
        }

        // Check for invalid file contents.
        if (!is_array($license_keys = file(LM_ETC_DIR . self::TEMP_TXT_FILE, FILE_IGNORE_NEW_LINES))) {
            return null;
        }

        $result = apply_filters(
            'lima_save_imported_license_keys',
            array(
                'license_keys' => $license_keys,
                'activate'     => array_key_exists('activate', $_POST) ? true : false,
                'product_id'   => intval($_POST['product'])
            )
        );

        if ($result['failed'] == 0 && $result['added'] == 0) {
            wp_redirect(admin_url('admin.php?page=license_manager_add_import&import=error'));
        }
        if ($result['failed'] == 0 && $result['added'] > 0) {
            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=license_manager_add_import&import=success&added=%d', $result['added'])
                )
            );
        }
        if ($result['failed'] > 0 && $result['added'] == 0) {
            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=license_manager_add_import&import=failed&rejected=%d', $result['failed'])
                )
            );
        }
        if ($result['failed'] > 0 && $result['added'] > 0) {
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=license_manager_add_import&import=mixed&added=%d&rejected=%d',
                        $result['failed']
                    )
                )
            );
        }
    }

    /**
     * Add a single license key to the database.
     *
     * @since 1.0.0
     *
     */
    public function addLicense()
    {
        // Check the nonce.
        check_admin_referer('lima-add');

        $result = apply_filters(
            'lima_save_added_license_key',
            array(
                'license_key' => sanitize_text_field($_POST['license_key']),
                'activate'    => array_key_exists('activate', $_POST) ? true : false,
                'product_id'  => intval($_POST['product'])
            )
        );

        if ($result) {
            wp_redirect(admin_url('admin.php?page=license_manager_add_import&add=success'));
        } else {
            wp_redirect(admin_url('admin.php?page=license_manager_add_import&add=failed'));
        }
    }

    public function showLicenseKey()
    {
        // Validate request.
        check_ajax_referer('lima_show_license_key', 'show');
        if ($_SERVER['REQUEST_METHOD'] != 'POST') wp_die('Invalid request.');

        $license_key = Database::getLicenseKey(intval($_POST['id']));

        wp_send_json($license_key);

        wp_die();
    }

    public function addLicenseKeysToOrder($item_id, $item, $product) {
        //var_dump(array($item_id, $item, $product));
        echo 'Your license key is: <code>1002-2301-9534-1321</code>, it expires on 2018-12-24';
    }

    /**
     * Hook into 'save_post' and assign a generator to the product (if  selected).
     *
     * @since 1.0.0
     *
     * @param int $post_id - WordPress Post ID.
     */
    public static function assignGeneratorToProduct($post_id)
    {
        // This is not a product.
        if (!array_key_exists('post_type', $_POST) || $_POST['post_type'] != 'product') {
            return;
        }

        // The checkbox wasn't selected.
        if (!array_key_exists('lima-sell-licenses', $_POST)) {
            return;
        }

        // No generator was selected, return with error.
        if ($_POST['lima-generator'] == '') {
            $error = new \WP_Error(3, 'You did not select a generator.');
            return;
        }

        // Generator already exists.
        if (get_post_meta($post_id, '_lima_generator_id', true)) {
            # code...
        }

        // Assign the selected generator to this product.
        update_post_meta($post_id, '_lima_generator_id', $_POST['lima-generator']);
    }
}