<?php

namespace LicenseManagerForWooCommerce\Abstracts;

defined('ABSPATH') || exit;

/**
 * Abstract class for basic REST controller functionality.
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class RestController extends \WP_REST_Controller
{
    /**
     * Flag used to identify an undefined state. In this case, missing parameters.
     *
     * @var integer
     */
    const UNDEFINED = -1;

    /**
     * Returns a standardized REST response
     * 
     * @param boolean $success
     * @param array   $data Response
     * @param integer $code HTTP status code
     * 
     * @return WP_REST_Response
     */
    protected function response($success, $data, $code = 200)
    {
        return new \WP_REST_Response(array(
            'success' => $success,
            'data' => $data
        ), $code);
    }


    /**
     * Determines if the string is a JSON object
     * 
     * @param string $string Possible JSON object
     * 
     * @since  1.1.0
     * @return boolean
     */
    protected function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}