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
                    'permission_callback' => array($this, 'getItemPermissionCheck'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // POST generator
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'createGenerators'),
                    'permission_callback' => array($this, 'createItemPermissionCheck'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // GET generator/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<generator_id>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'getGenerator'),
                    'permission_callback' => array($this, 'getItemPermissionCheck'),
                    'args'                => array(
                        'generator_id' => array(
                            'description' => __('Generator ID.', 'lmfwc'),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        // POST generator/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<generator_id>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'createGenerator'),
                    'permission_callback' => array($this, 'createItemPermissionCheck'),
                    'args'                => array(
                        'generator_id' => array(
                            'description' => __('Generator ID.', 'lmfwc'),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
    }

    public function getGenerators(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('getGenerators', 200);
    }

    public function createGenerators(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('createGenerators', 200);
    }

    public function getGenerator(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('getGenerator', 200);
    }

    public function createGenerator(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('createGenerator', 200);
    }

    public function getItemPermissionCheck(\WP_REST_Request $request)
    {
        return true;
    }

    public function createItemPermissionCheck(\WP_REST_Request $request)
    {
        return true;
    }

}