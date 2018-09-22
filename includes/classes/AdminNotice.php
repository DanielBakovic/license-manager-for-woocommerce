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
        add_action('admin_notices', array($this, 'initAdminNotices'));
    }

    public function initAdminNotices()
    {
        $this->importLicenseKeys();
        $this->addLicenseKey();
        $this->activateLicenseKey();
        $this->deleteLicenseKey();
        $this->invalidNonce();
    }

    private function importLicenseKeys()
    {
        // Return if this is not related.
        if (!isset($_GET['lima_import_license_keys'])) return;

        if ($_GET['lima_import_license_keys'] == 'error') {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                __('Something went wrong, no keys were added. Please try again.', 'lima')
            );
        } elseif ($_GET['lima_import_license_keys'] == 'true' && isset($_GET['added'])) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_SUCCESS,
                sprintf(
                    __('%d key(s) have been imported successfully.', 'lima'),
                    intval($_GET['added'])
                )
            );
        } elseif ($_GET['lima_import_license_keys'] == 'false' && isset($_GET['rejected'])) {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                sprintf(
                    __('Import failed. %d key(s) were not added.', 'lima'),
                    intval($_GET['rejected'])
                )
            );
        } elseif ($_GET['lima_import_license_keys'] == 'mixed' && isset($_GET['added']) && isset($_GET['rejected'])) {
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

    private function addLicenseKey()
    {
        // Return if this is not related.
        if (!isset($_GET['lima_add_license_key'])) return;

        if ($_GET['lima_add_license_key'] == 'true') {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_SUCCESS,
                __('Your license key has been added successfully.', 'lima')
            );
        } else {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                __('Something went wrong, your key was not added. Please try again.', 'lima')
            );
        }
    }

    private function activateLicenseKey()
    {
        // Return if this is not related.
        if (!isset($_GET['lima_activate_license_key'])) return;

        if ($_GET['lima_activate_license_key'] == 'true') {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_SUCCESS,
                __('Your license key has been activated successfully.', 'lima')
            );
        } else {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                __('Something went wrong, your license key was not deactivate. Please try again.', 'lima')
            );
        }
    }

    private function deleteLicenseKey()
    {
        // Return if this is not related.
        if (!isset($_GET['lima_delete_license_key'])) return;

        if ($_GET['lima_delete_license_key'] == 'true') {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_SUCCESS,
                __('Your license key has been activated successfully.', 'lima')
            );
        } else {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                __('Something went wrong, your license key was not deactivate. Please try again.', 'lima')
            );
        }
    }

    private function invalidNonce()
    {
        // Return if this is not related.
        if (!isset($_GET['lima_nonce_status'])) return;

        if ($_GET['lima_nonce_status'] == 'invalid') {
            echo sprintf(
                self::MESSAGE_DISMISSIBLE,
                self::NOTICE_ERROR,
                __('Invalid nonce! Your action was not completed.', 'lima')
            );
        }
    }

}