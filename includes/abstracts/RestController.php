<?php

namespace LicenseManagerForWooCommerce\Abstracts;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;

defined('ABSPATH') || exit;

class RestController extends WP_REST_Controller
{
    /**
     * Returns a structured response object for the API.
     *
     * @param bool  $success
     * @param array $data
     * @param int   $code
     * 
     * @return WP_REST_Response|WP_Error
     */
    protected function response($success, $data, $code = 200)
    {
        return new WP_REST_Response(
            array(
                'success' => $success,
                'data' => $data
            ),
            $code
        );
    }

    /**
     * Checks if the given string is a JSON object.
     *
     * @param string $string
     * 
     * @return bool
     */
    protected function isJson($string) {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}