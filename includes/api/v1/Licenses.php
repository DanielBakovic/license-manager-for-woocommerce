<?php

namespace LicenseManagerForWooCommerce\API\v1;

use \LicenseManagerForWooCommerce\Logger;
use \LicenseManagerForWooCommerce\Enums\LicenseStatusEnum;

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
        /*
         * GET license
         * 
         * Retrieves all the available licenses from the database.
         */
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'  => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'getLicenses'),
                )
            )
        );

        /*
         * GET license/{id}
         * 
         * Retrieves a single licenses from the database.
         */
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'getLicense'),
                    'args'     => array(
                        'key_id' => array(
                            'description' => __('License key ID.', 'lmfwc'),
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );

        /*
         * POST license
         * 
         * Creates a new license in the database
         */
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'  => \WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'createLicense'),
                )
            )
        );

        // PUT license/{id}
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<key_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'updateLicense'),
                    'args'     => array(
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
        $body = $request->get_params();

        // Validate the product_id parameter
        if (isset($body['product_id']) && is_numeric($body['product_id'])) {

            $id = absint($body['product_id']);

            if (!$this->validateProductId($id)) {
                return new \WP_Error(
                    'lmfwc_rest_data_error',
                    sprintf(__('The WooCommerce product with the ID: %d could not be found.', 'lmfwc'), $id),
                    array('status' => 404)
                );
            }
        }

        // Validate the license_key parameter
        if (!isset($body['license_key'])) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The license key is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }

        // Validate the valid_for parameter
        if (isset($body['valid_for']) && is_numeric($body['valid_for'])) {
           $valid_for = absint($body['valid_for']);
        } else {
            $valid_for = null;
        }

        // Validate the status parameter
        if (isset($body['status']) && in_array(sanitize_text_field($body['status']), array('active', 'inactive'))) {
            $status = LicenseStatusEnum::$values[sanitize_text_field($body['status'])];
        } else {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The status is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }

        return new \WP_REST_Response('createLicense', 200);
    }

    public function updateLicense(\WP_REST_Request $request)
    {
        return new \WP_REST_Response('updateLicense', 200);
    }

    // Validation functions.

    protected function validateProductId($product_id)
    {
        try {
            $product = new \WC_Product($product_id);
        } catch (\Exception $e) {
            return false;
        }

        return new \WP_REST_Response($product, 200);

        if ($product->exists()) {
            return true;
        }

        return false;
    }

}