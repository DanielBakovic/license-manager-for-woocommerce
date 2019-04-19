<?php

namespace LicenseManagerForWooCommerce\Emails;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce CustomerPreorderComplete.
 *
 * @since 1.2.0
 */
class CustomerPreorderComplete extends \WC_Email
{
    /**
     * Create an instance of the class.
     *
     * @return null
     */
    function __construct() {
        // Email slug we can use to filter other data.
        $this->id          = 'lmfwc_email_customer_preorder_complete';
        $this->title       = __('Completed preorder', 'lmfwc');
        $this->description = __('An email sent to the customer when a license key preorder is complete.', 'lmfwc');

        // For admin area to let the user know we are sending this email to customers.
        $this->customer_email = true;
        $this->heading        = __('Preorder complete', 'lmfwc');

        // translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
        $this->subject = sprintf(
            _x(
                '[%s] - Your preorder has arrived!',
                'default email subject for cancelled emails sent to the customer',
                'lmfwc'
            ),
            '{blogname}'
        );
    
        // Template paths.
        $this->template_html  = 'emails/customer-preorder-complete.php';
        $this->template_plain = 'emails/plain/customer-preorder-complete.php';
        $this->template_base  = LMFWC_TEMPLATES_DIR;
    
        // Action to which we hook onto to send the email.
        add_action('lmfwc_email_customer_preorder_complete', array($this, 'trigger'));

        parent::__construct();
    }

    /**
     * Get content html.
     *
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
     * Get content plain.
     *
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
     * @param integer        $order_id The order ID.
     * @param WC_Order|false $order    Order object.
     */
    public function trigger($order_id, $order = false)
    {
        $this->setup_locale();

        if ($order_id && ! is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
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