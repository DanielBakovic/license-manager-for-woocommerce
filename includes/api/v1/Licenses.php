<?php

namespace LicenseManagerForWooCommerce\API\v1;

use \LicenseManagerForWooCommerce\Abstracts\RestController as LMFWC_REST_Controller;
use \LicenseManagerForWooCommerce\Enums\LicenseSource as LicenseSourceEnum;
use \LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;

defined('ABSPATH') || exit;

/**
 * Create the License endpoint.
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class Licenses extends LMFWC_REST_Controller
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
            $this->namespace, '/' . $this->base . '/(?P<license_key_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'getLicense'),
                    'args'     => array(
                        'license_key_id' => array(
                            'description' => 'License Key ID',
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
            $this->namespace, '/' . $this->base . '/(?P<license_key_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'updateLicense'),
                    'args'     => array(
                        'license_key_id' => array(
                            'description' => 'License Key ID',
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );
    }

    /**
     * Callback for the GET licenses route. Retrieves all license keys from the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getLicenses(\WP_REST_Request $request)
    {
        try {
            $result = apply_filters('lmfwc_get_license_keys', null);
        } catch (\Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$result) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'No License Keys available',
                array('status' => 404)
            );
        }

        foreach ($result as $license) {
            // Remove the hash and decrypt the license key
            unset($license->hash);
            $license->license_key = apply_filters('lmfwc_decrypt', $license->license_key);
        }

        return $this->response(true, $result, 200);
    }

    /**
     * Callback for the GET licenses/{id} route. Retrieves a single license key from the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getLicense(\WP_REST_Request $request)
    {
        $license_key_id = absint($request->get_param('license_key_id'));

        if (!$license_key_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'License Key ID is invalid.',
                array('status' => 404)
            );
        }

        try {
            $result = apply_filters('lmfwc_get_license_key', $license_key_id);
        } catch (Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$result) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key with ID: %d could not be found.',
                    $license_key_id
                ),
                array('status' => 404)
            );
        }

        // Remove the hash and decrypt the license key
        unset($result['hash']);
        $result['license_key'] = apply_filters('lmfwc_decrypt', $result['license_key']);

        return $this->response(true, $result, 200);
    }

    /**
     * Callback for the POST licenses route. Creates a new license key in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function createLicense(\WP_REST_Request $request)
    {
        $body = $request->get_params();

        $product_id  = isset($body['product_id'])  ? absint($body['product_id'])               : null;
        $license_key = isset($body['license_key']) ? sanitize_text_field($body['license_key']) : null;
        $valid_for   = isset($body['valid_for'])   ? absint($body['valid_for'])                : null;
        $valid_for   = $valid_for                  ? $valid_for                                : null;
        $status_enum = isset($body['status'])      ? sanitize_text_field($body['status'])      : null;
        $status      = null;

        if ($product_id) {
            try {
                $product = new \WC_Product($product_id);
            } catch (\Exception $e) {
                return new \WP_Error(
                    'lmfwc_rest_data_error',
                    $e->getMessage(),
                    array('status' => 404)
                );
            }
        }
        if (!$license_key) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'License Key is invalid.',
                array('status' => 404)
            );
        }
        if ($status_enum && !in_array($status_enum, LicenseStatusEnum::$enum_array)) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'License Key status is invalid',
                array('status' => 404)
            );
        } else {
            $status = LicenseStatusEnum::$values[$status_enum];
        }

        $created_by_user = apply_filters(
            'lmfwc_get_user_data_by_consumer_key',
            $_SERVER['PHP_AUTH_USER']
        );

        try {
            $license_key_id = apply_filters(
                'lmfwc_insert_license_key',
                null,
                $product_id,
                $license_key,
                $valid_for,
                LicenseSourceEnum::API,
                $status,
                $created_by_user->user_id
            );
        } catch (\Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$license_key_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'The license key could not be added to the database.',
                array('status' => 404)
            );
        }

        try {
            $license_key = apply_filters('lmfwc_get_license_key', $license_key_id);
        } catch (Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$license_key) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'The newly added license key could not be retrieved from the database.',
                array('status' => 404)
            );
        }

        // Remove the hash and decrypt the license key
        unset($license_key['hash']);
        $license_key['license_key'] = apply_filters(
            'lmfwc_decrypt',
            $license_key['license_key']
        );

        return $this->response(true, $license_key, 200);
    }

    /**
     * Callback for the PUT licenses/{id} route. Updates an existing license key in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function updateLicense(\WP_REST_Request $request)
    {
        // init
        $license_key_id = null;
        $body           = null;
        $status         = null;

        // Set and sanitize the basic parameters to be used.
        if ($request->get_param('license_key_id')) {
            $license_key_id = absint($request->get_param('license_key_id'));
        }
        if ($this->isJson($request->get_body())) {
            $body = json_decode($request->get_body());
        }

        // Validate basic parameters
        if (!$license_key_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'License Key ID is invalid.',
                array('status' => 404)
            );
        }
        if (!$body) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'No parameters were provided.',
                array('status' => 404)
            );
        }

        // Set and sanitize the other parameters to be used
        if (property_exists($body, 'order_id')) {
            if (is_null($body->order_id)) {
                $order_id = null;
            } else {
                $order_id = absint($body->order_id);
            }
        } else {
            $order_id = self::UNDEFINED;
        }

        if (property_exists($body, 'product_id')) {
            if (is_null($body->product_id)) {
                $product_id = null;
            } else {
                $product_id = absint($body->product_id);
            }
        } else {
            $product_id = self::UNDEFINED;
        }

        if (property_exists($body, 'license_key')) {
            $license_key = sanitize_text_field($body->license_key);
        } else {
            $license_key = self::UNDEFINED;
        }

        if (property_exists($body, 'valid_for')) {
            if (is_null($body->valid_for)) {
                $valid_for = null;
            } else {
                $valid_for = absint($body->valid_for);
            }
        } else {
            $valid_for = self::UNDEFINED;
        }

        if (property_exists($body, 'status')) {
            if (is_null($body->status)) {
                $status_enum = null;
                $status = null;
            } else {
                $status_enum = sanitize_text_field($body->status);
                $status = LicenseStatusEnum::$values[$status_enum];
            }
        } else {
            $status_enum = self::UNDEFINED;
        }

        // Throw errors if anything crucial is missing
        if ($order_id == self::UNDEFINED
            && $product_id == self::UNDEFINED
            && $license_key == self::UNDEFINED
            && $valid_for == self::UNDEFINED
            && $status_enum == self::UNDEFINED
        ) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'No parameters were provided.',
                array('status' => 404)
            );
        }
        if (!$status) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'License Key status is invalid.',
                array('status' => 404)
            );
        }
        if ($status_enum
            && $status_enum != self::UNDEFINED
            && !in_array($status_enum, LicenseStatusEnum::$enum_array)
        ) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'Status enumerator is invalid.',
                array('status' => 404)
            );
        }
        if ($order_id && $order_id != self::UNDEFINED) {
            try {
                $order = new \WC_Order($order_id);
            } catch (\Exception $e) {
                return new \WP_Error(
                    'lmfwc_rest_data_error',
                    $e->getMessage(),
                    array('status' => 404)
                );
            }
            if ($order->get_status() == 'completed') {
                return new \WP_Error(
                    'lmfwc_rest_data_error',
                    sprintf('WooCommerce Order with ID: %d has already been completed.', $order_id),
                    array('status' => 404)
                );
            }
        }
        if ($product_id && $product_id != self::UNDEFINED) {
            try {
                $product = new \WC_Product($product_id);
            } catch (\Exception $e) {
                return new \WP_Error(
                    'lmfwc_rest_data_error',
                    $e->getMessage(),
                    array('status' => 404)
                );
            }
        }

        $license = apply_filters('lmfwc_get_license_key', $license_key_id);

        if (!$license) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key with ID: %d could not be found.',
                    $license_key_id
                ),
                array('status' => 404)
            );
        }

        if (intval($license['status']) === LicenseStatusEnum::SOLD) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key with ID: %d has already been sold.',
                    $license_key_id
                ),
                array('status' => 404)
            );
        }

        if (intval($license['status']) === LicenseStatusEnum::DELIVERED) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key with ID: %d has already been delivered.',
                    $license_key_id
                ),
                array('status' => 404)
            );
        }

        $updated_by_user = apply_filters(
            'lmfwc_get_user_data_by_consumer_key',
            $_SERVER['PHP_AUTH_USER']
        );

        try {
            $updated_license_key = apply_filters(
                'lmfwc_update_selective_license_key',
                $license_key_id,
                $order_id,
                $product_id,
                $license_key,
                $valid_for,
                $status,
                $updated_by_user->user_id
            );
        } catch (\Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        // Remove the hash and decrypt the license key
        unset($updated_license_key['hash']);
        $updated_license_key['license_key'] = apply_filters(
            'lmfwc_decrypt',
            $updated_license_key['license_key']
        );

        return $this->response(true, $updated_license_key, 200);
    }
}