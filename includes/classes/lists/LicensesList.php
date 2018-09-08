<?php

namespace LicenseManager\Classes\Lists;

use \LicenseManager\Classes\Settings;

/**
 * Create the Licenses list
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class LicensesList extends \WP_List_Table
{
    private $crypto;

    public function __construct(
        \LicenseManager\Classes\Crypto $crypto
    ) {
        $this->crypto = $crypto;

        parent::__construct([
            'singular' => __('License', 'lima'),
            'plural'   => __('Licenses', 'lima'),
            'ajax'     => false
        ]);
    }

    public static function get_orders($per_page = 20, $page_number = 1)
    {
        global $wpdb;
        $table = $wpdb->prefix . \LicenseManager\Classes\Setup::LICENSES_TABLE_NAME;
        $sql = "SELECT * FROM $table";
        $sql .= ' ORDER BY ' . (empty($_REQUEST['orderby']) ? 'id' : esc_sql($_REQUEST['orderby']));
        $sql .= ' '          . (empty($_REQUEST['order'])   ? 'DESC'  : esc_sql($_REQUEST['order']));
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    public static function record_count()
    {
        global $wpdb;
        $table = $wpdb->prefix . \LicenseManager\Classes\Setup::LICENSES_TABLE_NAME;

        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    public function no_items()
    {
        _e('No licenses found.', 'lima');
    }

    public function column_name($item)
    {
        $title = '<strong>' . $item['shop_order_id'] . '</strong>';

        $actions = [
            'download' => sprintf(
                '<a href="%s">%s</a>',
                $item['voucher_url'],
                __('Download', 'lima')
            ),
        ];

        return $title . $this->row_actions($actions);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'order_id':
                if ($order = wc_get_order($item['order_id'])) {
                    $link = sprintf(
                        '<a href="%s" target="_blank">#%s</a>',
                        get_edit_post_link($item['order_id']),
                        $order->get_order_number()
                    );
                } else {
                    $link = '';
                }
                return $link;
            case 'product_id':
                if ($product = wc_get_product($item['product_id'])) {
                    $link = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        get_edit_post_link($item['product_id']),
                        $product->get_name()
                    );
                } else {
                    $link = '';
                }
                return $link;
            case 'license_key':
                return sprintf('<code>%s</code>', $this->crypto->decrypt($item['license_key']));
            case 'status':
                switch ($item['status']) {
                    case 1:
                        $status = sprintf('
                            <span class="lima-status available">%s</span>',
                            __('Available', 'lima')
                        );
                        break;
                    case 2:
                        $status = sprintf('
                            <span class="lima-status deactivated">%s</span>',
                            __('Deactivated', 'lima')
                        );
                        break;
                    default:
                        $status = sprintf('
                            <span class="lima-status unknown">%s</span>',
                            __('Unknown', 'lima')
                        );
                        break;
                }
                return $status;
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
            'cb'          => '<input type="checkbox" />',
            'id'          => __('ID', 'lima'),
            'order_id'    => __('Order', 'lima'),
            'product_id'  => __('Product', 'lima'),
            'license_key' => __('License Key', 'lima'),
            'created_at'  => __('Created at', 'lima'),
            'expires_at'  => __('Expires at', 'lima'),
            'status'      => __('Status', 'lima')
        );

        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'product_id' => array('product_id', true),
            'order_id' => array('order_id', true),
            'status' => array('status', true)
        );

        return $sortable_columns;
    }

    public function get_bulk_actions()
    {
        $actions = [
            'activate' => __('Activate', 'lima'),
            'deactivate' => __('Deactivate', 'lima')
        ];

        return $actions;
    }

    public function prepare_items()
    {
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
        );

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('licenses_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $this->items = self::get_orders($per_page, $current_page);
    }

    public function process_bulk_action()
    {
        $action = $this->current_action();
        switch ($action) {
            case 'activate':
                //$this->some_function();
                break;
            case 'deactivate':
                //$this->some_function();
                break;
            default:
                break;
        }
    }
}
