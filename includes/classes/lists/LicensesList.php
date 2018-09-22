<?php

namespace LicenseManager\Classes\Lists;

use \LicenseManager\Classes\AdminMenus;
use \LicenseManager\Classes\Database;
use \LicenseManager\Classes\Logger;
use \LicenseManager\Classes\Settings;
use \LicenseManager\Classes\Setup;
use \LicenseManager\Classes\Abstracts\LicenseStatusEnum;

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
    const SPINNER_URL = '/wp-admin/images/loading.gif';

    /**
     * @var \LicenseManager\Classes\Crypto
     */
    private $crypto;

    /**
     * Class constructor.
     */
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

    protected function get_views()
    {
        $status_links   = array();
        $current = (!empty($_REQUEST['status']) ? $_REQUEST['status'] : 'all');

        // All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg('status');
        $status_links['all'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $all_url,
            $class,
            __('All', 'lima'),
            Database::getLicenseKeyCount()
        );

        // Sold link
        $class         = ($current == LicenseStatusEnum::SOLD ? ' class="current"' :'');
        $sold_url      = esc_url(add_query_arg('status', LicenseStatusEnum::SOLD));
        $status_links['sold'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $sold_url,
            $class,
            __('Sold', 'lima'),
            Database::getLicenseKeyCount(LicenseStatusEnum::SOLD)
        );

        // Delivered link
        $class         = ($current == LicenseStatusEnum::DELIVERED ? ' class="current"' :'');
        $delivered_url = esc_url(add_query_arg('status', LicenseStatusEnum::DELIVERED));
        $status_links['delivered'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $delivered_url,
            $class,
            __('Delivered', 'lima'),
            Database::getLicenseKeyCount(LicenseStatusEnum::DELIVERED)
        );

        // Active link
        $class      = ($current == LicenseStatusEnum::ACTIVE ? ' class="current"' :'');
        $active_url = esc_url(add_query_arg('status', LicenseStatusEnum::ACTIVE));
        $status_links['active'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $active_url,
            $class,
            __('Active', 'lima'),
            Database::getLicenseKeyCount(LicenseStatusEnum::ACTIVE)
        );

        // Inactive link
        $class        = ($current == LicenseStatusEnum::INACTIVE ? ' class="current"' :'');
        $inactive_url = esc_url(add_query_arg('status', LicenseStatusEnum::INACTIVE));
        $status_links['inactive'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $inactive_url,
            $class,
            __('Inactive', 'lima'),
            Database::getLicenseKeyCount(LicenseStatusEnum::INACTIVE)
        );

        return $status_links;
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
            case 'valid_for':
                if ($item['valid_for']) {
                    $link = sprintf(__('%d Day(s)', 'lima'), intval($item['valid_for']));
                } else {
                    $link = __('non-expiring', 'lima');
                }
                return $link;
            case 'source':
                switch ($item['source']) {
                    case 1:
                        $status = sprintf(
                            '<span class="dashicons dashicons-admin-generic" title="%s"></span>',
                            __('Generator', 'lima')
                        );
                        break;
                    case 2:
                        $status = sprintf(
                            '<span class="dashicons dashicons-download" title="%s"></span>',
                            __('Import', 'lima')
                        );
                        break;

                    // Default switch case
                    default:
                        $status = '';
                        break;
                }
                return $status;
            case 'status':
                switch ($item['status']) {
                    case LicenseStatusEnum::SOLD:
                        $status = sprintf(
                            '<span class="lima-status sold-pending">%s</span>',
                            __('Sold', 'lima')
                        );
                        break;
                    case LicenseStatusEnum::DELIVERED:
                        $status = sprintf(
                            '<span class="lima-status sold-delivered">%s</span>',
                            __('Delivered', 'lima')
                        );
                        break;
                    case LicenseStatusEnum::ACTIVE:
                        $status = sprintf(
                            '<span class="lima-status available-ready">%s</span>',
                            __('Active', 'lima')
                        );
                        break;
                    case LicenseStatusEnum::INACTIVE:
                        $status = sprintf(
                            '<span class="lima-status available-deactivated">%s</span>',
                            __('Inactive', 'lima')
                        );
                        break;

                    // Default switch case
                    default:
                        $status = sprintf(
                            '<div class="lima-status unknown">%s</div>',
                            __('Unknown', 'lima')
                        );
                        break;
                }
                return $status;

            // Default switch case
            default:
                return $item[$column_name];
        }

        return $item[$column_name];
    }

    public function column_license_key($item)
    {
        if (Settings::get('_lima_hide_license_keys')) {
            $title = '<code class="lima-placeholder empty"></code>';
            $title .= sprintf('<img class="lima-spinner" data-id="%d" src="%s">', $item['id'], self::SPINNER_URL);
        } else {
            $title = sprintf('<code class="lima-placeholder">%s</code>', $this->crypto->decrypt($item['license_key']));
            $title .= sprintf('<img class="lima-spinner" data-id="%d" src="%s">', $item['id'], self::SPINNER_URL);
        }

        $actions = [
            'show' => sprintf(
                '<a class="lima-license-key-show" data-id="%d">%s</a>',
                $item['id'],
                __('Show', 'lima')
            ),
            'hide' => sprintf(
                '<a class="lima-license-key-hide" data-id="%d">%s</a>',
                $item['id'],
                __('Hide', 'lima')
            ),
            'activate' => sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=activate&id=%d&_wpnonce=%s',
                        AdminMenus::LICENSES_PAGE,
                        intval($item['id']),
                        wp_create_nonce('activate')
                    )
                ),
                __('Activate', 'lima')
            ),
            'deactivate' => sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=deactivate&id=%d&_wpnonce=%s',
                        AdminMenus::LICENSES_PAGE,
                        intval($item['id']),
                        wp_create_nonce('deactivate')
                    )
                ),
                __('Deactivate', 'lima')
            ),
            'delete' => sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=delete&id=%d&_wpnonce=%s',
                        AdminMenus::LICENSES_PAGE,
                        intval($item['id']),
                        wp_create_nonce('delete')
                    )
                ),
                __('Delete', 'lima')
            ),
        ];

        return $title . $this->row_actions($actions);
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'         => array('id', true),
            'order_id'   => array('order_id', true),
            'product_id' => array('product_id', true),
            'created_at' => array('created_at', true),
            'expires_at' => array('expires_at', true),
            'source'     => array('source', true),
            'status'     => array('status', true)
        );

        return $sortable_columns;
    }

    public function get_bulk_actions()
    {
        $actions = [
            'activate'   => __('Activate', 'lima'),
            'deactivate' => __('Deactivate', 'lima'),
            'delete'     => __('Delete', 'lima')
        ];

        return $actions;
    }

    public function process_bulk_action()
    {
        $action = $this->current_action();

        switch ($action) {
            case 'activate':
                $this->toggleLicenseKeyStatus(LicenseStatusEnum::ACTIVE);
                break;
            case 'deactivate':
                $this->toggleLicenseKeyStatus(LicenseStatusEnum::INACTIVE);
                break;
            case 'delete':
                $this->deleteLicenseKeys();
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

        $per_page     = $this->get_items_per_page('licenses_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

        if (array_key_exists('filter_action', (array)$_REQUEST)) {
            $this->filterLicenses();
        }

        $this->items = self::get_orders($per_page, $current_page);
    }

    protected function extra_tablenav($which) {
        $html = '';

        if ($which == "top") {
            $html    .= '<div class="alignleft actions">';
            $products = Database::getDistinct('product_id', Setup::LICENSES_TABLE_NAME);
            $orders   = Database::getDistinct('order_id', Setup::LICENSES_TABLE_NAME);

            if ($products) {
                $html .= '<select id="products" class="filter-product" name="product-filter">';
                $html .= sprintf('<option value="">%s</option>', __('Filter by Product', 'lima'));

                foreach ($products as $product) {
                    if ($product = wc_get_product($product->product_id)) {
                        $html .= sprintf('<option value="%d">%s</option>', $product->get_id(), $product->get_title());
                    }
                }

                $html .= '</select>';
            }

            if ($orders) {
                $html .= '<select id="orders" class="filter-order" name="order-filter">';
                $html .= sprintf('<option value="">%s</option>', __('Filter by Order', 'lima'));

                foreach ($orders as $order) {
                    if ($order = wc_get_order($order->order_id)) {
                        if ($user = $order->get_user()) {
                            $html .= sprintf(
                                '<option value="%d">#%d - %s</option>',
                                $order->get_id(),
                                $order->get_id(),
                                $user->data->display_name
                            );
                        } else {
                            $html .= sprintf(
                                '<option value="%d">#%d</option>',
                                $order->get_id(),
                                $order->get_id()
                            );
                        }

                    }
                }

                $html .= '</select>';
            }

            $html .= sprintf(
                '<input type="submit" name="filter_action" id="post-query-submit" class="button" value="%s">',
                __('Filter', 'lima')
            );
            $html .= '</div>';

            echo $html;
        }
    }

    public static function get_orders($per_page = 20, $page_number = 1)
    {
        global $wpdb;

        if (self::isViewFilterActive()) $where = $wpdb->prepare(' WHERE status = %d', intval($_GET['status']));

        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        $sql = "SELECT * FROM $table";
        if (isset($where)) $sql .= $where;
        $sql .= ' ORDER BY ' . (empty($_REQUEST['orderby']) ? 'id' : esc_sql($_REQUEST['orderby']));
        $sql .= ' '          . (empty($_REQUEST['order'])   ? 'DESC'  : esc_sql($_REQUEST['order']));
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    public static function record_count($status = null)
    {
        global $wpdb;

        $table = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

        if (self::isViewFilterActive()) {
            return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE status = %d", intval($_GET['status'])));
        } else {
            return $wpdb->get_var("SELECT COUNT(*) FROM $table");
        }
    }

    public function no_items()
    {
        _e('No licenses found.', 'lima');
    }

    public function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'id'          => __('ID', 'lima'),
            'license_key' => __('License Key', 'lima'),
            'order_id'    => __('Order', 'lima'),
            'product_id'  => __('Product', 'lima'),
            'created_at'  => __('Created at', 'lima'),
            'expires_at'  => __('Expires at', 'lima'),
            'valid_for'   => __('Valid for', 'lima'),
            'source'      => __('Source', 'lima'),
            'status'      => __('Status', 'lima')
        );

        return $columns;
    }

    private function verifyNonce($nonce_action)
    {
        if (
            !wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action) &&
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])
        ) {
            wp_redirect(admin_url(sprintf('admin.php?page=%s&lima_nonce_status=invalid', AdminMenus::LICENSES_PAGE)));
        }
    }

    /**
     * @todo Check if given ID's are linked to license keys with SOLD or DELIVERED status.
     */
    private function isValidRequest()
    {
        // *Tumbleweed rolls*
    }

    private function toggleLicenseKeyStatus($status)
    {
        ($status == LicenseStatusEnum::ACTIVE) ? $nonce_action = 'activate' : $nonce_action = 'deactivate';

        $this->verifyNonce($nonce_action);
        $this->isValidRequest();

        $result = apply_filters(
            'lima_toggle_license_key_status',
            array(
                'column_name' => 'id',
                'operator' => 'in',
                'value' => (array)$_REQUEST['id'],
                'status' => $status
            )
        );
    }

    private function deleteLicenseKeys()
    {
        $this->verifyNonce('delete');
        $this->isValidRequest();

        $result = apply_filters(
            'lima_delete_license_keys',
            array(
                'ids' => (array)($_REQUEST['id'])
            )
        );

        if ($result) {
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&lima_delete_license_key=true',
                        AdminMenus::LICENSES_PAGE
                    )
                )
            );
        } else {
            wp_redirect(
                admin_url(
                    sprintf(
                        'admin.php?page=%s&lima_delete_license_key=error',
                        AdminMenus::LICENSES_PAGE
                    )
                )
            );
        }
    }

    public static function isViewFilterActive()
    {
        if (array_key_exists('status', $_GET) && in_array($_GET['status'], LicenseStatusEnum::$statuses)) {
            return true;
        } else {
            return false;
        }
    }

    protected function filterLicenses()
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
    }
}