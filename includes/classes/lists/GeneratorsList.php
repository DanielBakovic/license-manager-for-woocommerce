<?php

namespace LicenseManager\Classes\Lists;

/**
 * Create the Generators list
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class GeneratorsList extends \WP_List_Table
{
    protected $table;

    public function __construct()
    {
        global $wpdb;

        parent::__construct([
            'singular' => __('Generator', 'lima'),
            'plural'   => __('Generators', 'lima'),
            'ajax'     => false
        ]);

        $this->table = $wpdb->prefix . \LicenseManager\Classes\Setup::GENERATORS_TABLE_NAME;
    }

    public static function get_orders($per_page = 20, $page_number = 1)
    {
        global $wpdb;
        $table = $wpdb->prefix . \LicenseManager\Classes\Setup::GENERATORS_TABLE_NAME;
        $sql = "SELECT * FROM $table";
        $sql .= ' ORDER BY ' . (empty($_REQUEST['orderby']) ? 'id' : esc_sql($_REQUEST['orderby']));
        $sql .= ' '          . (empty($_REQUEST['order'])   ? 'ASC'  : esc_sql($_REQUEST['order']));
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    public static function record_count()
    {
        global $wpdb;
        $table = $wpdb->prefix . \LicenseManager\Classes\Setup::GENERATORS_TABLE_NAME;

        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    public function no_items()
    {
        _e('No generators found.', 'lima');
    }


    public function column_name($item)
    {
        $title = '<strong>' . $item['name'] . '</strong>';

        $actions = [
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&generator_id=%s&_wpnonce=%s">%s</a>',
                esc_attr($_REQUEST['page']),
                'delete',
                absint($item['id']),
                wp_create_nonce('lima_delete_generator'),
                __('Delete', 'lima')
            ),
            'edit' => sprintf(
                '<a href="?page=%s&action=%s&generator_id=%s&_wpnonce=%s">%s</a>',
                'license_manager_generators_edit',
                'edit',
                absint($item['id']),
                wp_create_nonce('lima_edit_generator'),
                __('Edit', 'lima')
            )
        ];

        return $title . $this->row_actions($actions);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'charset':
                ($item['charset'] == '') ? $charset = '' : $charset = sprintf('<code>%s</code>', $item['charset']);
                return $charset;
            case 'separator':
                ($item['separator'] == '') ? $sep = '' : $sep = sprintf('<code>%s</code>', $item['separator']);
                return $sep;
            case 'prefix':
                ($item['prefix'] == '') ? $prefix = '' : $prefix = sprintf('<code>%s</code>', $item['prefix']);
                return $prefix;
            case 'suffix':
                ($item['suffix'] == '') ? $suffix = '' : $suffix = sprintf('<code>%s</code>', $item['suffix']);
                return $suffix;
            case 'expires_in':
                ($item['expires_in'] == '') ? $expires_in = __('never', 'lima') : $expires_in = sprintf('%d %s', $item['expires_in'], __('day(s)', 'lima'));
                return $expires_in;
            default:
                return $item[$column_name];
        }

        return $item[$column_name];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="bulk[]" value="%s" />', $item['id']);
    }

    public function get_columns()
    {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'id'           => __('ID', 'lima'),
            'name'         => __('Name', 'lima'),
            'charset'      => __('Character map', 'lima'),
            'chunks'       => __('Number of chunks', 'lima'),
            'chunk_length' => __('Chunk length', 'lima'),
            'separator'    => __('Separator', 'lima'),
            'prefix'       => __('Prefix', 'lima'),
            'suffix'       => __('Suffix', 'lima'),
            'expires_in'   => __('Expires in', 'lima'),
        );

        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
        );

        return $sortable_columns;
    }

    public function get_bulk_actions()
    {
        $actions = [
            'delete' => __('Delete', 'lima'),
        ];

        return $actions;
    }

    public function process_bulk_action()
    {
        $action = $this->current_action();

        switch ($action) {
            case 'delete':
                // Handle the bulk delete request.
                if (isset($_REQUEST['bulk'])) {
                    $this->delete_generators($_REQUEST['bulk']);
                // Handle the single delete request.
                } elseif (isset($_REQUEST['generator_id'])) {
                    // Check nonce.
                    if (!wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'lima_delete_generator')) {
                        die('Go get a life script kiddies');
                    } else {
                        $this->delete_generators($_REQUEST['generator_id']);
                    }
                }
                // Redirect to URL.
                wp_redirect(admin_url('admin.php?page=license_manager_generators'));
                break;
            default:
                break;
        }
    }

    public function prepare_items()
    {
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
        );

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('orders_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $this->items = self::get_orders($per_page, $current_page);
    }

    /**
     * Bulk deletes the generators from the table by an array of ID's.
     *
     * @since 1.0.0
     *
     * @param array $ids - ID's corresponding to the "id" field in the custom table.
     *
     * @todo Retrieve the default parameters from the user settings.
     *
     * @return string
     */
    public function delete_generators($ids)
    {
        global $wpdb;

        if (!is_array($ids)) $ids = (array)$ids;

        foreach ($ids as $id) {
            $wpdb->delete($this->table, array('id' => $id));
        }
    }
}
