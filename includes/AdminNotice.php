<?php

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

/**
 * Set up WordPress Admin Notices.
 *
 * @version 1.1.0
 * @since 1.0.0
 */
class AdminNotice
{
    const MESSAGE_DISMISSIBLE = '<div class="notice %s is-dismissible"><p><b>License Manager</b>: %s</p></div>';
    const MESSAGE_PERMANENT   = '<div class="notice %s"><p>%s</p></div>';

    const NOTICE_ERROR   = 'notice-error';
    const NOTICE_SUCCESS = 'notice-success';
    const NOTICE_WARNING = 'notice-warning';
    const NOTICE_INFO    = 'notice-info';

    /**
     * Class constructor.
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'init'));
    }

    /**
     * Retrieves the notice message from the transients, displays it and finally deletes the transient itself.
     * 
     * @since 1.1.0
     */
    public function init()
    {
        if ($error = get_transient('lmfwc_notice_error')) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                $error->get_error_message()
            );

            delete_transient('lmfwc_notice_error');
        } elseif ($success = get_transient('lmfwc_notice_success')) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_SUCCESS,
                $success
            );

            delete_transient('lmfwc_notice_success');
        } elseif ($warning = get_transient('lmfwc_notice_warning')) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_WARNING,
                $warning
            );

            delete_transient('lmfwc_notice_warning');
        } elseif ($info = get_transient('lmfwc_notice_info')) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_INFO,
                $info
            );

            delete_transient('lmfwc_notice_info');
        }
    }

    /**
     * Adds a dashboard notice to be displayed on the next page reload.
     *
     * @since 1.1.0
     *
     * @param string $level
     * @param string $message
     * @param int $code
     * @param int $duration
     */
    public static function add($level, $message, $code = -1, $duration = 60)
    {
        switch ($level) {
            case 'error':
                Logger::exception(new \Exception($message, $code));
                set_transient('lmfwc_notice_error', new \WP_Error($code, $message), $duration);
                break;
            case 'success':
                set_transient('lmfwc_notice_success', $message, $duration);
                break;
            case 'warning':
                set_transient('lmfwc_notice_warning', $message, $duration);
                break;
            case 'info':
                set_transient('lmfwc_notice_info', $message, $duration);
                break;
        }
    }

    /**
     * Adds a generic error notice to the dashboard which is then displayed on the next page reload.
     *
     * @since 1.1.0
     *
     * @param int $code
     * @param int $duration
     */
    public static function addErrorSupportForum($code = -1, $duration = 60)
    {
        self::add(
            'error',
            sprintf(
                __(
                    'Oops! Something went wrong. Let us know by sending an error report <a href="%s" target="_blank" rel="noopener">in the support forum</a>.',
                    'lmfwc'
                ),
                'https://wordpress.org/support/plugin/license-manager-for-woocommerce/'
            ),
            $code,
            $duration
        );
    }
}