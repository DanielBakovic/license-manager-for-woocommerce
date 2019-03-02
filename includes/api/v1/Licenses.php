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
         * GET licenses
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
         * GET licenses/{id}
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
         * POST licenses
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

        /*
         * PUT licenses/{id}
         * 
         * Updates an already existing license in the database
         */
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

    /**
     * Callback for the GET licenses route. Retrieves all the license keys from the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
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

    /**
     * Callback for the GET licenses/{id} route. Retrieves a single license key from the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
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

    /**
     * Callback for the POST licenses route. Create a new license key in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function createLicense(\WP_REST_Request $request)
    {
        $body = $request->get_params();

        // Validate the product_id parameter
        if (isset($body['product_id']) && is_numeric($body['product_id'])) {

            $product_id = absint($body['product_id']);

            if (!$this->validateProductId($product_id)) {
                return new \WP_Error(
                    'lmfwc_rest_data_error',
                    sprintf(__('The WooCommerce Product with the ID: %d could not be found.', 'lmfwc'), $product_id),
                    array('status' => 404)
                );
            }
        }

        // Validate the license_key parameter
        if (!isset($body['license_key'])) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The License Key is missing from the request.', 'lmfwc'),
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

        $license_key_id = apply_filters(
            'lmfwc_insert_license_key_from_api',
            $product_id,
            sanitize_text_field($body['license_key']),
            $valid_for,
            $status
        );

        if (!$license_key_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The license key could not be added to the database.', 'lmfwc'),
                array('status' => 404)
            );
        }

        if (!$license_key = apply_filters('lmfwc_get_license', absint($license_key_id))) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The newly added license key could not be retrieved from the database.', 'lmfwc'),
                array('status' => 404)
            );
        }

        return new \WP_REST_Response($license_key, 200);
    }

    /**
     * Callback for the PUT licenses/{id} route. Updates an existing license key in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function updateLicense(\WP_REST_Request $request)
    {
        $body = $request->get_params();
        $key_id = null;
        $order_id = null;
        $product_id = null;
        $license_key = null;
        $valid_for = null;
        $status = null;
        $status_enum = null;

        if (!isset($body['order_id']) &&
            !isset($body['product_id']) &&
            !isset($body['license_key']) &&
            !isset($body['valid_for']) &&
            !isset($body['status'])
        ) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('No parameters were provided in the request. Please provide at least one parameter you wish to alter.', 'lmfwc'),
                array('status' => 404)
            );
        }

        if (!isset($body['key_id'])) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The License Key ID is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }

        if (!is_numeric($body['key_id'])) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The License Key ID is not a number.', 'lmfwc'),
                array('status' => 404)
            );
        }

        $key_id     = absint($body['key_id']);
        $license    = apply_filters('lmfwc_get_license', $key_id);
        $order_id   = isset($body['order_id'])   ? absint($body['order_id'])   : null;
        $product_id = isset($body['product_id']) ? absint($body['product_id']) : null;

        if (!$license) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The License Key with the ID: %d could not be found.', 'lmfwc'), $key_id),
                array('status' => 404)
            );
        }

        if (intval($license['status']) === LicenseStatusEnum::SOLD) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The License Key with the ID: %d has already been sold and cannot be modified any further.', 'lmfwc'), $key_id),
                array('status' => 404)
            );
        }

        if (intval($license['status']) === LicenseStatusEnum::USED) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The License Key with the ID: %d has already been used and cannot be modified any further.', 'lmfwc'), $key_id),
                array('status' => 404)
            );
        }

        if (intval($license['status']) === LicenseStatusEnum::DELIVERED) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The License Key with the ID: %d has already been delivered and cannot be modified any further.', 'lmfwc'), $key_id),
                array('status' => 404)
            );
        }

        if (!$this->validateOrderId($order_id)) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The WooCommerce Order with the ID: %d could not be found.', 'lmfwc'), $order_id),
                array('status' => 404)
            );
        }

        $order = new \WC_Order($order_id);

        if ($order->get_status() == 'completed') {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The WooCommerce Order with the ID: %d is already completed. No further changes to the associated License Key(s) are possible.', 'lmfwc'), $order_id),
                array('status' => 404)
            );
        }

        if (!$this->validateProductId($product_id)) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(__('The WooCommerce Product with the ID: %d could not be found.', 'lmfwc'), $product_id),
                array('status' => 404)
            );
        }

        if (isset($body['license_key'])) {
            $license_key = sanitize_text_field($body['license_key']);
        }

        if (isset($body['valid_for']) && is_numeric($body['valid_for'])) {
            $valid_for = absint($body['valid_for']);
        }

        if (isset($body['status'])) {
            $status_enum = sanitize_text_field($body['status']);

            if (!in_array($status_enum, array('sold', 'delivered', 'active', 'inactive', 'used'))) {
                return new \WP_Error(
                    'lmfwc_rest_data_error',
                    __('The status must be a valid enumerator value.', 'lmfwc'),
                    array('status' => 404)
                );
            }

            $status = LicenseStatusEnum::$values[$status_enum];
        }

        $updated_license_key = apply_filters(
            'lmfwc_update_license_key',
            $key_id,
            $order_id,
            $product_id,
            $license_key,
            $valid_for,
            $status
        );

        return new \WP_REST_Response($updated_license_key, 200);
    }

    /**
     * Validate a WooCommerce Product ID supplied by the request
     * 
     * @param  integer $product_id
     * @return boolean
     */
    protected function validateProductId($product_id)
    {
        try {
            $product = new \WC_Product($product_id);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Validate a WooCommerce Order ID supplied by the request
     * 
     * @param  integer $order_id
     * @return boolean
     */
    protected function validateOrderId($order_id)
    {
        try {
            $order = new \WC_Order($order_id);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

}