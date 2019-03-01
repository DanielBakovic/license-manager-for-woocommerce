<?php

namespace LicenseManagerForWooCommerce\Lists;

use \LicenseManagerForWooCommerce\AdminMenus;
use \LicenseManagerForWooCommerce\AdminNotice;
use \LicenseManagerForWooCommerce\Database;
use \LicenseManagerForWooCommerce\Logger;
use \LicenseManagerForWooCommerce\Settings;
use \LicenseManagerForWooCommerce\Setup;
use \LicenseManagerForWooCommerce\Enums\LicenseStatusEnum;
use \LicenseManagerForWooCommerce\Enums\SourceEnum;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create the Licenses list
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class LicensesList extends \WP_List_Table
{
    const SPINNER_URL = '/wp-admin/images/loading.gif';

    /**
     * @string
     */
    private $date_format;

    /**
     * @string
     */
    private $time_format;

    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct([
            'singular' => __('License', 'lmfwc'),
            'plural'   => __('Licenses', 'lmfwc'),
            'ajax'     => false
        ]);

        $this->date_format = get_option('date_format');
        $this->time_format = get_option('time_format');
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
            __('All', 'lmfwc'),
            Database::getLicenseKeyCount()
        );

        // Sold link
        $class         = ($current == LicenseStatusEnum::SOLD ? ' class="current"' :'');
        $sold_url      = esc_url(add_query_arg('status', LicenseStatusEnum::SOLD));
        $status_links['sold'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $sold_url,
            $class,
            __('Sold', 'lmfwc'),
            Database::getLicenseKeyCount(LicenseStatusEnum::SOLD)
        );

        // Delivered link
        $class         = ($current == LicenseStatusEnum::DELIVERED ? ' class="current"' :'');
        $delivered_url = esc_url(add_query_arg('status', LicenseStatusEnum::DELIVERED));
        $status_links['delivered'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $delivered_url,
            $class,
            __('Delivered', 'lmfwc'),
            Database::getLicenseKeyCount(LicenseStatusEnum::DELIVERED)
        );

        // Active link
        $class      = ($current == LicenseStatusEnum::ACTIVE ? ' class="current"' :'');
        $active_url = esc_url(add_query_arg('status', LicenseStatusEnum::ACTIVE));
        $status_links['active'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $active_url,
            $class,
            __('Active', 'lmfwc'),
            Database::getLicenseKeyCount(LicenseStatusEnum::ACTIVE)
        );

        // Inactive link
        $class        = ($current == LicenseStatusEnum::INACTIVE ? ' class="current"' :'');
        $inactive_url = esc_url(add_query_arg('status', LicenseStatusEnum::INACTIVE));
        $status_links['inactive'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $inactive_url,
            $class,
            __('Inactive', 'lmfwc'),
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
            case 'created_at':
                if ($item['created_at']) {
                    $date_time = new \DateTime($item['created_at']);

                    $created_at = sprintf(
                        '%s<br>%s',
                        $date_time->format($this->date_format),
                        $date_time->format($this->time_format)
                    );
                } else {
                    $created_at = '';
                }

                return $created_at;
            case 'expires_at':
                if ($item['expires_at']) {
                    $date_time = new \DateTime($item['expires_at']);

                    $expires_at = sprintf(
                        '%s <br> %s',
                        $date_time->format($this->date_format),
                        $date_time->format($this->time_format)
                    );
                } else {
                    $expires_at = '';
                }

                return $expires_at;
            case 'valid_for':
                if ($item['valid_for']) {
                    $link = sprintf(__('<b>%d</b> day(s)', 'lmfwc'), intval($item['valid_for']));
                    $link .= '<br>';
                    $link .= sprintf('<small>%s</small>', __('After purchase', 'lmfwc'));
                } else {
                    $link = '';
                }
                return $link;
            case 'source':
                switch ($item['source']) {
                    case SourceEnum::GENERATOR:
                        $status = sprintf(
                            '<img class="lmfwc-source-icon" src="%s" alt="%s" title="%s">',
                            LMFWC_IMG_URL . 'icons/ic_settings_black_24dp.png',
                            __('Generator', 'lmfwc'),
                            __('Generator', 'lmfwc')
                        );
                        break;
                    case SourceEnum::IMPORT:
                        $status = sprintf(
                            '<img class="lmfwc-source-icon" src="%s" alt="%s" title="%s">',
                            LMFWC_IMG_URL . 'icons/ic_import_export_black_24dp.png',
                            __('Import', 'lmfwc'),
                            __('Import', 'lmfwc')
                        );
                        break;
                    case SourceEnum::API:
                        $status = sprintf(
                            '<img class="lmfwc-source-icon" src="%s" alt="%s" title="%s">',
                            LMFWC_IMG_URL . 'icons/ic_cloud_black_24dp.png',
                            __('API', 'lmfwc'),
                            __('API', 'lmfwc')
                        );
                        break;

                    // Default switch case
                    default:
                        $status = __('Unknown', 'lmfwc');
                        break;
                }
                return $status;
            case 'status':
                switch ($item['status']) {
                    case LicenseStatusEnum::SOLD:
                        $status = sprintf(
                            '<span class="lmfwc-status sold">%s</span>',
                            __('Sold', 'lmfwc')
                        );
                        break;
                    case LicenseStatusEnum::DELIVERED:
                        $status = sprintf(
                            '<span class="lmfwc-status delivered">%s</span>',
                            __('Delivered', 'lmfwc')
                        );
                        break;
                    case LicenseStatusEnum::ACTIVE:
                        $status = sprintf(
                            '<span class="lmfwc-status active">%s</span>',
                            __('Active', 'lmfwc')
                        );
                        break;
                    case LicenseStatusEnum::INACTIVE:
                        $status = sprintf(
                            '<span class="lmfwc-status inactive">%s</span>',
                            __('Inactive', 'lmfwc')
                        );
                        break;
                    case LicenseStatusEnum::USED:
                        $status = sprintf(
                            '<span class="lmfwc-status used">%s</span>',
                            __('Used', 'lmfwc')
                        );
                        break;

                    // Default switch case
                    default:
                        $status = sprintf(
                            '<div class="lmfwc-status unknown">%s</div>',
                            __('Unknown', 'lmfwc')
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
        if (Settings::get('lmfwc_hide_license_keys')) {
            $title = '<code class="lmfwc-placeholder empty"></code>';
            $title .= sprintf('<img class="lmfwc-spinner" data-id="%d" src="%s">', $item['id'], self::SPINNER_URL);
        } else {
            $title = sprintf('<code class="lmfwc-placeholder">%s</code>', apply_filters('lmfwc_decrypt', $item['license_key']));
            $title .= sprintf('<img class="lmfwc-spinner" data-id="%d" src="%s">', $item['id'], self::SPINNER_URL);
        }

        $actions = [
            'id' => sprintf(__('ID: %d', 'lmfwc'), intval($item['id'])),
            'show' => sprintf(
                '<a class="lmfwc-license-key-show" data-id="%d">%s</a>',
                $item['id'],
                __('Show', 'lmfwc')
            ),
            'hide' => sprintf(
                '<a class="lmfwc-license-key-hide" data-id="%d">%s</a>',
                $item['id'],
                __('Hide', 'lmfwc')
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
                __('Activate', 'lmfwc')
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
                __('Deactivate', 'lmfwc')
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
                __('Delete', 'lmfwc')
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
            'activate'   => __('Activate', 'lmfwc'),
            'deactivate' => __('Deactivate', 'lmfwc'),
            'delete'     => __('Delete', 'lmfwc')
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
        _e('No licenses found.', 'lmfwc');
    }

    public function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'license_key' => __('License Key', 'lmfwc'),
            'order_id'    => __('Order', 'lmfwc'),
            'product_id'  => __('Product', 'lmfwc'),
            'created_at'  => __('Created at', 'lmfwc'),
            'expires_at'  => __('Expires at', 'lmfwc'),
            'valid_for'   => __('Valid for', 'lmfwc'),
            'source'      => __('Source', 'lmfwc'),
            'status'      => __('Status', 'lmfwc')
        );

        return $columns;
    }

    private function verifyNonce($nonce_action)
    {
        if (
            !wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action) &&
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])
        ) {
            AdminNotice::addErrorSupportForum(9);
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::LICENSES_PAGE)));
            wp_die();
        }
    }
    private function toggleLicenseKeyStatus($status)
    {
        ($status == LicenseStatusEnum::ACTIVE) ? $nonce_action = 'activate' : $nonce_action = 'deactivate';

        $this->verifyNonce($nonce_action);

        $result = apply_filters(
            'lmfwc_toggle_license_key_status',
            array(
                'column_name' => 'id',
                'operator' => 'in',
                'value' => (array)$_REQUEST['id'],
                'status' => $status
            )
        );

        if ($result) {
            AdminNotice::add(
                'success',
                __('Your license key(s) have been altered successfully.', 'lmfwc')
            );
        } else {
            AdminNotice::addErrorSupportForum(6);
        }
    }

    private function deleteLicenseKeys()
    {
        $this->verifyNonce('delete');

        $result = apply_filters(
            'lmfwc_delete_license_keys',
            array(
                'ids' => (array)($_REQUEST['id'])
            )
        );

        if ($result) {
            AdminNotice::add(
                'success',
                __('Your license key(s) have been deleted successfully.', 'lmfwc')
            );
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::LICENSES_PAGE)));
        } else {
            AdminNotice::addErrorSupportForum(7);
            wp_redirect(admin_url(sprintf('admin.php?page=%s', AdminMenus::LICENSES_PAGE)));
        }

        wp_die();
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