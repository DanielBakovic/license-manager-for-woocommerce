<?php

namespace LicenseManagerForWooCommerce\API;

defined('ABSPATH') || exit;

/**
 * Setup all the API endpoints
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class Setup
{
    public $controller;

    /**
     * Setup class constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        // REST API was included starting WordPress 4.4.
        if (!class_exists('\WP_REST_Server')) {
            return;
        }

        // Init REST API routes.
        add_action('rest_api_init', array($this, 'registerRoutes'), 10);
    }

    public function registerRoutes()
    {
        $controllers = array(
            // REST API v1 controllers.
            '\LicenseManagerForWooCommerce\API\v1\Licenses',
            '\LicenseManagerForWooCommerce\API\v1\Generators'
        );

        foreach ($controllers as $controller) {
            $this->$controller = new $controller();
            $this->$controller->register_routes();
        }
    }
}