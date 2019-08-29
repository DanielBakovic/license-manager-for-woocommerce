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
     * @param bool   $success Indicates whether the request was successful or not
     * @param array  $data    Contains the response data
     * @param int    $code    Contains the response HTTP status code
     * @param string $route   Contains the request route name
     *
     * @return WP_REST_Response
     */
    protected function response($success, $data, $code = 200, $route)
    {
        return new WP_REST_Response(
            array(
                'success' => $success,
                'data'    => apply_filters('lmfwc_rest_api_pre_response', $_SERVER['REQUEST_METHOD'], $route, $data)
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
     * Checks whether a specific API route is enabled.
     *
     * @param array  $settings Plugin settings array
     * @param string $routeId  Unique plugin API endpoint ID
     *
     * @return bool
     */
    protected function isRouteEnabled($settings, $routeId)
    {
        if (!array_key_exists('lmfwc_enabled_api_routes', $settings)
            || !array_key_exists($routeId, $settings['lmfwc_enabled_api_routes'])
            || !$settings['lmfwc_enabled_api_routes'][$routeId]
        ) {
            return false;
        }

        return true;
    }

    /**
     * Returns the default error for disabled routes.
     *
     * @return WP_Error
     */
    protected function routeDisabledError()
    {
        return new WP_Error(
            'lmfwc_rest_route_disabled_error',
            'This route is disabled via the plugin settings.',
            array('status' => 404)
        );
    }
}