<?php

namespace LicenseManagerForWooCommerce\API\v1;

defined('ABSPATH') || exit;

/**
 * Create the LicenseKey endpoint.
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class LicenseKey extends \WP_REST_Controller
{
    public function register_routes()
    {
        $namespace = 'lmfwc/v1';

        register_rest_route($namespace, '/licenses/(?P<license_id>\d+)', [
            array(
              'methods'             => 'GET',
              'callback'            => array($this, 'getItems'),
              'permission_callback' => array($this, 'getItemsPermissionsCheck')
            )
        ]);

        register_rest_route($namespace, '/licenses' [
            array(
              'methods'             => 'GET',
              'callback'            => array($this, 'getAll'),
              'permission_callback' => array($this, 'getItemsPermissionsCheck')
            )
        ]);
    }

    public function getItems()
    {
        return new \WP_REST_Response('getItems!', 200);
    }

    public function getAll()
    {
        return new \WP_REST_Response('getAll!', 200);
    }

    public function getItemsPermissionsCheck($request)
    {
        print_r($request);
        return true;
    }
}