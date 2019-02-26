<?php

namespace LicenseManagerForWooCommerce\API\v1;

use \LicenseManagerForWooCommerce\Logger;

defined('ABSPATH') || exit;

/**
 * Create the Generator endpoint.
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class Generators extends \WP_REST_Controller
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
    protected $base = 'generators';

    /**
     * Register all the needed routes for this resource.
     */
    public function register_routes()
    {
        // GET generator
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'getGenerators'),
                    'permission_callback' => array($this, 'getGeneratorsPermissionCheck'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // POST generator
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'postGenerators'),
                    'permission_callback' => array($this, 'postGeneratorsPermissionCheck'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // GET generator/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'getGenerator'),
                    'permission_callback' => array($this, 'getGeneratorPermissionsCheck'),
                    'args'                => array(
                        'key' => array(
                            'description' => __('Generator key ID.', 'lmfwc'),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // POST generator/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key_id>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'postGenerator'),
                    'permission_callback' => array($this, 'postGeneratorPermissionsCheck'),
                    'args'                => array(
                        'key_id' => array(
                            'description' => __('Generator key ID.', 'lmfwc'),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
    }

    public function getGenerators($request)
    {
        return new \WP_REST_Response('getGenerators', 200);
    }

    public function getGeneratorsPermissionCheck($request)
    {
        return true;
    }

    public function postGenerators($request)
    {
        return new \WP_REST_Response('postGenerators', 200);
    }

    public function postGeneratorsPermissionCheck($request)
    {
        return true;
    }

    public function getGenerator($request)
    {
        return new \WP_REST_Response('getGenerator', 200);
    }

    public function getGeneratorPermissionsCheck($request)
    {
        return true;
    }

    public function postGenerator($request)
    {
        return new \WP_REST_Response('postGenerator', 200);
    }

    public function postGeneratorPermissionsCheck($request)
    {
        return true;
    }

}