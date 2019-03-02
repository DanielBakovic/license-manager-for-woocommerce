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
                            'description' => __('Generator ID.', 'lmfwc'),
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
                            'description' => __('Generator ID.', 'lmfwc'),
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

    /**
     * Callback for the GET generators/{id} route. Retrieves a single generator from the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
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

    /**
     * Callback for the POST generators route. Creates a new generator in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function createGenerator(\WP_REST_Request $request)
    {
        $body = $request->get_params();

        $name         = isset($body['name'])         ? sanitize_text_field($body['name'])      : null;
        $charset      = isset($body['charset'])      ? sanitize_text_field($body['charset'])   : null;
        $chunks       = isset($body['chunks'])       ? absint($body['chunks'])                 : null;
        $chunk_length = isset($body['chunk_length']) ? absint($body['chunk_length'])           : null;
        $separator    = isset($body['separator'])    ? sanitize_text_field($body['separator']) : null;
        $prefix       = isset($body['prefix'])       ? sanitize_text_field($body['prefix'])    : null;
        $suffix       = isset($body['suffix'])       ? sanitize_text_field($body['suffix'])    : null;
        $expires_in   = isset($body['expires_in'])   ? absint($body['expires_in'])             : null;

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

        $generator_id = apply_filters('lmfwc_insert_generator', array(
            'name'         => $name,
            'charset'      => $charset,
            'chunks'       => $chunks,
            'chunk_length' => $chunk_length,
            'separator'    => $separator,
            'prefix'       => $prefix,
            'suffix'       => $suffix,
            'expires_in'   => $expires_in
        ));

        if (!$generator_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator could not be added to the database.', 'lmfwc'),
                array('status' => 404)
            );
        }

        $generator = apply_filters('lmfwc_get_generator', $generator_id);

        if (!$generator) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The newly added Generator could not be retrieved from the database.', 'lmfwc'),
                array('status' => 404)
            );
        }

        return new \WP_REST_Response($generator, 200);
    }

    /**
     * Callback for the PUT generators/{id} route. Updates an existing generator in the database.
     * 
     * @param  WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function updateGenerator(\WP_REST_Request $request)
    {
        $body = $request->get_params();

        $generator_id = isset($body['generator_id']) ? sanitize_text_field($body['generator_id']) : null;
        $name         = isset($body['name'])         ? sanitize_text_field($body['name'])         : null;
        $charset      = isset($body['charset'])      ? sanitize_text_field($body['charset'])      : null;
        $chunks       = isset($body['chunks'])       ? absint($body['chunks'])                    : null;
        $chunk_length = isset($body['chunk_length']) ? absint($body['chunk_length'])              : null;
        $separator    = isset($body['separator'])    ? sanitize_text_field($body['separator'])    : null;
        $prefix       = isset($body['prefix'])       ? sanitize_text_field($body['prefix'])       : null;
        $suffix       = isset($body['suffix'])       ? sanitize_text_field($body['suffix'])       : null;
        $expires_in   = isset($body['expires_in'])   ? absint($body['expires_in'])                : null;

        if (!isset($name) &&
            !isset($charset) &&
            !isset($chunks) &&
            !isset($chunk_length) &&
            !isset($separator) &&
            !isset($prefix) &&
            !isset($suffix) &&
            !isset($expires_in)
        ) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('No parameters were provided in the request. Please provide at least one parameter you wish to alter.', 'lmfwc'),
                array('status' => 404)
            );
        }

        if (!$generator_id) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator ID is missing from the request.', 'lmfwc'),
                array('status' => 404)
            );
        }

        if ($name && (strlen($name) > 255)) {
            return new \WP_Error(
                'lmfwc_rest_data_error',
                __('The Generator Name can not be longer than 255 characters.', 'lmfwc'),
                array('status' => 404)
            );
        }

        $updated_generator = apply_filters(
            'lmfwc_update_generator_from_api',
            $generator_id,
            $name,
            $charset,
            $chunks,
            $chunk_length,
            $separator,
            $prefix,
            $suffix,
            $expires_in
        );

        return new \WP_REST_Response($updated_generator, 200);
    }

}