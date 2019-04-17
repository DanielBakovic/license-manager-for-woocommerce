<?php

namespace LicenseManagerForWooCommerce\API\v1;

use \LicenseManagerForWooCommerce\Abstracts\RestController as LMFWC_REST_Controller;

defined('ABSPATH') || exit;

/**
 * Create the Generator endpoint.
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class Generators extends LMFWC_REST_Controller
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
        /*
         * GET generators
         * 
         * Retrieves all the available generators from the database.
         */
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'  => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'getGenerators'),
                )
            )
        );

        /*
         * GET generators/{id}
         * 
         * Retrieves a single generator from the database.
         */
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<generator_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'getGenerator'),
                    'args'     => array(
                        'generator_id' => array(
                            'description' => 'Generator ID',
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );

        /*
         * POST generators
         * 
         * Creates a new generator in the database
         */
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'  => \WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'createGenerator'),
                )
            )
        );

        /*
         * PUT generators/{id}
         * 
         * Updates an already existing generator in the database
         */
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<generator_id>[\w-]+)', array(
                array(
                    'methods'  => \WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'updateGenerator'),
                    'args'     => array(
                        'generator_id' => array(
                            'description' => 'Generator ID',
                            'type'        => 'integer',
                        ),
                    ),
                )
            )
        );
    }

    /**
     * Callback for the GET generators route. Retrieves all generators from the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getGenerators(\WP_REST_Request $request)
    {
        try {
            $result = apply_filters('lmfwc_get_generators', null);
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
                'No Generators available',
                array('status' => 404)
            );
        }

        return $this->response(true, $result, 200);
    }

    /**
     * Callback for the GET generators/{id} route. Retrieves a single generator from the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getGenerator(\WP_REST_Request $request)
    {
        $generator_id = absint($request->get_param('generator_id'));

        if (!$generator_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'Generator ID is invalid.',
                array('status' => 404)
            );
        }

        try {
            $result = apply_filters('lmfwc_get_generator', $generator_id);
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
                    'Generator with ID: %d could not be found.',
                    $generator_id
                ),
                array('status' => 404)
            );
        }

        return $this->response(true, $result, 200);
    }

    /**
     * Callback for the POST generators route. Creates a new generator in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function createGenerator(\WP_REST_Request $request)
    {
        $body = $request->get_params();

        $name                = isset($body['name'])                ? sanitize_text_field($body['name'])      : null;
        $charset             = isset($body['charset'])             ? sanitize_text_field($body['charset'])   : null;
        $chunks              = isset($body['chunks'])              ? absint($body['chunks'])                 : null;
        $chunk_length        = isset($body['chunk_length'])        ? absint($body['chunk_length'])           : null;
        $times_activated_max = isset($body['times_activated_max']) ? absint($body['times_activated_max'])    : null;
        $separator           = isset($body['separator'])           ? sanitize_text_field($body['separator']) : null;
        $prefix              = isset($body['prefix'])              ? sanitize_text_field($body['prefix'])    : null;
        $suffix              = isset($body['suffix'])              ? sanitize_text_field($body['suffix'])    : null;
        $expires_in          = isset($body['expires_in'])          ? absint($body['expires_in'])             : null;

        if (!$name) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator name is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }
        if (!$charset) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator charset is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }
        if (!$chunks) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator chunks is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }
        if (!$chunk_length) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator chunk length is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }

        try {
            $generator_id = apply_filters(
                'lmfwc_insert_generator',
                $name,
                $charset,
                $chunks,
                $chunk_length,
                $times_activated_max,
                $separator,
                $prefix,
                $suffix,
                $expires_in
            );
        } catch (\Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }


        if (!$generator_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator could not be added to the database.', 'lmfwc'),
                array('status' => 404)
            );
        }

        try {
            $generator = apply_filters('lmfwc_get_generator', $generator_id);
        } catch (Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$generator) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The newly added Generator could not be retrieved from the database.', 'lmfwc'),
                array('status' => 404)
            );
        }

        return $this->response(true, $generator, 200);
    }

    /**
     * Callback for the PUT generators/{id} route. Updates an existing generator in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function updateGenerator(\WP_REST_Request $request)
    {
        // init
        $generator_id = null;
        $body         = null;

        // Set and sanitize the basic parameters to be used.
        if ($request->get_param('generator_id')) {
            $generator_id = absint($request->get_param('generator_id'));
        }
        if ($this->isJson($request->get_body())) {
            $body = json_decode($request->get_body());
        }

        // Validate basic parameters
        if (!$generator_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator ID is missing from the request.', 'lmfwc'),
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
        if (property_exists($body, 'name')) {
            $name = sanitize_text_field($body->name);
        } else {
            $name = self::UNDEFINED;
        }

        if (property_exists($body, 'charset')) {
            $charset = sanitize_text_field($body->charset);
        } else {
            $charset = self::UNDEFINED;
        }

        if (property_exists($body, 'chunks')) {
            $chunks = absint($body->chunks);
        } else {
            $chunks = self::UNDEFINED;
        }

        if (property_exists($body, 'chunk_length')) {
            $chunk_length = absint($body->chunk_length);
        } else {
            $chunk_length = self::UNDEFINED;
        }

        if (property_exists($body, 'times_activated_max')) {
            if (is_null($body->times_activated_max)) {
                $times_activated_max = null;
            } else {
                $times_activated_max = absint($body->times_activated_max);
            }
        } else {
            $times_activated_max = self::UNDEFINED;
        }

        if (property_exists($body, 'separator')) {
            if (is_null($body->separator)) {
                $separator = null;
            } else {
                $separator = sanitize_text_field($body->separator);
            }
        } else {
            $separator = self::UNDEFINED;
        }

        if (property_exists($body, 'prefix')) {
            if (is_null($body->prefix)) {
                $prefix = null;
            } else {
                $prefix = sanitize_text_field($body->prefix);
            }
        } else {
            $prefix = self::UNDEFINED;
        }

        if (property_exists($body, 'suffix')) {
            if (is_null($body->suffix)) {
                $suffix = null;
            } else {
                $suffix = sanitize_text_field($body->suffix);
            }
        } else {
            $suffix = self::UNDEFINED;
        }

        if (property_exists($body, 'expires_in')) {
            if (is_null($body->expires_in)) {
                $expires_in = null;
            } else {
                $expires_in = sanitize_text_field($body->expires_in);
            }
        } else {
            $expires_in = self::UNDEFINED;
        }

        // Throw errors if anything crucial is missing
        if ($name == self::UNDEFINED
            && $charset == self::UNDEFINED
            && $chunks == self::UNDEFINED
            && $chunk_length == self::UNDEFINED
            && $separator == self::UNDEFINED
            && $prefix == self::UNDEFINED
            && $suffix == self::UNDEFINED
            && $expires_in == self::UNDEFINED
        ) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'No parameters were provided.',
                array('status' => 404)
            );
        }
        if (!$name) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'Generator name is invalid.',
                array('status' => 404)
            );
        }
        if (!$charset) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'Generator character map is invalid.',
                array('status' => 404)
            );
        }
        if (!$chunks) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'Generator chunks is invalid.',
                array('status' => 404)
            );
        }
        if (!$chunk_length) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'Generator chunk length is invalid.',
                array('status' => 404)
            );
        }
        if ($name && $name != self::UNDEFINED && strlen($name) > 255) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                'Generator name cannot be longer than 255 characters.',
                array('status' => 404)
            );
        }

        try {
            $updated_generator = apply_filters(
                'lmfwc_update_selective_generator',
                $generator_id,
                $name,
                $charset,
                $chunks,
                $chunk_length,
                $times_activated_max,
                $separator,
                $prefix,
                $suffix,
                $expires_in
            );
        } catch (\Exception $e) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        return $this->response(true, $updated_generator, 200);
    }
}