<?php

namespace LicenseManagerForWooCommerce\Lists;

use \LicenseManagerForWooCommerce\AdminMenus;
use \LicenseManagerForWooCommerce\AdminNotice;
use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;
use LicenseManagerForWooCommerce\Repositories\Resources\ApiKey as ApiKeyResourceRepository;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create the API Key list
 *
 * @version 1.0.0
 * @since 1.0.0
 */
class APIKeyList extends \WP_List_Table
{
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct([
            'singular' => __('Key', 'lmfwc'),
            'plural'   => __('Keys', 'lmfwc'),
            'ajax'     => false
        ]);
    }

    /**
     * No items found text.
     * 
     * @return null
     */
    public function no_items()
    {
        _e('No keys found.', 'lmfwc');
    }

    /**
     * Get list columns.
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'title'         => __('Description', 'lmfwc'),
            'truncated_key' => __('Consumer key ending in', 'lmfwc'),
            'user'          => __('User', 'lmfwc'),
            'permissions'   => __('Permissions', 'lmfwc'),
            'last_access'   => __('Last access', 'lmfwc'),
        );

        return $columns;
    }

    /**
     * Column cb.
     *
     * @param  array $key Key data.
     * @return string
     */
    public function column_cb($key)
    {
        return sprintf('<input type="checkbox" name="key[]" value="%1$s" />', $key['id']);
    }

    /**
     * Return title column.
     *
     * @param  array $key Key data.
     * @return string
     */
    public function column_title($key)
    {
        $key_id  = intval($key['id']);
        $url     = admin_url(sprintf('admin.php?page=%s&tab=rest_api&edit_key=%d', AdminMenus::SETTINGS_PAGE, $key_id));
        $user_id = intval($key['user_id']);

        // Check if current user can edit other users or if it's the same user.
        $can_edit = current_user_can('edit_user', $user_id) || get_current_user_id() === $user_id;

        $output = '<strong>';

        if ($can_edit) {
            $output .= '<a href="' . esc_url($url) . '" class="row-title">';
        }

        if (empty($key['description'])) {
            $output .= esc_html__('API key', 'lmfwc');
        } else {
            $output .= esc_html($key['description']);
        }

        if ($can_edit) {
            $output .= '</a>';
        }

        $output .= '</strong>';

        // Get actions.
        $actions = array(
            'id' => sprintf(__('ID: %d', 'lmfwc'), $key_id),
        );

        if ($can_edit) {
            $actions['edit']  = '<a href="' . esc_url($url) . '">' . __('View/Edit', 'lmfwc') . '</a>';
            $actions['trash'] = '<a class="submitdelete" aria-label="' . esc_attr__('Revoke API key', 'lmfwc') . '" href="' . esc_url(
                wp_nonce_url(
                    add_query_arg(
                        array(
                            'action' => 'revoke',
                            'key' => $key_id,
                        ),
                        admin_url(sprintf('admin.php?page=%s&tab=rest_api', AdminMenus::SETTINGS_PAGE))
                    ),
                    'revoke'
                )
            ) . '">' . esc_html__('Revoke', 'lmfwc') . '</a>';
        }

        $row_actions = array();

        foreach ($actions as $action => $link) {
            $row_actions[] = '<span class="' . esc_attr($action) . '">' . $link . '</span>';
        }

        $output .= '<div class="row-actions">' . implode(' | ', $row_actions) . '</div>';

        return $output;
    }

    /**
     * Return truncated consumer key column.
     *
     * @param  array $key Key data.
     * @return string
     */
    public function column_truncated_key($key)
    {
        return '<code>&hellip;' . esc_html($key['truncated_key']) . '</code>';
    }

    /**
     * Return user column.
     *
     * @param  array $key Key data.
     * @return string
     */
    public function column_user($key)
    {
        $user = get_user_by('id', $key['user_id']);

        if (!$user) {
            return '';
        }

        if (current_user_can('edit_user', $user->ID)) {
            return '<a href="' . esc_url(add_query_arg(array('user_id' => $user->ID), admin_url('user-edit.php'))) . '">' . esc_html($user->display_name) . '</a>';
        }

        return esc_html($user->display_name);
    }

    /**
     * Return permissions column.
     *
     * @param  array $key Key data.
     * @return string
     */
    public function column_permissions($key)
    {
        $permission_key = $key['permissions'];
        $permissions    = array(
            'read'       => __('Read', 'lmfwc'),
            'write'      => __('Write', 'lmfwc'),
            'read_write' => __('Read/Write', 'lmfwc'),
        );

        if (isset($permissions[$permission_key])) {
            return esc_html($permissions[$permission_key]);
        } else {
            return '';
        }
    }

    /**
     * Return last access column.
     *
     * @param  array $key Key data.
     * @return string
     */
    public function column_last_access($key)
    {
        if (!empty($key['last_access'])) {
            $date = sprintf(
                __('%1$s at %2$s', 'lmfwc'),
                date_i18n(wc_date_format(), strtotime($key['last_access'])),
                date_i18n(wc_time_format(), strtotime($key['last_access']))
            );

            return apply_filters('woocommerce_api_key_last_access_datetime', $date, $key['last_access']);
        }

        return __('Unknown', 'lmfwc');
    }

    /**
     * Get bulk actions.
     *
     * @return array
     */
    protected function get_bulk_actions()
    {
        if (!current_user_can('remove_users')) {
            return array();
        }

        return array(
            'revoke' => __('Revoke', 'lmfwc'),
        );
    }

    /**
     * Handle bulk action requests.
     *
     * @return null
     */
    public function process_bulk_action()
    {
        if (!$action = $this->current_action()) {
            return;
        }

        if (!current_user_can('remove_users')) {
            return;
        }

        switch ($action) {
            case 'revoke':
                $this->verifyNonce('revoke');
                $this->revokeKeys();
                break;
            default:
                break;
        }
    }

    /**
     * Search box.
     *
     * @param string $text     Button text.
     * @param string $input_id Input ID.
     */
    public function search_box($text, $input_id)
    {
        if (empty($_REQUEST['s']) && ! $this->has_items()) {
            return;
        }

        $input_id     = $input_id . '-search-input';
        $search_query = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="' . esc_attr($input_id) . '">' . esc_html($text) . ':</label>';
        echo '<input type="search" id="' . esc_attr($input_id) . '" name="s" value="' . esc_attr($search_query) . '" />';

        submit_button(
            $text, '', '', false,
            array(
                'id' => 'search-submit',
            )
        );

        echo '</p>';
    }

    /**
     * Prepare table list items.
     */
    public function prepare_items()
    {
        global $wpdb;

        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
        );

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('lmfwc_keys_per_page');
        $current_page = $this->get_pagenum();

        if (1 < $current_page) {
            $offset = $per_page * ($current_page - 1);
        } else {
            $offset = 0;
        }

        $search = '';

        if (!empty($_REQUEST['s'])) {
            $search = "AND description LIKE '%" . esc_sql($wpdb->esc_like(wc_clean(wp_unslash($_REQUEST['s'])))) . "%' ";
        }

        // Get the API keys.
        $keys = $wpdb->get_results("
            SELECT
                id, user_id, description, permissions, truncated_key, last_access
            FROM
                {$wpdb->prefix}lmfwc_api_keys
            WHERE
                1=1
                {$search}"
            . $wpdb->prepare('ORDER BY id DESC LIMIT %d OFFSET %d;', $per_page, $offset), ARRAY_A
        );

        $count = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}lmfwc_api_keys WHERE 1 = 1 {$search};");

        $this->items = $keys;

        // Set the pagination.
        $this->set_pagination_args(
            array(
                'total_items' => $count,
                'per_page'    => $per_page,
                'total_pages' => ceil($count / $per_page),
            )
        );
    }

    /**
     * Checks if a valid nonce has been passed.
     */
    private function verifyNonce($nonce_action)
    {
        if (
            !wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action) &&
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])
        ) {
            AdminNotice::error(__('The nonce is invalid or has expired.', 'lmfwc'));
            wp_redirect(
                admin_url(sprintf('admin.php?page=%s', AdminMenus::GENERATORS_PAGE))
            );

            exit();
        }
    }

    private function revokeKeys()
    {
        if ($count = ApiKeyResourceRepository::instance()->delete((array)$_REQUEST['key'])) {
            AdminNotice::success(
                sprintf(__('%d API key(s) permanently revoked.', 'lmfwc'), $count)
            );
        } else {
            AdminNotice::error(
                __('There was a problem revoking the API key(s).', 'lmfwc')
            );
        }

        wp_redirect(sprintf('admin.php?page=%s&tab=rest_api', AdminMenus::SETTINGS_PAGE));
        exit();
    }

}