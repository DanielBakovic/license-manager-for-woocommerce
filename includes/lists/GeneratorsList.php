<?php

namespace LicenseManagerForWooCommerce\Lists;

use \LicenseManagerForWooCommerce\AdminMenus;
use \LicenseManagerForWooCommerce\AdminNotice;
use \LicenseManagerForWooCommerce\Logger;
use \LicenseManagerForWooCommerce\Setup;

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
            'singular' => __('Generator', 'lmfwc'),
            'plural'   => __('Generators', 'lmfwc'),
            'ajax'     => false
        ]);

        $this->table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;
    }

    public static function get_orders($per_page = 20, $page_number = 1)
    {
        global $wpdb;

        $table = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

        $sql = "SELECT * FROM {$table}";
        $sql .= ' ORDER BY ' . (empty($_REQUEST['orderby']) ? 'id'   : esc_sql($_REQUEST['orderby']));
        $sql .= ' '          . (empty($_REQUEST['order'])   ? 'DESC' : esc_sql($_REQUEST['order']));
        $sql .= " LIMIT {$per_page}";
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
        _e('No generators found.', 'lmfwc');
    }


    public function column_name($item)
    {
        $title = '<strong>' . $item['name'] . '</strong>';

        $actions = [
            'id' => sprintf(__('ID: %d', 'lmfwc'), intval($item['id'])),
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
                AdminMenus::GENERATORS_PAGE,
                'delete',
                absint($item['id']),
                wp_create_nonce('delete'),
                __('Delete', 'lmfwc')
            ),
            'edit' => sprintf(
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
                AdminMenus::EDIT_GENERATOR_PAGE,
                'edit',
                absint($item['id']),
                wp_create_nonce('edit'),
                __('Edit', 'lmfwc')
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
                if (!$item['expires_in']) {
                    return '';
                }

                $expires_in = sprintf('%d %s', $item['expires_in'], __('day(s)', 'lmfwc'));
                $expires_in .= '<br>';
                $expires_in .= sprintf('<small>%s</small>', __('After purchase'));

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
            'name'         => __('Name', 'lmfwc'),
            'charset'      => __('Character map', 'lmfwc'),
            'chunks'       => __('Number of chunks', 'lmfwc'),
            'chunk_length' => __('Chunk length', 'lmfwc'),
            'separator'    => __('Separator', 'lmfwc'),
            'prefix'       => __('Prefix', 'lmfwc'),
            'suffix'       => __('Suffix', 'lmfwc'),
            'expires_in'   => __('Expires in', 'lmfwc'),
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
            'delete' => __('Delete', 'lmfwc'),
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

        $per_page     = $this->get_items_per_page('generators_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

        $this->items = self::get_orders($per_page, $current_page);
    }

    private function verifyNonce($nonce_action)
    {
        if (
            !wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action) &&
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])
        ) {
            AdminNotice::addErrorSupportForum(8);
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)));
            wp_die();
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
        $generators_to_delete = array();

        foreach ($selected_generators as $generator_id) {
            if ($products = apply_filters('lmfwc_get_assigned_products', array('generator_id' => absint($generator_id)))) {
                continue;
            } else {
                array_push($generators_to_delete, $generator_id);
            }
        }

        $result = apply_filters(
            'lmfwc_delete_generators',
            $generators_to_delete
        );

        if ($result) {
            AdminNotice::add(
                'success',
                sprintf(__('%d Generator(s) permanently deleted.', 'lmfwc'), $result)
            );

            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)
                )
            );
        } else {
            AdminNotice::addErrorSupportForum(9);

            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)
                )
            );
        }
    }
}
