<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce\Emails;

use WC_Email;
use WC_Order;

defined('ABSPATH') || exit;

class CustomerDeliverLicenseKeys extends WC_Email
{
    /**
     * CustomerDeliverLicenseKeys constructor.
     */
    function __construct() {
        // Email slug we can use to filter other data.
        $this->id          = 'lmfwc_email_customer_deliver_license_keys';
        $this->title       = __('Deliver license keys', 'lmfwc');
        $this->description = __('A manual email to send out license keys to the customer.', 'lmfwc');

        // For admin area to let the user know we are sending this email to customers.
        $this->customer_email = true;
        $this->heading        = __('License key delivery', 'lmfwc');

        // translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
        $this->subject = sprintf(
            _x(
                '[%s] - Your license keys are here!',
                'Default email subject for resent license key emails sent to the customer',
                'lmfwc'
            ),
            '{blogname}'
        );

        // Template paths.
        $this->template_html  = 'emails/customer-deliver-license-keys.php';
        $this->template_plain = 'emails/plain/customer-deliver-license-keys.php';
        $this->template_base  = LMFWC_TEMPLATES_DIR;

        // Action to which we hook onto to send the email.
        add_action('lmfwc_email_customer_deliver_license_keys', array($this, 'trigger'));

        parent::__construct();
    }

    /**
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html(
            $this->template_html,
            array(
                'order'         => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => false,
                'email'         => $this
            ),
            '',
            $this->template_base
        );
    }
    /**
     * @return string
     */
    public function get_content_plain()
    {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'order'         => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => true,
                'email'         => $this
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param integer       $orderId
     * @param WC_Order|bool $order
     */
    public function trigger($orderId, $order = false)
    {
        $this->setup_locale();

        if ($orderId && ! is_a($order, 'WC_Order')) {
            $order = wc_get_order($orderId);
        }

        if (is_a($order, 'WC_Order')) {
            $this->object                         = $order;
            $this->recipient                      = $this->object->get_billing_email();
            $this->placeholders['{order_date}']   = wc_format_datetime($this->object->get_date_created());
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        }

        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_content(),
                $this->get_headers(),
                $this->get_attachments()
            );
        }

        $this->restore_locale();
    }
}