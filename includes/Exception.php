<?php

namespace LicenseManagerForWooCommerce;

use Throwable;

defined('ABSPATH') || exit;

class Exception extends \Exception implements Throwable
{
    /**
     * @var string
     */
    const EMAIL_ADDRESS = 'info@bebic.at';

    /**
     * @var string
     */
    const EMAIL_SUBJECT_TEMPLATE = '[LMFWC] [Exception] [%s]';

    /**
     * Constructor
     *
     * @param string $message
     * @param integer $code
     * @param Exception $previous
     *
     * @throws \Exception
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
     * @param Exception $e The exception to be mailed.
     *
     * @throws \Exception
     */
    protected function mailException($e)
    {
        // We won't do this yet
        if (1 === 1) {
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