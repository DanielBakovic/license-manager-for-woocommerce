<?php

namespace LicenseManagerForWooCommerce\API\v1;

use Exception;
use LicenseManagerForWooCommerce\Abstracts\RestController as LMFWC_REST_Controller;
use LicenseManagerForWooCommerce\Enums\LicenseSource;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

class Licenses extends LMFWC_REST_Controller
{
    /**
     * @var string
     */
    protected $namespace = 'lmfwc/v1';

    /**
     * @var string
     */
    protected $rest_base = '/licenses';

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * Licenses constructor.
     */
    public function __construct()
    {
        $this->settings = (array)get_option('lmfwc_settings');
    }

    /**
     * Register all the needed routes for this resource.
     */
    public function register_routes()
    {
        /**
         * GET licenses
         * 
         * Retrieves all the available licenses from the database.
         */
        register_rest_route(
            $this->namespace, $this->rest_base, array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array($this, 'getLicenses'),
                )
            )
        );

        /**
         * GET licenses/{license_key OR id}
         * 
         * Retrieves a single licenses from the database, either by the license key string or its ID.
         */
        register_rest_route(
            $this->namespace, $this->rest_base . '/(?P<license_key>[\w-]+)', array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array($this, 'getLicense'),
                    'args'     => array(
                        'license_key' => array(
                            'description' => 'License Key',
                            'type'        => 'string',
                        )
                    )
                )
            )
        );

        /**
         * POST licenses
         * 
         * Creates a new license in the database
         */
        register_rest_route(
            $this->namespace, $this->rest_base, array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'createLicense'),
                )
            )
        );

        /**
         * PUT licenses/{license_key OR id}
         * 
         * Updates an already existing license in the database, either by the license key string or its ID.
         */
        register_rest_route(
            $this->namespace, $this->rest_base . '/(?P<license_key>[\w-]+)', array(
                array(
                    'methods'  => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'updateLicense'),
                    'args'     => array(
                        'license_key' => array(
                            'description' => 'License Key',
                            'type'        => 'string',
                        ),
                    ),
                )
            )
        );

        /**
         * PUT licenses/activate/{license_key OR id}
         * 
         * Activates a license key, either by the license key string or its ID.
         */
        register_rest_route(
            $this->namespace, $this->rest_base . '/activate/(?P<license_key>[\w-]+)', array(
                array(
                    'methods'  => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'activateLicense'),
                    'args'     => array(
                        'license_key' => array(
                            'description' => 'License Key',
                            'type'        => 'string',
                        ),
                    ),
                )
            )
        );

        /**
         * PUT licenses/activate/{license_key OR id}
         * 
         * Activates a license key, either by the license key string or its ID.
         */
        register_rest_route(
            $this->namespace, $this->rest_base . '/validate/(?P<license_key>[\w-]+)', array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array($this, 'validateLicense'),
                    'args'     => array(
                        'license_key' => array(
                            'description' => 'License Key',
                            'type'        => 'string',
                        ),
                    ),
                )
            )
        );
    }

    /**
     * Callback for the GET licenses route. Retrieves all license keys from the database.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function getLicenses()
    {
        if (!$this->isRouteEnabled($this->settings, '000')) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'This route is disabled via the plugin settings.',
                array('status' => 404)
            );
        }

        try {
            /** @var LicenseResourceModel[] $licenses */
            $licenses = LicenseResourceRepository::instance()->findAll();
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$licenses) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'No License Keys available',
                array('status' => 404)
            );
        }

        $response = array();

        /** @var LicenseResourceModel $license */
        foreach ($licenses as $license) {
            $response[] = $this->getLicenseData($license);
        }

        return $this->response(true, $response, 200);
    }

    /**
     * Callback for the GET licenses/{license_key} route. Retrieves a single license key from the database.
     *
     * @param WP_REST_Request $request
     * 
     * @return WP_REST_Response|WP_Error
     */
    public function getLicense(WP_REST_Request $request)
    {
        if (!$this->isRouteEnabled($this->settings, '001')) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'This route is disabled via the plugin settings.',
                array('status' => 404)
            );
        }

        $licenseKey = sanitize_text_field($request->get_param('license_key'));

        if (!$licenseKey) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'The license key or license key ID is invalid.',
                array('status' => 404)
            );
        }

        try {
            if (is_numeric($licenseKey)) {
                /** @var LicenseResourceModel $license */
                $license = LicenseResourceRepository::instance()->findBy(
                    array(
                        'id' => $licenseKey
                    )
                );
            }

            else {
                /** @var LicenseResourceModel $license */
                $license = LicenseResourceRepository::instance()->findBy(
                    array(
                        'hash' => apply_filters('lmfwc_hash', $licenseKey)
                    )
                );
            }
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$license) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'The license key could not be found.',
                array('status' => 404)
            );
        }

        return $this->response(true, $this->getLicenseData($license), 200);
    }

    /**
     * Callback for the POST licenses route. Creates a new license key in the database.
     *
     * @param WP_REST_Request $request
     * 
     * @return WP_REST_Response|WP_Error
     */
    public function createLicense(WP_REST_Request $request)
    {
        if (!$this->isRouteEnabled($this->settings, '002')) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'This route is disabled via the plugin settings.',
                array('status' => 404)
            );
        }

        $body = $request->get_params();

        $productId         = isset($body['product_id'])          ? absint($body['product_id'])               : null;
        $licenseKey        = isset($body['license_key'])         ? sanitize_text_field($body['license_key']) : null;
        $validFor          = isset($body['valid_for'])           ? absint($body['valid_for'])                : null;
        $validFor          = $validFor                           ? $validFor                                 : null;
        $timesActivatedMax = isset($body['times_activated_max']) ? absint($body['times_activated_max'])      : null;
        $statusEnum        = isset($body['status'])              ? sanitize_text_field($body['status'])      : null;
        $status            = null;

        if (!$licenseKey) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'License Key is invalid.',
                array('status' => 404)
            );
        }

        if ($statusEnum && !in_array($statusEnum, LicenseStatus::$enumArray)) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'License Key status is invalid',
                array('status' => 404)
            );
        }

        else {
            $status = LicenseStatus::$values[$statusEnum];
        }

        try {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->insert(
                array(
                    'product_id'          => $productId,
                    'license_key'         => apply_filters('lmfwc_encrypt', $licenseKey),
                    'hash'                => apply_filters('lmfwc_hash', $licenseKey),
                    'valid_for'           => $validFor,
                    'source'              => LicenseSource::API,
                    'status'              => $status,
                    'times_activated_max' => $timesActivatedMax
                )
            );
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$license) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'The license key could not be added to the database.',
                array('status' => 404)
            );
        }

        return $this->response(true, $this->getLicenseData($license), 200);
    }

    /**
     * Callback for the PUT licenses/{license_key} route. Updates an existing license key in the database.
     *
     * @param WP_REST_Request $request
     * 
     * @return WP_REST_Response|WP_Error
     */
    public function updateLicense(WP_REST_Request $request)
    {
        if (!$this->isRouteEnabled($this->settings, '003')) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'This route is disabled via the plugin settings.',
                array('status' => 404)
            );
        }

        $body      = null;
        $urlParams = $request->get_url_params();

        if (!array_key_exists('license_key', $urlParams)) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'No license key was provided.',
                array('status' => 404)
            );
        }

        $licenseKey = sanitize_text_field($urlParams['license_key']);

        if (!$licenseKey) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'License Key invalid.',
                array('status' => 404)
            );
        }

        if ($this->isJson($request->get_body())) {
            $body = json_decode($request->get_body());
        }

        // Validate basic parameters
        if (!$body) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'No parameters were provided.',
                array('status' => 404)
            );
        }

        if (is_numeric($licenseKey)) {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->findBy(
                array(
                    'id' => $licenseKey
                )
            );
        }

        else {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->findBy(
                array(
                    'hash' => apply_filters('lmfwc_hash', $licenseKey)
                )
            );
        }

        if (!$license) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key: %s could not be found.',
                    $licenseKey
                ),
                array('status' => 404)
            );
        }

        $updateData = (array)$body;

        if (empty($updateData)) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'No parameters were provided.',
                array('status' => 404)
            );
        }

        if (array_key_exists('status', $updateData)) {
            $updateData['status'] = $this->getStatus($updateData['status']);
        }

        if (array_key_exists('hash', $updateData)) {
            unset($updateData['hash']);
        }

        if (array_key_exists('license_key', $updateData)) {
            $updateData['hash'] = apply_filters('lmfwc_hash', $updateData['license_key']);
            $updateData['license_key'] = apply_filters('lmfwc_encrypt', $updateData['license_key']);
        }

        /** @var LicenseResourceModel $updatedLicense */
        $updatedLicense = LicenseResourceRepository::instance()->update($license->getId(), $updateData);

        if (!$updatedLicense) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'The license key could not be updated.',
                array('status' => 404)
            );
        }

        return $this->response(true, $this->getLicenseData($updatedLicense), 200);
    }

    /**
     * Callback for the PUT licenses/activate/{license_key OR license_key_id} route. This will activate a license key (if possible)
     *
     * @param WP_REST_Request $request
     * 
     * @return WP_REST_Response|WP_Error
     */
    public function activateLicense(WP_REST_Request $request)
    {
        if (!$this->isRouteEnabled($this->settings, '004')) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'This route is disabled via the plugin settings.',
                array('status' => 404)
            );
        }

        $licenseKey = sanitize_text_field($request->get_param('license_key'));

        if (!$licenseKey) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'License key is invalid.',
                array('status' => 404)
            );
        }

        try {
            if (is_numeric($licenseKey)) {
                /** @var LicenseResourceModel $license */
                $license = LicenseResourceRepository::instance()->findBy(
                    array(
                        'id' => $licenseKey
                    )
                );
            }

            else {
                /** @var LicenseResourceModel $license */
                $license = LicenseResourceRepository::instance()->findBy(
                    array(
                        'hash' => apply_filters('lmfwc_hash', $licenseKey)
                    )
                );
            }
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$license) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key: %s could not be found.',
                    $licenseKey
                ),
                array('status' => 404)
            );
        }

        // Check if the license key can be activated
        $timesActivated    = absint($license->getTimesActivated());
        $timesActivatedMax = absint($license->getTimesActivatedMax());

        if (!$timesActivatedMax) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key: %s can not be activated (times_activated_max not set).',
                    $licenseKey
                ),
                array('status' => 404)
            );
        }

        if ($timesActivatedMax && ($timesActivated >= $timesActivatedMax)) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key: %s reached maximum activation count.',
                    $licenseKey
                ),
                array('status' => 404)
            );
        }

        // Activate the license key
        try {
            if (!$timesActivated) {
                $timesActivatedNew = 1;
            }

            else {
                $timesActivatedNew = intval($timesActivated) + 1;
            }

            /** @var LicenseResourceModel $updatedLicense */
            $updatedLicense = LicenseResourceRepository::instance()->update(
                $license->getId(),
                array(
                    'times_activated' => $timesActivatedNew
                )
            );
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        return $this->response(true, $this->getLicenseData($updatedLicense), 200);
    }

    /**
     * Callback for the GET licenses/validate{id} route. This check and verify the activation status of a given
     * license key.
     *
     * @param WP_REST_Request $request
     * 
     * @return WP_REST_Response|WP_Error
     */
    public function validateLicense(WP_REST_Request $request)
    {
        if (!$this->isRouteEnabled($this->settings, '005')) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'This route is disabled via the plugin settings.',
                array('status' => 404)
            );
        }

        $urlParams = $request->get_url_params();

        if (!array_key_exists('license_key', $urlParams)) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'License Key is invalid.',
                array('status' => 404)
            );
        }

        $licenseKey = sanitize_text_field($urlParams['license_key']);

        if (!$licenseKey) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                'License Key is invalid.',
                array('status' => 404)
            );
        }

        try {
            if (is_numeric($licenseKey)) {
                /** @var LicenseResourceModel $license */
                $license = LicenseResourceRepository::instance()->findBy(
                    array(
                        'id' => $licenseKey
                    )
                );
            }

            else {
                /** @var LicenseResourceModel $license */
                $license = LicenseResourceRepository::instance()->findBy(
                    array(
                        'hash' => apply_filters('lmfwc_hash', $licenseKey)
                    )
                );
            }
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        if (!$license) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'License Key: %s could not be found.',
                    $licenseKey
                ),
                array('status' => 404)
            );
        }

        $result = array(
            'times_activated'       => intval($license->getTimesActivated()),
            'times_activated_max'   => intval($license->getTimesActivatedMax()),
            'remaining_activations' => intval($license->getTimesActivatedMax()) - intval($license->getTimesActivated())
        );

        return $this->response(true, $result, 200);
    }

    /**
     * Converts the passed status string to a valid enumerator value.
     *
     * @param string $enumerator
     *
     * @return int
     */
    private function getStatus($enumerator)
    {
        $status = LicenseStatus::INACTIVE;

        if (strtoupper($enumerator) === 'SOLD') {
            $status = LicenseStatus::SOLD;
            return $status;
        }

        if (strtoupper($enumerator) === 'DELIVERED') {
            $status = LicenseStatus::DELIVERED;
            return $status;
        }

        if (strtoupper($enumerator) === 'ACTIVE') {
            $status = LicenseStatus::ACTIVE;
            return $status;
        }

        if (strtoupper($enumerator) === 'INACTIVE') {
            $status = LicenseStatus::INACTIVE;
            return $status;
        }

        return $status;
    }

    /**
     * Prepares the legacy API response format.
     *
     * @param LicenseResourceModel $license
     *
     * @return array
     */
    private function getLicenseData($license)
    {
        return array(
            'id'                  => $license->getId(),
            'order_id'            => $license->getOrderId(),
            'product_id'          => $license->getProductId(),
            'license_key'         => $license->getDecryptedLicenseKey(),
            'expires_at'          => $license->getExpiresAt(),
            'valid_for'           => $license->getValidFor(),
            'source'              => $license->getSource(),
            'status'              => $license->getStatus(),
            'times_activated'     => $license->getTimesActivated(),
            'times_activated_max' => $license->getTimesActivatedMax(),
            'created_at'          => $license->getCreatedAt(),
            'created_by'          => $license->getCreatedBy(),
            'updated_at'          => $license->getUpdatedAt(),
            'updated_by'          => $license->getUpdatedBy()
        );
    }
}