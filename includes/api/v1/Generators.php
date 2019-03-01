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
                    'methods'  => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'getGenerators'),
                )
            )
        );

        // POST generator
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'  => \WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'createGenerators'),
                )
            )
        );

        // GET generator/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<generator_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'getGenerator'),
                    'args'     => array(
                        'generator_id' => array(
                            'description' => __('Generator ID.', 'lmfwc'),
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );

        // POST generator/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<generator_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'createGenerator'),
                    'args'     => array(
                        'generator_id' => array(
                            'description' => __('Generator ID.', 'lmfwc'),
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );
    }

    public function getGenerators(\WP_REST_Request $request)
    {
        $result = apply_filters('lmfwc_get_generators', null);

        if (!$result) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('No generators available.', 'lmfwc'),
                array('status' => 404)
            );
        }

        return new \WP_REST_Response($result, 200);
    }

    public function createGenerators(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('createGenerators', 200);
    }

    public function getGenerator(\WP_REST_Request $request)
    {
        $id = intval($request->get_param('generator_id'));
        $result = apply_filters('lmfwc_get_generator', $id);

        if (!$result) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The generator with the ID: %d could not be found.', 'lmfwc'), $id),
                array('status' => 404)
            );
        }

        return new \WP_REST_Response($result, 200);
    }

    public function createGenerator(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('createGenerator', 200);
    }

}