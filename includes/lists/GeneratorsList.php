<?php

namespace LicenseManager\Lists;

use \LicenseManager\AdminMenus;
use \LicenseManager\Logger;
use \LicenseManager\Setup;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create the Generators list
 *
 * @version 1.0.0
 * @since 1.0.0
 */
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

        $this->table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;
    }

    public static function get_orders($per_page = 20, $page_number = 1)
    {
        global $wpdb;

        $table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

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

        $table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

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
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
                AdminMenus::GENERATORS_PAGE,
                'delete',
                absint($item['id']),
                wp_create_nonce('delete'),
                __('Delete', 'lima')
            ),
            'edit' => sprintf(
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
                AdminMenus::EDIT_GENERATOR_PAGE,
                'edit',
                absint($item['id']),
                wp_create_nonce('edit'),
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
                (!$item['expires_in']) ? $expires_in = __('non-expiring', 'lima') : $expires_in = sprintf('%d %s', $item['expires_in'], __('day(s)', 'lima'));
                return $expires_in;
            default:
                return $item[$column_name];
        }

        return $item[$column_name];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
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
                $this->verifyNonce('delete');
                $this->deleteGenerators();
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

    private function verifyNonce($nonce_action)
    {
        if (
            !wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action) &&
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])
        ) {
            wp_redirect(admin_url(sprintf('admin.php?page=%s&lima_nonce_status=invalid', AdminMenus::GENERATORS_PAGE)));
        }
    }

    /**
     * Bulk deletes the generators from the table by a single ID or an array of ID's.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function deleteGenerators()
    {
        $selected_generators = (array)$_REQUEST['id'];

        foreach ($selected_generators as $generator_id) {
            if ($products = apply_filters('lima_get_assigned_products', array('generator_id' => absint($generator_id)))) {
                continue;
            } else {
                $result = apply_filters(
                    'lima_delete_generators',
                    array(
                        'ids' => (array)$generator_id
                    )
                );
            }
        }

        if ($result) {
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&lima_delete_generators=true',
                        AdminMenus::GENERATORS_PAGE
                    )
                )
            );
        } else {
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&lima_delete_generators=error',
                        AdminMenus::GENERATORS_PAGE
                    )
                )
            );
        }
    }
}
