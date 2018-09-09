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
    const TEMP_CSV_FILE = 'import.tmp.csv';
    const TEMP_TXT_FILE = 'import.tmp.txt';

    /**
     * FormHandler Constructor.
     */
    public function __construct()
    {
        // Admin POST requests.
        add_action('admin_post_LM_save_generator',      array($this, 'saveGenerator' ), 10);
        add_action('admin_post_LM_import_licence_keys', array($this, 'importLicences'), 10);

        // Meta box handlers
        add_action('save_post', array($this, 'assignGeneratorToProduct'), 10);
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
     * Import licences from a compatible CSV or TXT file into the database.
     *
     * @since 1.0.0
     *
     */
    public function importLicences()
    {
        // Check the nonce.
        check_admin_referer('lima-import');

        //$extension = pathinfo($_POST['file'], PATHINFO_EXTENSION);
        $extension = 'txt';

        // CSV Import
        if ($extension == 'csv') {
            # code...

        // TXT Import
        } elseif ($extension == 'txt') {

            // File upload file, return with error.
            if (!move_uploaded_file($_FILES['file']['tmp_name'], LM_ETC_DIR . self::TEMP_TXT_FILE)) {
                echo "There was an error uploading the file, please try again!";
                return;
            }

            $license_keys = file(LM_ETC_DIR . self::TEMP_TXT_FILE, FILE_IGNORE_NEW_LINES);

            $test = apply_filters('lima_import_license_keys', $license_keys);

        } else {
            // Wrong file type.
        }

        echo '<pre>';
        var_dump($test);
        exit();
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
        if ($_POST['post_type'] != 'product') {
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