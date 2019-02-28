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
                )
            )
        );

        // POST license
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'createLicenses'),
                )
            )
        );

        // GET license/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key_id>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'getLicense'),
                    'args'                => array(
                        'key_id' => array(
                            'description' => __('License key ID.', 'lmfwc'),
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );

        // POST license/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key_id>[\w-]+)', array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'createLicense'),
                    'args'                => array(
                        'key_id' => array(
                            'description' => __('License key ID.', 'lmfwc'),
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );
    }
    public function getLicenses(\WP_REST_Request $request)
    {
        $result = apply_filters('lmfwc_get_licenses', null);

        if (!$result) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('No license keys available.', 'lmfwc'),
                array('status' => 404)
            );
        }

        return new \WP_REST_Response($result, 200);
    }

    public function createLicenses(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('', 200);
    }

    public function getLicense(\WP_REST_Request $request)
    {
        $id = intval($request->get_param('key_id'));
        $result = apply_filters('lmfwc_get_license', $id);

        if (!$result) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The license key with the ID: %d could not be found.', 'lmfwc'), $id),
                array('status' => 404)
            );
        }

        return new \WP_REST_Response($result, 200);
    }

    public function createLicense(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('createLicense', 200);
    }

}