<?php

namespace LicenseManager\Classes;

/**
 * LicenseManager Logger.
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Logger class.
 */
class Logger
{
    /**
     * Directory containing the log files (inside the main plugin folder)
     *
     * @since 1.0.0
     */
    const LOG_DIR = 'logs';

    /**
     * Debug file name
     *
     * @since 1.0.0
     */
    const DEBUG_FILE = 'debug.log';

    /**
     * Error file name
     *
     * @since 1.0.0
     */
    const ERROR_FILE = 'error.log';

    /**
     * Helper function for converting any PHP value into a string.
     *
     * @since 1.0.0
     */
    protected static function objectToString($object, $JSCode = false)
    {
        static $object_to_string_map = null;

        if (is_null($object_to_string_map)) {
            // https://secure.php.net/manual/en/function.gettype.php#refsect1-function.gettype-returnvalues
            $object_to_string_map = array(
                'boolean' => function (&$object, &$JSCode) {
                    return $object ? 'true' : 'false';
                },
                'integer' => function (&$object, &$JSCode) {
                    return strval($object);
                },
                'double' => function (&$object, &$JSCode) {
                    return strval($object);
                },
                'string' => function (&$object, &$JSCode) {
                    return $JSCode ? json_encode($object) : $object;
                },
                'array' => function (&$object, &$JSCode) {
                    return $JSCode ?
                        'JSON.parse(' . json_encode(json_encode(
                            $object,
                            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        )) . ')' :
                        json_encode(
                            $object,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        );
                },
                'object' => function (&$object, &$JSCode) {
                    return $JSCode ?
                        json_encode(print_r($object, true)) :
                        print_r($object, true);
                },
                'resource' => function (&$object, &$JSCode) {
                    return $JSCode ?
                        json_encode('Resource of type "' . get_resource_type($object) . '"') :
                        'Resource of type "' . get_resource_type($object) . '"';
                },
                'NULL' => function (&$object, &$JSCode) {
                    return 'null';
                },
                'unknown type' => function (&$object, &$JSCode) {
                    return $JSCode ? json_encode('unknown type') : 'unknown type';
                }
            );
        }

        return $object_to_string_map[gettype($object)]($object, $JSCode);
    }

    /**
     * Helper function for getting a log label from the backtrace.
     *
     * @since 1.0.0
     */
    protected static function labelFromBacktrace($backtrace)
    {
        return substr($backtrace[0]['file'], ABSPATH_LENGTH) . ':' . $backtrace[0]['line'];
    }

    /**
     * Log a value to the browser console.
     *
     * @since 1.0.0
     */
    public static function console($object, $label = null)
    {
        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        if (!empty($label)) {
            $label = json_encode($label . "\n") . ', ';
        }

        $object_str = self::objectToString($object, true);

        add_action(is_admin() ? 'admin_footer' : 'wp_footer', function () use (&$object_str, &$label) {
            ?><script type="text/javascript">console.log(<?= $label . $object_str; ?>);</script><?php
        }, 65535);
    }

    /**
     * Log the backtrace to the browser console.
     *
     * @since 1.0.0
     */
    public static function consoleBacktrace($chronological = true, $label = null)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace($backtrace);
        }
        foreach ($backtrace as &$entry) {
            if (isset($entry['file'])) {
                $entry['file'] = substr($entry['file'], ABSPATH_LENGTH);
            }
        }
        if ($chronological) {
            $backtrace = array_reverse($backtrace);
            array_pop($backtrace);
        } else {
            array_shift($backtrace);
        }
        self::console($backtrace, $label);
    }

    /**
     * Log a value to a file.
     *
     * @since 1.0.0
     */
    public static function file($object, $filename = self::DEBUG_FILE, $label = null)
    {
        static $log_files = array();

        if (gettype($filename) !== 'string') {
            $filename = self::DEBUG_FILE;
        }

        if (!isset($log_files[$filename])) {
            $log_files[$filename] = fopen(LM_LOG_DIR . $filename, 'ab');

            register_shutdown_function(function () use (&$log_files, &$filename) {
                fclose($log_files[$filename]);
            });
        }

        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        if (!empty($label)) {
            $label .= "\n";
        }

        fwrite($log_files[$filename], $label . self::objectToString($object) . "\n\n\n\n");
    }

    /**
     * Log the backtrace to a file.
     *
     * @since 1.0.0
     */
    public static function fileBacktrace($chronological = true, $filename = self::DEBUG_FILE, $label = null)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace($backtrace);
        }

        foreach ($backtrace as &$entry) {
            if (isset($entry['file'])) {
                $entry['file'] = substr($entry['file'], ABSPATH_LENGTH);
            }
        }

        if ($chronological) {
            $backtrace = array_reverse($backtrace);
            array_pop($backtrace);
        } else {
            array_shift($backtrace);
        }

        self::file($backtrace, $filename, $label);
    }

    /**
     * Log error messages or exception to the exception log.
     *
     * @since 1.0.0
     */
    public static function exception($error)
    {
        if ($error instanceof \Exception) {

            $date = new \DateTime();

            $message = sprintf("Exception thrown at: %s\n", $date->format('Y-m-d H:i'));
            $message .= 'Message: ' . $error->getMessage() . "\n";
            $message .= 'Code: ' . $error->getCode() . "\n";
            $message .= 'Thrown at: ' . $error->getFile() . ':' . $error->getLine() . "\n";
            $message .= "Trace:\n";

            foreach ($error->getTrace() as $id => $trace) {
                $message .= '    [' . $id . '] ';
                if (isset($trace['class']) && isset($trace['type'])) {
                    $message .= $trace['class'] . $trace['type'] . $trace['function'] . '() | ';
                } else {
                    $message .= $trace['function'] . '() | ';
                }
                $message .= $trace['file'] . ':' . $trace['line'] . "\n";
            }

            self::file($message, self::ERROR_FILE);
        } else {
            self::file($error, self::ERROR_FILE);
        }
    }

}