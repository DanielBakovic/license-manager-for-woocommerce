<?php

namespace LicenseManagerForWooCommerce\Emails;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce TemplateParts.
 *
 * @since 1.2.0
 */
class TemplateParts
{
    /**
     * Create an instance of the class.
     */
    function __construct() {
        add_action('lmfwc_email_order_details',      array($this, 'addOrderDetails'),     10, 4);
        add_action('lmfwc_email_order_license_keys', array($this, 'addOrderLicenseKeys'), 10, 4);
    }

    /**
     * Adds the ordered license keys to the email body
     * 
     * @param WC_Order $order         The WooCommerce Order object
     * @param boolean  $sent_to_admin Should the admin receive a copy?
     * @param boolean  $plain_text    Is this a plain text email?
     * @param WC_Email $email         The plugin email object
     */
    public function addOrderDetails($order, $sent_to_admin, $plain_text, $email)
    {
        if ($plain_text) {
            echo wc_get_template(
                'emails/plain/email-order-details.php',
                array(
                    'order'         => $order,
                    'sent_to_admin' => false,
                    'plain_text'    => false,
                    'email'         => $email
                ),
                '',
                LMFWC_TEMPLATES_DIR
            );
        } else {
            echo wc_get_template_html(
                'emails/email-order-details.php',
                array(
                    'order'         => $order,
                    'sent_to_admin' => false,
                    'plain_text'    => false,
                    'email'         => $email
                ),
                '',
                LMFWC_TEMPLATES_DIR
            );
        }
    }

    /**
     * Adds basic order info to the email body
     * 
     * @param WC_Order $order         The WooCommerce Order object
     * @param boolean  $sent_to_admin Should the admin receive a copy?
     * @param boolean  $plain_text    Is this a plain text email?
     * @param WC_Email $email         The plugin email object
     */
    public function addOrderLicenseKeys($order, $sent_to_admin, $plain_text, $email)
    {
        if ($plain_text) {
            echo wc_get_template(
                'emails/plain/email-order-license-keys.php',
                array(
                    'heading'       => apply_filters('lmfwc_license_keys_table_heading', null),
                    'data'          => apply_filters('lmfwc_get_customer_license_keys', $order),
                    'date_format'   => get_option('date_format'),
                    'order'         => $order,
                    'sent_to_admin' => false,
                    'plain_text'    => false,
                    'email'         => $email
                ),
                '',
                LMFWC_TEMPLATES_DIR
            );
        } else {
            echo wc_get_template_html(
                'emails/email-order-license-keys.php',
                array(
                    'heading'       => apply_filters('lmfwc_license_keys_table_heading', null),
                    'data'          => apply_filters('lmfwc_get_customer_license_keys', $order),
                    'date_format'   => get_option('date_format'),
                    'order'         => $order,
                    'sent_to_admin' => false,
                    'plain_text'    => false,
                    'email'         => $email
                ),
                '',
                LMFWC_TEMPLATES_DIR
            );
        }
    }
}