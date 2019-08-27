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
    protected function isJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Checks whether a specific plugin API route is enabled.
     *
     * @param array  $settings Plugin settings array
     * @param string $routeId  Unique plugin API route ID
     *
     * @return bool
     */
    protected function isRouteEnabled($settings, $routeId)
    {
        if (!array_key_exists('lmfwc_enabled_api_endpoints', $settings)
            || !array_key_exists($routeId, $settings['lmfwc_enabled_api_endpoints'])
            || !$settings['lmfwc_enabled_api_endpoints'][$routeId]
        ) {
            return false;
        }

        return true;
    }
}