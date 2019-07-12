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

use DateTime;
use Exception;
use LicenseManagerForWooCommerce\AdminMenus;
use LicenseManagerForWooCommerce\AdminNotice;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Settings;
use LicenseManagerForWooCommerce\Setup;
use WC_Product;
use WP_List_Table;

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
 * @version  Release: <1.3.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class LicensesList extends WP_List_Table
{
    const SPINNER_URL = '/wp-admin/images/loading.gif';

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var string
     */
    protected $timeFormat;

    /**
     * @var string
     */
    protected $gmtOffset;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        global $wpdb;

        parent::__construct(
            array(
                'singular' => __('License key', 'lmfwc'),
                'plural'   => __('License keys', 'lmfwc'),
                'ajax'     => false
            )
        );

        $this->table      = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;
        $this->dateFormat = get_option('date_format');
        $this->timeFormat = get_option('time_format');
        $this->gmtOffset  = get_option('gmtOffset');
    }

    /**
     * Creates the different status filter links at the top of the table.
     * 
     * @return null
     */
    protected function get_views()
    {
        $statusLinks = array();
        $current     = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'all';

        // All link
        $class = $current == 'all' ? ' class="current"' :'';
        $allUrl = remove_query_arg('status');
        $statusLinks['all'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $allUrl,
            $class,
            __('All', 'lmfwc'),
            LicenseResourceRepository::instance()->count()
        );

        // Sold link
        $class = $current == LicenseStatus::SOLD ? ' class="current"' :'';
        $soldUrl = esc_url(add_query_arg('status', LicenseStatus::SOLD));
        $statusLinks['sold'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $soldUrl,
            $class,
            __('Sold', 'lmfwc'),
            LicenseResourceRepository::instance()->countBy(array('status' => LicenseStatus::SOLD))
        );

        // Delivered link
        $class = $current == LicenseStatus::DELIVERED ? ' class="current"' :'';
        $deliveredUrl = esc_url(add_query_arg('status', LicenseStatus::DELIVERED));
        $statusLinks['delivered'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $deliveredUrl,
            $class,
            __('Delivered', 'lmfwc'),
            LicenseResourceRepository::instance()->countBy(array('status' => LicenseStatus::DELIVERED))
        );

        // Active link
        $class = $current == LicenseStatus::ACTIVE ? ' class="current"' :'';
        $activeUrl = esc_url(add_query_arg('status', LicenseStatus::ACTIVE));
        $statusLinks['active'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $activeUrl,
            $class,
            __('Active', 'lmfwc'),
            LicenseResourceRepository::instance()->countBy(array('status' => LicenseStatus::ACTIVE))
        );

        // Inactive link
        $class = $current == LicenseStatus::INACTIVE ? ' class="current"' :'';
        $inactiveUrl = esc_url(add_query_arg('status', LicenseStatus::INACTIVE));
        $statusLinks['inactive'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $inactiveUrl,
            $class,
            __('Inactive', 'lmfwc'),
            LicenseResourceRepository::instance()->countBy(array('status' => LicenseStatus::INACTIVE))
        );

        return $statusLinks;
    }

//    protected function extra_tablenav($which)
//    {
//        if ($which === 'top') {
//            echo '<div class="alignleft actions">';
//                $this->level_dropdown();
//                $this->source_dropdown();
//                submit_button(__( 'Filter', 'lmfwc' ), '', 'filter-action', false);
//            echo '</div>';
//        }
//    }

    /**
     * Display level dropdown
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

        $selectedOrder = isset($_REQUEST['order-id']) ? $_REQUEST['order-id'] : '';
        ?>
            <label for="filter-by-order-id" class="screen-reader-text">
                <span><?php _e('Filter by order', 'lmfwc'); ?></span>
            </label>
            <select name="order-id" id="filter-by-order-id">
                <option<?php selected($selectedOrder, ''); ?> value="">
                    <span><?php _e('All orders', 'lmfwc'); ?></option></span>
                <?php
                foreach ($orders as $order) {
                    printf(
                        '<option%1$s value="%2$s">%3$s</option>',
                        selected($selectedOrder, $order['value'], false),
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

        // Activate, Deactivate
        if ($item['status'] != LicenseStatus::SOLD
            && $item['status'] != LicenseStatus::DELIVERED
        ) {
            if ($item['status'] != LicenseStatus::ACTIVE) {
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
            }

            if ($item['status'] != LicenseStatus::INACTIVE) {
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
            }
        }

        // Delete
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

        /** @var WC_Product $product */
        if ($product = wc_get_product($item['product_id'])) {

            if ($parentId = $product->get_parent_id()) {
                $html = sprintf(
                    '<span>#%s - %s</span>',
                    $product->get_id(),
                    $product->get_name()
                );

                if ($parent = wc_get_product($parentId)) {
                    $html .= sprintf(
                        '<br><small>%s <a href="%s" target="_blank">#%s - %s</a></small>',
                        __('Variation of', 'lmfwc'),
                        get_edit_post_link($parent->get_id()),
                        $parent->get_id(),
                        $parent->get_name()
                    );
                }
            } else {
                $html = sprintf(
                    '<a href="%s" target="_blank">#%s - %s</a>',
                    get_edit_post_link($item['product_id']),
                    $product->get_id(),
                    $product->get_name()
                );
            }
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

        $timesActivated    = intval($item['times_activated']);
        $timesActivatedMax = intval($item['times_activated_max']);

        if ($timesActivated == $timesActivatedMax) {
            $icon = '<span class="dashicons dashicons-yes"></span>';
            $status = 'activation done';
        } else {
            $icon = '';
            $status = 'activation pending';
        }

        if ($timesActivated || $timesActivatedMax) {
            $html = sprintf(
                '<div class="lmfwc-status %s">%s <small>%d</small> / <b>%d</b></div>',
                $status,
                $icon,
                $timesActivated,
                $timesActivatedMax
            );
        }

        return $html;
    }

    /**
     * Created at column
     * 
     * @param array $item Associative array of column name and value pairs
     *
     * @throws Exception
     * @return string
     */
    public function column_created_at($item)
    {
        if (!$item['created_at']) {
            return '';
        }

        $offsetSeconds = floatval($this->gmtOffset) * 60 * 60;
        $timestamp = strtotime($item['created_at']) + $offsetSeconds;
        $result = date('Y-m-d H:i:s', $timestamp);
        $date = new DateTime($result);

        $createdAt = sprintf(
            '<span class="lmfwc-date lmfwc-status">%s, %s</span>',
            $date->format($this->dateFormat),
            $date->format($this->timeFormat)
        );

        return $createdAt;
    }

    /**
     * Expires at column
     * 
     * @param array $item Associative array of column name and value pairs
     *
     * @throws Exception
     * @return string
     */
    public function column_expires_at($item)
    {
        if (!$item['expires_at']) {
            return '';
        }

        $offsetSeconds = floatval($this->gmtOffset) * 60 * 60;
        $timestampExpiresAt = strtotime($item['expires_at']) + $offsetSeconds;
        $timestampNow = strtotime('now') + $offsetSeconds;
        $datetimeString = date('Y-m-d H:i:s', $timestampExpiresAt);
        $date = new DateTime($datetimeString);

        if ($timestampNow > $timestampExpiresAt) {
            return sprintf(
                '<span class="lmfwc-date lmfwc-status expired" title="%s">%s, %s</span><br>',
                __('Expired'),
                $date->format($this->dateFormat),
                $date->format($this->timeFormat)
            );
        }

        return sprintf(
            '<span class="lmfwc-date lmfwc-status">%s, %s</span>',
            $date->format($this->dateFormat),
            $date->format($this->timeFormat)
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
        switch ($item['status']) {
            case LicenseStatus::SOLD:
                $status = sprintf(
                    '<div class="lmfwc-status sold"><span class="dashicons dashicons-yes"></span> %s</div>',
                    __('Sold', 'lmfwc')
                );
                break;
            case LicenseStatus::DELIVERED:
                $status = sprintf(
                    '<div class="lmfwc-status delivered"><span class="lmfwc-icons delivered"></span> %s</div>',
                    __('Delivered', 'lmfwc')
                );
                break;
            case LicenseStatus::ACTIVE:
                $status = sprintf(
                    '<div class="lmfwc-status active"><span class="dashicons dashicons-marker"></span> %s</div>',
                    __('Active', 'lmfwc')
                );
                break;
            case LicenseStatus::INACTIVE:
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
        $sortableColumns = array(
            'id'         => array('id', true),
            'order_id'   => array('order_id', true),
            'product_id' => array('product_id', true),
            'expires_at' => array('expires_at', true),
            'status'     => array('status', true),
            'created_at' => array('created_at', true),
            'activation' => array('times_activated_max', true)
        );

        return $sortableColumns;
    }

    /**
     * Defines items in the bulk action dropdown
     * 
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'activate'   => __('Activate', 'lmfwc'),
            'deactivate' => __('Deactivate', 'lmfwc'),
            'delete'     => __('Delete', 'lmfwc'),
            'export_csv' => __('Export (CSV)', 'lmfwc'),
            'export_pdf' => __('Export (PDF)', 'lmfwc')
        );

        return $actions;
    }

    /**
     * Processes the currently selected action
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();

        switch ($action) {
            case 'activate':
                $this->toggle_license_key_status(LicenseStatus::ACTIVE);
                break;
            case 'deactivate':
                $this->toggle_license_key_status(LicenseStatus::INACTIVE);
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
     */
    public function prepare_items()
    {
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
        );

        $this->process_bulk_action();

        $perPage     = $this->get_items_per_page('lmfwc_licenses_per_page', 10);
        $currentPage = $this->get_pagenum();
        $totalItems  = $this->record_count();

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage,
            'total_pages' => ceil($totalItems / $perPage)
        ));

        if (array_key_exists('filter_action', (array)$_REQUEST)) {
            $this->filter_licenses();
        }

        $this->items = $this->get_licenses($perPage, $currentPage);
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
     */
    public function no_items()
    {
        _e('No license keys found.', 'lmfwc');
    }

    /**
     * Set the table columns
     */
    public function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'license_key' => __('License key', 'lmfwc'),
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
     * @param string $nonce The nonce to check
     */
    private function verify_nonce($nonce)
    {
        $currentNonce = $_REQUEST['_wpnonce'];

        if (!wp_verify_nonce($currentNonce, $nonce)
            && !wp_verify_nonce($currentNonce, 'bulk-' . $this->_args['plural'])
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
     * @param int $status
     */
    private function toggle_license_key_status($status)
    {
        $status == LicenseStatus::ACTIVE ? $nonce = 'activate' : $nonce = 'deactivate';

        $this->verify_nonce($nonce);

        $licenseKeyIds = (array)$_REQUEST['id'];
        $count = 0;
        $message = '';

        foreach ($licenseKeyIds as $licenseKeyId) {
            try {
                LicenseResourceRepository::instance()->update($licenseKeyId, array('status' => $status));
                $count++;
            } catch (Exception $e) {
                // Todo...
            }
        }

        if ($nonce == 'activate') {
            $message = sprintf(
                esc_html__('%d license key(s) activated successfully.', 'lmfwc'),
                $count
            );
        } elseif ($nonce == 'deactivate') {
            $message = sprintf(
                esc_html__('%d license key(s) deactivated successfully.', 'lmfwc'),
                $count
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
     */
    private function delete_license_keys()
    {
        $this->verify_nonce('delete');

        $result = LicenseResourceRepository::instance()->deleteBy(array('id' => (array)($_REQUEST['id'])));

        $message = sprintf(esc_html__('%d license key(s) permanently deleted.', 'lmfwc'), $result);

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
     * @param string $type
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
            && in_array($_GET['status'], LicenseStatus::$status)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Sets a filter
     */
    protected function filter_licenses()
    {
        $args = array();

        remove_query_arg('product_id');
        remove_query_arg('order_id');

        if ($_REQUEST['product-filter']) {
            $args['product_id'] = intval($_REQUEST['product-filter']);
        }

        if ($_REQUEST['order-filter']) {
            $args['order_id'] = intval($_REQUEST['order-filter']);
        }

        $url = add_query_arg($args);

        wp_redirect($url);
        exit();
    }
}