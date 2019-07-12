<?php

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;

defined('ABSPATH') || exit;

class AdminNotice
{
    const MESSAGE_DISMISSIBLE = '<div class="notice %s is-dismissible"><p><b>License Manager</b>: %s</p></div>';
    const MESSAGE_PERMANENT   = '<div class="notice %s"><p>%s</p></div>';

    const NOTICE_ERROR   = 'notice-error';
    const NOTICE_SUCCESS = 'notice-success';
    const NOTICE_WARNING = 'notice-warning';
    const NOTICE_INFO    = 'notice-info';

    /**
     * AdminNotice constructor.
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'init'));
    }

    /**
     * Retrieves the notice message from the transients, displays it and finally deletes the transient itself.
     */
    public function init()
    {
        if ($error = get_transient('lmfwc_notice_error')) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                $error
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
     * @param string $level
     * @param string $message
     * @param int    $code
     * @param int    $duration
     */
    public static function add($level, $message, $code = 0, $duration = 60)
    {
        switch ($level) {
            case 'error':
                set_transient('lmfwc_notice_error', $message, $duration);
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
     * Log and display exception
     * 
     * @param string  $message
     * @param integer $duration
     */
    public static function error($message, $duration = 60)
    {
        $e = new LMFWC_Exception($message);

        $message = '';
        $message .= $e->getMessage();
        $message .= ' ';
        $message .= sprintf(
            __('If you are having trouble solving the problem on your own, please let us know by sending an error report <a href="%s" target="_blank" rel="noopener">in the support forum</a>.', 'lmfwc'),
            'https://wordpress.org/support/plugin/license-manager-for-woocommerce/'
        );

        self::add('error', $message, $e->getCode(), $duration);
    }

    /**
     * Display a success message
     * 
     * @param string $message The success message to be display
     */
    public static function success($message)
    {
        self::add('success', $message);
    } 

    /**
     * Display a warning message
     * 
     * @param string $message
     */
    public static function warning($message)
    {
        self::add('warning', $message);
    } 

    /**
     * Display a info message
     * 
     * @param string $message
     */
    public static function info($message)
    {
        self::add('info', $message);
    } 
}