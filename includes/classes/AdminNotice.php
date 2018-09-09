<?php

namespace LicenseManager\Classes;

/**
 * Set up WordPress Admin Notices.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * AdminNotice Class.
 */
class AdminNotice
{
    const MESSAGE_DISMISSIBLE = '<div class="notice %s is-dismissible"><p>%s</p></div>';
    const MESSAGE_PERMANENT   = '<div class="notice %s"><p>%s</p></div>';

    const NOTICE_ERROR   = 'notice-error';
    const NOTICE_SUCCESS = 'notice-success';
    const NOTICE_WARNING = 'notice-warning';

    /**
     * Class constructor.
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'importLicenceKeys'));
    }

    public function importLicenceKeys()
    {
        // Return if this is not related to the import messages.
        if (!isset($_GET['import'])) return;

        if ($_GET['import'] == 'error') {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                __('Something went wrong, no keys were added. Please try again.', 'lima')
            );
        } elseif ($_GET['import'] == 'success' && isset($_GET['added'])) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_SUCCESS,
                sprintf(
                    __('%d key(s) have been imported successfully.', 'lima'),
                    intval($_GET['added'])
                )
            );
        } elseif ($_GET['import'] == 'failed' && isset($_GET['rejected'])) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                sprintf(
                    __('Import failed. %d key(s) were not added.', 'lima'),
                    intval($_GET['rejected'])
                )
            );
        } elseif ($_GET['import'] == 'mixed' && isset($_GET['added']) && isset($_GET['rejected'])) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_WARNING,
                sprintf(
                    __('%d key(s) have been imported and %d key(s) were not imported.', 'lima'),
                    intval($_GET['added']),
                    intval($_GET['rejected'])
                )
            );
        }
    }

}