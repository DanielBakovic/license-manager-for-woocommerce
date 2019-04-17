<?php

namespace LicenseManagerForWooCommerce\Lists;

use \LicenseManagerForWooCommerce\AdminMenus;
use \LicenseManagerForWooCommerce\AdminNotice;
use \LicenseManagerForWooCommerce\Setup;
use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create the Generators list
 *
 * @since 1.0.0
 */
class GeneratorsList extends \WP_List_Table
{
    /**
     * Prefixed generator table name
     * 
     * @var string
     */
    protected $table;

    /**
     * Class constructor
     * 
     * @return null
     */
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

    /**
     * Retrieves the generators from the database.
     * 
     * @param integer $per_page    Default amount of generators per page
     * @param integer $page_number Default page number
     * 
     * @return array
     */
    public function get_generators($per_page = 20, $page_number = 1)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$this->table}";
        $sql .= ' ORDER BY ' . (empty($_REQUEST['orderby']) ? 'id'   : esc_sql($_REQUEST['orderby']));
        $sql .= ' '          . (empty($_REQUEST['order'])   ? 'DESC' : esc_sql($_REQUEST['order']));
        $sql .= " LIMIT {$per_page}";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    /**
     * Retrieves the generator table row count
     * 
     * @return integer
     */
    public function record_count()
    {
        global $wpdb;

        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * Output in case no items exist
     * 
     * @return null
     */
    public function no_items()
    {
        _e('No generators found.', 'lmfwc');
    }

    /**
     * Checkbox column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
    }

    /**
     * Name column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_name($item)
    {
        $products = apply_filters('lmfwc_get_assigned_products', $item['id']);
        $actions = array();
        $title = '<strong>' . $item['name'] . '</strong>';

        if (count($products) > 0) {
            $title .= sprintf(
                '<span class="lmfwc-badge info" title="%s">%d</span>',
                __('Number of products assigned to this generator', 'lmfwc'),
                count($products)
            );
        }

        $actions['id'] = sprintf(__('ID: %d', 'lmfwc'), intval($item['id']));

        if (!apply_filters('lmfwc_get_assigned_products', $item['id'])) {
            $actions['delete'] = sprintf(
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
                AdminMenus::GENERATORS_PAGE,
                'delete',
                absint($item['id']),
                wp_create_nonce('delete'),
                __('Delete', 'lmfwc')
            );
        }

        $actions['edit'] = sprintf(
            '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
            AdminMenus::GENERATORS_PAGE,
            'edit',
            absint($item['id']),
            wp_create_nonce('edit'),
            __('Edit', 'lmfwc')
        );

        return $title . $this->row_actions($actions);
    }

    /**
     * Character map column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_charset($item)
    {
        $charset = '';

        if ($item['charset']) {
            $charset = sprintf('<code>%s</code>', $item['charset']);
        }

        return $charset;
    }

    /**
     * Separator column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_separator($item)
    {
        $separator = '';

        if ($item['separator']) {
            $separator = sprintf('<code>%s</code>', $item['separator']);
        }

        return $separator;
    }

    /**
     * Prefix column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_prefix($item)
    {
        $prefix = '';

        if ($item['prefix']) {
            $prefix = sprintf('<code>%s</code>', $item['prefix']);
        }

        return $prefix;
    }

    /**
     * Suffix column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_suffix($item)
    {
        $suffix = '';

        if ($item['suffix']) {
            $suffix = sprintf('<code>%s</code>', $item['suffix']);
        }

        return $suffix;
    }

    /**
     * Expires in column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_expires_in($item)
    {
        $expires_in = '';

        if (!$item['expires_in']) {
            return $expires_in;
        }

        $expires_in .= sprintf('%d %s', $item['expires_in'], __('day(s)', 'lmfwc'));
        $expires_in .= '<br>';
        $expires_in .= sprintf('<small>%s</small>', __('After purchase', 'lmfwc'));

        return $expires_in;
    }

    /**
     * Default column value
     * 
     * @param array  $item        Associative array of column name and value pairs
     * @param string $column_name Name of the current column
     * 
     * @return string
     */
    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * Set the table columns
     * 
     * @return null
     */
    public function get_columns()
    {
        $columns = array(
            'cb'                  => '<input type="checkbox" />',
            'name'                => __('Name', 'lmfwc'),
            'charset'             => __('Character map', 'lmfwc'),
            'chunks'              => __('Number of chunks', 'lmfwc'),
            'chunk_length'        => __('Chunk length', 'lmfwc'),
            'times_activated_max' => __('Maximum activation count', 'lmfwc'),
            'separator'           => __('Separator', 'lmfwc'),
            'prefix'              => __('Prefix', 'lmfwc'),
            'suffix'              => __('Suffix', 'lmfwc'),
            'expires_in'          => __('Expires in', 'lmfwc')
        );

        return $columns;
    }

    /**
     * Defines sortable columns and their sort value
     * 
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'                => array('name', true),
            'charset'             => array('charset', true),
            'chunks'              => array('chunks', true),
            'chunk_length'        => array('chunk_length', true),
            'times_activated_max' => array('times_activated_max', true),
            'expires_in'          => array('expires_in', true),
        );

        return $sortable_columns;
    }

    /**
     * Defines items in the bulk action dropdown
     * 
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'lmfwc'),
        );

        return $actions;
    }

    /**
     * Processes the currently selected action
     * 
     * @return null
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();

        switch ($action) {
            case 'delete':
                $this->verify_nonce('delete');
                $this->delete_generators();
                break;
            default:
                break;
        }
    }

    /**
     * Initialization function
     * 
     * @return null
     */
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
        $total_items  = $this->record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

        $this->items = $this->get_generators($per_page, $current_page);
    }

    /**
     * Checks if the given nonce is (still) valid
     * 
     * @param string $nonce_action The nonce to check
     * 
     * @return null
     */
    private function verify_nonce($nonce_action)
    {
        if (
            !wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action) &&
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])
        ) {
            AdminNotice::error(
                __('The nonce is invalid or has expired.', 'lmfwc')
            );
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)));

            exit();
        }
    }

    /**
     * Bulk deletes the generators from the table by a single ID or an array of ID's.
     *
     * @return string
     */
    public function delete_generators()
    {
        $selected_generators = (array)$_REQUEST['id'];
        $generators_to_delete = array();

        foreach ($selected_generators as $generator_id) {
            if ($products = apply_filters('lmfwc_get_assigned_products', $generator_id)) {
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
            AdminNotice::success(
                sprintf(__('%d Generator(s) permanently deleted.', 'lmfwc'), $result)
            );

            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)
                )
            );
        } else {
            AdminNotice::error(
                __('There was a problem deleting the generators.', 'lmfwc')
            );

            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE)
                )
            );
        }
    }
}
