<?php
/**
 * Exception
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce Exception
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class Exception extends \Exception implements \Throwable
{
    /**
     * Error reporting email
     * 
     * @var string
     */
    const EMAIL_ADDRESS = 'info@bebic.at';

    /**
     * Error reporting email
     * 
     * @var string
     */
    const EMAIL_SUBJECT_TEMPLATE = '[LMFWC] [Exception] [%s]';

    /**
     * Constructor
     * 
     * @param string    $message  The exception message
     * @param integer   $code     The exception code
     * @param Exception $previous Previously thrown exception
     *
     * @since  1.2.0
     * @return null
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        // Construct the error object
        parent::__construct($message, $code);

        // Mail the error (only if the option is on)
        $this->mailException($this);

        // Log the exception locally
        Logger::exception($this);
    }

    /**
     * Email the exception if the setting is on
     * 
     * @param LicenseManagerForWooCommerce\Exception $e The exception to be mailed.
     * 
     * @return null
     */
    protected function mailException($e)
    {
        // We won't do this yet
        if (1 == 1) {
            return;
        }

        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $log = ob_get_clean();

        $message_mail = '';
        $message_mail .= "MESSAGE: ";
        $message_mail .= $e->getMessage();
        $message_mail .= "\n\n";
        $message_mail .= "CODE: ";
        $message_mail .= $e->getCode();
        $message_mail .= "\n\n";
        $message_mail .= "TRACE:\n\n";
        $message_mail .= $e->getTraceAsString();

        $sent = wp_mail(
            self::EMAIL_ADDRESS,
            sprintf(self::EMAIL_SUBJECT_TEMPLATE, get_site_url()),
            $message_mail
        );

        if (!$sent) {
            Logger::exception(new \Exception('Exception mail was not sent', 99, $this));
        }
    }



}