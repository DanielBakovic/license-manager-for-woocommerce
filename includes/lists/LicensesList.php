<?php
/**
 * License listing class.
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce\Lists;

use \LicenseManagerForWooCommerce\AdminMenus;
use \LicenseManagerForWooCommerce\AdminNotice;
use \LicenseManagerForWooCommerce\Settings;
use \LicenseManagerForWooCommerce\Setup;
use \LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;
use \LicenseManagerForWooCommerce\Enums\LicenseSource as LicenseSourceEnum;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * LicenseManagerForWooCommerce
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.2.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class LicensesList extends \WP_List_Table
{
    const SPINNER_URL = '/wp-admin/images/loading.gif';

    /**
     * Prefixed licenses table name
     * 
     * @var string
     */
    protected $table;

    /**
     * The default WordPress date format
     * 
     * @var string
     */
    protected $date_format;

    /**
     * The default WordPress time format
     * 
     * @var string
     */
    protected $time_format;

    /**
     * The default WordPress GMT offset
     * 
     * @var string
     */
    protected $gmt_offset;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        global $wpdb;

        parent::__construct(
            array(
                'singular' => __('License', 'lmfwc'),
                'plural'   => __('Licenses', 'lmfwc'),
                'ajax'     => false
            )
        );

        $this->table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;
        $this->date_format = get_option('date_format');
        $this->time_format = get_option('time_format');
        $this->gmt_offset = get_option('gmt_offset');
    }

    /**
     * Creates the different status filter links at the top of the table.
     * 
     * @return null
     */
    protected function get_views()
    {
        $status_links = array();
        $current = (!empty($_REQUEST['status']) ? $_REQUEST['status'] : 'all');

        // All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg('status');
        $status_links['all'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $all_url,
            $class,
            __('All', 'lmfwc'),
            apply_filters('lmfwc_get_license_key_count', null)
        );

        // Sold link
        $class = ($current == LicenseStatusEnum::SOLD ? ' class="current"' :'');
        $sold_url = esc_url(add_query_arg('status', LicenseStatusEnum::SOLD));
        $status_links['sold'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $sold_url,
            $class,
            __('Sold', 'lmfwc'),
            apply_filters('lmfwc_get_license_key_count', LicenseStatusEnum::SOLD)
        );

        // Delivered link
        $class = ($current == LicenseStatusEnum::DELIVERED ? ' class="current"' :'');
        $delivered_url = esc_url(add_query_arg('status', LicenseStatusEnum::DELIVERED));
        $status_links['delivered'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $delivered_url,
            $class,
            __('Delivered', 'lmfwc'),
            apply_filters('lmfwc_get_license_key_count', LicenseStatusEnum::DELIVERED)
        );

        // Active link
        $class = ($current == LicenseStatusEnum::ACTIVE ? ' class="current"' :'');
        $active_url = esc_url(add_query_arg('status', LicenseStatusEnum::ACTIVE));
        $status_links['active'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $active_url,
            $class,
            __('Active', 'lmfwc'),
            apply_filters('lmfwc_get_license_key_count', LicenseStatusEnum::ACTIVE)
        );

        // Inactive link
        $class = ($current == LicenseStatusEnum::INACTIVE ? ' class="current"' :'');
        $inactive_url = esc_url(add_query_arg('status', LicenseStatusEnum::INACTIVE));
        $status_links['inactive'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $inactive_url,
            $class,
            __('Inactive', 'lmfwc'),
            apply_filters('lmfwc_get_license_key_count', LicenseStatusEnum::INACTIVE)
        );

        return $status_links;
    }

    //protected function extra_tablenav($which)
    //{
    //    if ($which === 'top') {
    //        echo '<div class="alignleft actions">';
    //            $this->level_dropdown();
    //            $this->source_dropdown();
    //            submit_button(__( 'Filter', 'lmfwc' ), '', 'filter-action', false);
    //        echo '</div>';
    //    }
    //}

    /**
     * Display level dropdown
     *
     * @global wpdb $wpdb
     */
    public function level_dropdown() {

        global $wpdb;

        $orders = array();
        $results = $wpdb->get_results(
            "SELECT DISTINCT `order_id` FROM {$this->table} WHERE `order_id` IS NOT NULL;",
            ARRAY_A
        );

        foreach ($results as $result) {
            if (!$order_id = intval($result['order_id'])) {
                continue;
            }

            if (!$order = wc_get_order($order_id)) {
                continue;
            }

            array_push($orders, array(
                'value' => $order_id,
                'label' => $order->get_formatted_billing_full_name()
            ));
        }

        if (count($orders) === 0) {
            return $orders;
        }

        $selected_order = isset($_REQUEST['order-id']) ? $_REQUEST['order-id'] : '';
        ?>
            <label for="filter-by-order-id" class="screen-reader-text">
                <span><?php _e('Filter by order', 'lmfwc'); ?></span>
            </label>
            <select name="order-id" id="filter-by-order-id">
                <option<?php selected($selected_order, ''); ?> value="">
                    <span><?php _e('All orders', 'lmfwc'); ?></option></span>
                <?php
                foreach ($orders as $order) {
                    printf(
                        '<option%1$s value="%2$s">%3$s</option>',
                        selected($selected_order, $order['value'], false),
                        esc_attr($order['value']),
                        esc_html('#' . $order['value'] . ' ' . $order['label'])
                    );
                }
                ?>
            </select>
        <?php
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
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * License key column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_license_key($item)
    {
        if (Settings::get('lmfwc_hide_license_keys')) {
            $title = '<code class="lmfwc-placeholder empty"></code>';
            $title .= sprintf(
                '<img class="lmfwc-spinner" data-id="%d" src="%s">',
                $item['id'],
                self::SPINNER_URL
            );
        } else {
            $title = sprintf(
                '<code class="lmfwc-placeholder">%s</code>',
                apply_filters('lmfwc_decrypt', $item['license_key'])
            );
            $title .= sprintf(
                '<img class="lmfwc-spinner" data-id="%d" src="%s">',
                $item['id'],
                self::SPINNER_URL
            );
        }

        // ID
        $actions['id'] = sprintf(__('ID: %d', 'lmfwc'), intval($item['id']));

        // Edit
        if ($item['status'] != LicenseStatusEnum::SOLD
            && $item['status'] != LicenseStatusEnum::DELIVERED
        ) {
            $actions['edit'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    wp_nonce_url(
                        sprintf(
                            'admin.php?page=%s&action=edit&id=%d',
                            AdminMenus::LICENSES_PAGE,
                            intval($item['id'])
                        ),
                        'lmfwc_edit_license_key'
                    )
                ),
                __('Edit', 'lmfwc')
            );
        }

        // Hide/Show
        $actions['show'] = sprintf(
            '<a class="lmfwc-license-key-show" data-id="%d">%s</a>',
            $item['id'],
            __('Show', 'lmfwc')
        );
        $actions['hide'] = sprintf(
            '<a class="lmfwc-license-key-hide" data-id="%d">%s</a>',
            $item['id'],
            __('Hide', 'lmfwc')
        );

        // Activate, Deactivate, and Delete
        if ($item['status'] != LicenseStatusEnum::SOLD
            && $item['status'] != LicenseStatusEnum::DELIVERED
        ) {
            $actions['activate'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=activate&id=%d&_wpnonce=%s',
                        AdminMenus::LICENSES_PAGE,
                        intval($item['id']),
                        wp_create_nonce('activate')
                    )
                ),
                __('Activate', 'lmfwc')
            );
            $actions['deactivate'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=deactivate&id=%d&_wpnonce=%s',
                        AdminMenus::LICENSES_PAGE,
                        intval($item['id']),
                        wp_create_nonce('deactivate')
                    )
                ),
                __('Deactivate', 'lmfwc')
            );
            $actions['delete'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=delete&id=%d&_wpnonce=%s',
                        AdminMenus::LICENSES_PAGE,
                        intval($item['id']),
                        wp_create_nonce('delete')
                    )
                ),
                __('Delete', 'lmfwc')
            );
        }

        return $title . $this->row_actions($actions);
    }

    /**
     * Order ID column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_order_id($item)
    {
        $html = '';

        if ($order = wc_get_order($item['order_id'])) {
            $html = sprintf(
                '<a href="%s" target="_blank">#%s</a>',
                get_edit_post_link($item['order_id']),
                $order->get_order_number()
            );
        }

        return $html;
    }

    /**
     * Product ID column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_product_id($item)
    {
        $html = '';

        if ($product = wc_get_product($item['product_id'])) {
            $html = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                get_edit_post_link($item['product_id']),
                $product->get_name()
            );
        }

        return $html;
    }

    /**
     * Activation column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_activation($item)
    {
        $html = '';

        $times_activated = intval($item['times_activated']);
        $times_activated_max = intval($item['times_activated_max']);

        if ($times_activated == $times_activated_max) {
            $icon = '<span class="dashicons dashicons-yes"></span>';
            $status = 'activation done';
        } else {
            $icon = '';
            $status = 'activation pending';
        }

        if ($times_activated || $times_activated_max) {
            $html = sprintf(
                '<div class="lmfwc-status %s">%s <small>%d</small> / <b>%d</b></div>',
                $status,
                $icon,
                $times_activated,
                $times_activated_max
            );
        }

        return $html;
    }


    /**
     * Created at column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_created_at($item)
    {
        if (!$item['created_at']) {
            return '';
        }

        $offset_seconds = floatval($this->gmt_offset) * 60 * 60;
        $timestamp = strtotime($item['created_at']) + $offset_seconds;
        $result = date('Y-m-d H:i:s', $timestamp);
        $date = new \DateTime($result);

        $created_at = sprintf(
            '<span class="lmfwc-date lmfwc-status">%s, %s</span>',
            $date->format($this->date_format),
            $date->format($this->time_format)
        );

        return $created_at;
    }

    /**
     * Expires at column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_expires_at($item)
    {
        if (!$item['expires_at']) {
            return '';
        }

        $offset_seconds = floatval($this->gmt_offset) * 60 * 60;
        $timestamp_expires_at = strtotime($item['expires_at']) + $offset_seconds;
        $timestamp_now = strtotime('now') + $offset_seconds;
        $datetime_string = date('Y-m-d H:i:s', $timestamp_expires_at);
        $date = new \DateTime($datetime_string);

        if ($timestamp_now > $timestamp_expires_at) {
            return sprintf(
                '<span class="lmfwc-date lmfwc-status expired" title="%s">%s, %s</span><br>',
                __('Expired'),
                $date->format($this->date_format),
                $date->format($this->time_format)
            );
        }

        return sprintf(
            '<span class="lmfwc-date lmfwc-status">%s, %s</span>',
            $date->format($this->date_format),
            $date->format($this->time_format)
        );
    }

    /**
     * Valid for column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_valid_for($item)
    {
        $html = '';

        if ($item['valid_for']) {
            $html = sprintf(
                '<b>%d</b> %s<br><small>%s</small>',
                intval($item['valid_for']),
                __('day(s)', 'lmfwc'),
                __('After purchase', 'lmfwc')
            );
        }

        return $html;
    }

    /**
     * Status column
     * 
     * @param array $item Associative array of column name and value pairs
     * 
     * @return string
     */
    public function column_status($item)
    {
        $status = __('Unknown', 'lmfwc');

        switch ($item['status']) {
            case LicenseStatusEnum::SOLD:
                $status = sprintf(
                    '<div class="lmfwc-status sold"><span class="dashicons dashicons-yes"></span> %s</div>',
                    __('Sold', 'lmfwc')
                );
                break;
            case LicenseStatusEnum::DELIVERED:
                $status = sprintf(
                    '<div class="lmfwc-status delivered"><span class="lmfwc-icons delivered"></span> %s</div>',
                    __('Delivered', 'lmfwc')
                );
                break;
            case LicenseStatusEnum::ACTIVE:
                $status = sprintf(
                    '<div class="lmfwc-status active"><span class="dashicons dashicons-marker"></span> %s</div>',
                    __('Active', 'lmfwc')
                );
                break;
            case LicenseStatusEnum::INACTIVE:
                $status = sprintf(
                    '<div class="lmfwc-status inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
                    __('Inactive', 'lmfwc')
                );
                break;
            default:
                $status = sprintf(
                    '<div class="lmfwc-status unknown">%s</div>',
                    __('Unknown', 'lmfwc')
                );
                break;
        }

        return $status;
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
     * Defines sortable columns and their sort value
     * 
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'         => array('id', true),
            'order_id'   => array('order_id', true),
            'product_id' => array('product_id', true),
            'expires_at' => array('expires_at', true),
            'status'     => array('status', true),
            'created_at' => array('created_at', true),
            'activation' => array('times_activated_max', true)
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
        $actions = [
            'activate'   => __('Activate', 'lmfwc'),
            'deactivate' => __('Deactivate', 'lmfwc'),
            'delete'     => __('Delete', 'lmfwc'),
            'export_csv' => __('Export (CSV)', 'lmfwc'),
            'export_pdf' => __('Export (PDF)', 'lmwfc')
        ];

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
            case 'activate':
                $this->toggle_license_key_status(LicenseStatusEnum::ACTIVE);
                break;
            case 'deactivate':
                $this->toggle_license_key_status(LicenseStatusEnum::INACTIVE);
                break;
            case 'delete':
                $this->delete_license_keys();
                break;
            case 'export_pdf':
                $this->export_license_keys('PDF');
                break;
            case 'export_csv':
                $this->export_license_keys('CSV');
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

        $per_page     = $this->get_items_per_page('lmfwc_licenses_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));

        if (array_key_exists('filter_action', (array)$_REQUEST)) {
            $this->filter_licenses();
        }

        $this->items = $this->get_licenses($per_page, $current_page);
    }

    /**
     * Retrieves the licenses from the database.
     * 
     * @param integer $per_page    Default amount of licenses per page
     * @param integer $page_number Default page number
     * 
     * @return array
     */
    public function get_licenses($per_page = 20, $page_number = 1)
    {
        global $wpdb;
        global $wp;

        $sql = "SELECT * FROM {$this->table} WHERE 1 = 1";

        $where = '';

        if ($this->is_view_filter_active()) {
            $where .= $wpdb->prepare(
                ' AND status = %d',
                intval($_GET['status'])
            );
        }

        if (isset($_REQUEST['order-id']) && is_numeric($_REQUEST['order-id'])) {
            $where .= $wpdb->prepare(
                ' AND order_id = %d',
                intval($_REQUEST['order-id'])
            );

            $foo = add_query_arg(array(
                'filter-order-id' => intval($_REQUEST['order-id'])
            ));
        }

        if (isset($_REQUEST['product-id']) && is_numeric($_REQUEST['product-id'])) {
            $where .= $wpdb->prepare(
                ' AND product_id = %d',
                intval($_REQUEST['product-id'])
            );
        }

        $sql .= $where;
        $sql .= ' ORDER BY ' . (empty($_REQUEST['orderby']) ? 'id' : esc_sql($_REQUEST['orderby']));
        $sql .= ' '          . (empty($_REQUEST['order'])   ? 'DESC'  : esc_sql($_REQUEST['order']));
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
    public function record_count($status = null)
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1 = 1";

        if ($this->is_view_filter_active()) {
            $sql .= $wpdb->prepare(' AND status = %d', intval($_GET['status']));
        }

        if (isset($_REQUEST['order-id'])) {
            $sql .= $wpdb->prepare(' AND order_id = %d', intval($_REQUEST['order-id']));
        }

        //return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
        return $wpdb->get_var($sql);
    }

    /**
     * Output in case no items exist
     * 
     * @return null
     */
    public function no_items()
    {
        _e('No licenses found.', 'lmfwc');
    }

    /**
     * Set the table columns
     * 
     * @return null
     */
    public function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'license_key' => __('License Key', 'lmfwc'),
            'order_id'    => __('Order', 'lmfwc'),
            'product_id'  => __('Product', 'lmfwc'),
            'activation'  => __('Activation', 'lmfwc'),
            'created_at'  => __('Created at', 'lmfwc'),
            'expires_at'  => __('Expires at', 'lmfwc'),
            'valid_for'   => __('Valid for', 'lmfwc'),
            'status'      => __('Status', 'lmfwc')
        );

        return $columns;
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
        $nonce = $_REQUEST['_wpnonce'];

        if (!wp_verify_nonce($nonce, $nonce_action) 
            && !wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])
        ) {
            AdminNotice::error(__('The nonce is invalid or has expired.', 'lmfwc'));
            wp_redirect(
                admin_url(sprintf('admin.php?page=%s', AdminMenus::LICENSES_PAGE))
            );

            exit();
        }
    }

    /**
     * Changes the license key status
     * 
     * @return null
     */
    private function toggle_license_key_status($status)
    {
        ($status == LicenseStatusEnum::ACTIVE) ? $nonce_action = 'activate' : $nonce_action = 'deactivate';
        $status_whitelist = array(
            LicenseStatusEnum::ACTIVE,
            LicenseStatusEnum::INACTIVE
        );

        $this->verify_nonce($nonce_action);

        $license_ids = (array)$_REQUEST['id'];
        $count = 0;
        $skipped = 0;

        foreach ($license_ids as $license_id) {
            try {
                // Retrieve full license info
                $license = apply_filters(
                    'lmfwc_get_license_key',
                    $license_id
                );

                // Skip if the license if it's already sold, delivered, or used
                if (!in_array($license['status'], $status_whitelist)) {
                    $skipped++;
                    continue;
                }

                apply_filters(
                    'lmfwc_update_license_key_status',
                    $license_id,
                    $status
                );
                $count++;
            } catch (\Exception $e) {
                // Todo...
            }
        }

        if ($nonce_action == 'activate') {
            $message = sprintf(
                esc_html__('%d License key(s) activated successfully.', 'lmfwc'),
                $count
            );
        } elseif ($nonce_action == 'deactivate') {
            $message = sprintf(
                esc_html__('%d License key(s) deactivated successfully.', 'lmfwc'),
                $count
            );
        }

        // Inform the user how many license keys were skipped
        if ($skipped > 0) {
            $message .= ' ';
            $message .= sprintf(
                esc_html__('Skipped %d license key(s) due to their incompatible status.', 'wcdpi'),
                $skipped
            );
        }

        // Set the admin notice
        AdminNotice::success($message);

        // Redirect and exit
        wp_redirect(
            admin_url(
                sprintf('admin.php?page=%s', AdminMenus::LICENSES_PAGE)
            )
        );

        exit();
    }

    /**
     * Removes the license key(s) permanently from the database
     * 
     * @return null
     */
    private function delete_license_keys()
    {
        $this->verify_nonce('delete');
        $status_whitelist = array(
            LicenseStatusEnum::ACTIVE,
            LicenseStatusEnum::INACTIVE
        );

        $license_ids = (array)($_REQUEST['id']);
        $license_ids_to_delete = array();
        $count = 0;
        $skipped = 0;

        foreach ($license_ids as $license_id) {
            try {
                // Retrieve full license info
                $license = apply_filters('lmfwc_get_license_key', $license_id);

                // Skip if the license if it's already sold, delivered, or used
                if (!in_array($license['status'], $status_whitelist)) {
                    $skipped++;
                    continue;
                }

                $license_ids_to_delete[] = $license_id;
                $count++;
            } catch (\Exception $e) {
                // Todo...
            }
        }

        $result = apply_filters(
            'lmfwc_delete_license_keys',
            $license_ids_to_delete
        );

        $message = sprintf(
            esc_html__('%d License key(s) permanently deleted.', 'lmfwc'),
            $result
        );

        // Inform the user how many license keys were skipped
        if ($skipped > 0) {
            $message .= ' ';
            $message .= sprintf(
                esc_html__('Skipped %d license key(s) due to their incompatible status.', 'wcdpi'),
                $skipped
            );
        }

        // Set the admin notice
        AdminNotice::success($message);

        // Redirect and exit
        wp_redirect(
            admin_url(
                sprintf('admin.php?page=%s', AdminMenus::LICENSES_PAGE)
            )
        );

        exit();
    }

    /**
     * Initiates a file download of the exported licenses (pdf or csv)
     * 
     * @return null
     */
    private function export_license_keys($type)
    {
        if ($type === 'PDF') {
            $this->verify_nonce('export_pdf');
            do_action('lmfwc_export_license_keys_pdf', (array)$_REQUEST['id']);
        }

        if ($type === 'CSV') {
            $this->verify_nonce('export_csv');
            do_action('lmfwc_export_license_keys_csv', (array)$_REQUEST['id']);
        }
    }

    /**
     * Checks if there are currently any license view filters active
     * 
     * @return boolean
     */
    public function is_view_filter_active()
    {
        if (array_key_exists('status', $_GET)
            && in_array($_GET['status'], LicenseStatusEnum::$status)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Sets a filter
     * 
     * @return null
     */
    protected function filter_licenses()
    {
        $args = [];

        remove_query_arg('product_id');
        remove_query_arg('order_id');

        if ($_REQUEST['product-filter']) {
            $args['product_id'] = intval($_REQUEST['product-filter']);
        }

        if ($_REQUEST['order-filter']) {
            $args['order_id'] = intval($_REQUEST['order-filter']);
        }

        $url .= add_query_arg($args);

        wp_redirect($url);
        exit();
    }
}