<?php

namespace LicenseManagerForWooCommerce\API\v1;

use \LicenseManagerForWooCommerce\Logger;

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

    /**
     * Register all the needed routes for this resource.
     */
    public function register_routes()
    {
        // GET license
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'getLicenses'),
                    'permission_callback' => array($this, 'getLicensesPermissionCheck'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // POST license
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'postLicenses'),
                    'permission_callback' => array($this, 'postLicensesPermissionCheck'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // GET license/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'getLicense'),
                    'permission_callback' => array($this, 'getLicensePermissionsCheck'),
                    'args'                => array(
                        'key' => array(
                            'description' => __('License key ID.', 'lmfwc'),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // POST license/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key_id>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'postLicense'),
                    'permission_callback' => array($this, 'postLicensePermissionsCheck'),
                    'args'                => array(
                        'key_id' => array(
                            'description' => __('License key ID.', 'lmfwc'),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
    }

    public function getLicenses($request)
    {
        return new \WP_REST_Response('getLicenses', 200);
    }

    public function getLicensesPermissionCheck($request)
    {
        return true;
    }

    public function postLicenses($request)
    {
        return new \WP_REST_Response('postLicenses', 200);
    }

    public function postLicensesPermissionCheck($request)
    {
        return true;
    }

    public function getLicense($request)
    {
        return new \WP_REST_Response('getLicense', 200);
    }

    public function getLicensePermissionsCheck($request)
    {
        return true;
    }

    public function postLicense($request)
    {
        return new \WP_REST_Response('postLicense', 200);
    }

    public function postLicensePermissionsCheck($request)
    {
        return true;
    }

}