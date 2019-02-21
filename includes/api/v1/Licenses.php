<?php

namespace LicenseManagerForWooCommerce\API\v1;

defined('ABSPATH') || exit;

/**
 * Create the License endpoint.
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class Licenses extends \WP_REST_Controller
{

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'lmfwc/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $base = 'licenses';

    public function register_routes()
    {
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'getItems'),
                    'permission_callback' => array($this, 'getItemsPermissionsCheck'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<license_key>[\w-]+)', array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'getItems'),
                    'permission_callback' => array($this, 'getItemsPermissionsCheck'),
                    'args'                => array(
                        'license_key' => array(
                            'description' => __('Hashed license key.', 'lmfwc'),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
    }

    public function getItems($request)
    {
        print_r(get_class_methods($request));
        print_r(($request->get_param('license_key')));
        return new \WP_REST_Response('', 200);
    }

    public function getAll()
    {
        return new \WP_REST_Response('getAll!', 200);
    }

    public function getItemsPermissionsCheck($request)
    {
        return true;
    }
}