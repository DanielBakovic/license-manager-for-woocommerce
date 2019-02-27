<?php

namespace LicenseManagerForWooCommerce\API;

use \LicenseManagerForWooCommerce\Logger;

defined('ABSPATH') || exit;

/**
 * Authentication for the API endpoints
 *
 * @version 1.0.0
 * @since 1.1.0
 */
class Authentication
{
    /**
     * Authentication error.
     *
     * @var WP_Error
     */
    protected $error = null;

    /**
     * Logged in user data.
     *
     * @var stdClass
     */
    protected $user = null;

    /**
     * Current authentication method.
     *
     * @var string
     */
    protected $auth_method = '';

    /**
     * Initialize authentication actions.
     */
    public function __construct() {
        add_filter('determine_current_user',     array($this, 'authenticate'), 15);
        add_filter('rest_authentication_errors', array($this, 'checkAuthenticationError'), 15);
        add_filter('rest_post_dispatch',         array($this, 'sendUnauthorizedHeaders'), 50);
        add_filter('rest_pre_dispatch',          array($this, 'checkUserPermissions'), 10, 3);
    }

    /**
     * Check if is request to our REST API.
     *
     * @return bool
     */
    protected function isRequestToRestApi() {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $rest_prefix = trailingslashit(rest_get_url_prefix());

        // Check if our endpoint.
        $lmfwc = (false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix . 'lmfwc/'));

        return $lmfwc;
    }

    /**
     * Authenticate user.
     *
     * @param int|false $user_id User ID if one has been determined, false otherwise.
     * @return int|false
     */
    public function authenticate($user_id) {
        // Do not authenticate twice and check if is a request to our endpoint in the WP REST API.
        if (!empty($user_id) || !$this->isRequestToRestApi()) {
            return $user_id;
        }

        //if (is_ssl()) {
        if (1 == 1) {
            $user_id = $this->performBasicAuthentication();
        }

        if ($user_id) {
            return $user_id;
        }

        return false;
    }

    /**
     * Check for authentication error.
     *
     * @param WP_Error|null|bool $error Error data.
     * @return WP_Error|null|bool
     */
    public function checkAuthenticationError($error) {
        // Pass through other errors.
        if (!empty($error)) {
            return $error;
        }

        return $this->getError();
    }

    /**
     * Set authentication error.
     *
     * @param WP_Error $error Authentication error data.
     */
    protected function setError($error) {
        // Reset user.
        $this->user = null;

        $this->error = $error;
    }

    /**
     * Get authentication error.
     *
     * @return WP_Error|null.
     */
    protected function getError() {
        return $this->error;
    }

    /**
     * Basic Authentication.
     *
     * SSL-encrypted requests are not subject to sniffing or man-in-the-middle
     * attacks, so the request can be authenticated by simply looking up the user
     * associated with the given consumer key and confirming the consumer secret
     * provided is valid.
     *
     * @return int|bool
     */
    private function performBasicAuthentication() {
        $this->auth_method = 'basic_auth';
        $consumer_key      = '';
        $consumer_secret   = '';

        // If the $_GET parameters are present, use those first.
        if (!empty($_GET['consumer_key']) && !empty($_GET['consumer_secret'])) {
            $consumer_key    = $_GET['consumer_key'];
            $consumer_secret = $_GET['consumer_secret'];
        }

        // If the above is not present, we will do full basic auth.
        if (!$consumer_key && !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            $consumer_key    = $_SERVER['PHP_AUTH_USER'];
            $consumer_secret = $_SERVER['PHP_AUTH_PW'];
        }

        // Stop if don't have any key.
        if (!$consumer_key || !$consumer_secret) {
            return false;
        }

        // Get user data.
        $this->user = $this->getUserDataByConsumerKey($consumer_key);
        if (empty($this->user)) {
            return false;
        }

        // Validate user secret.
        if (!hash_equals($this->user->consumer_secret, $consumer_secret)) {
            $this->set_error(
                new WP_Error(
                    'lmfwc_rest_authentication_error',
                    __('Consumer secret is invalid.', 'lmfwc'),
                    array('status' => 401)
                )
            );

            return false;
        }

        return $this->user->user_id;
    }

    /**
     * Return the user data for the given consumer_key.
     *
     * @param string $consumer_key Consumer key.
     * @return array
     */
    private function getUserDataByConsumerKey($consumer_key) {
        global $wpdb;

        $consumer_key = wc_api_hash(sanitize_text_field($consumer_key));
        $user         = $wpdb->get_row(
            $wpdb->prepare(
                "
            SELECT id, user_id, permissions, consumer_key, consumer_secret, nonces
            FROM {$wpdb->prefix}lmfwc_api_keys
            WHERE consumer_key = %s
        ",
                $consumer_key
            )
        );

        return $user;
    }

    /**
     * Check that the API keys provided have the proper key-specific permissions to either read or write API resources.
     *
     * @param string $method Request method.
     * @return bool|WP_Error
     */
    private function checkPermissions($method) {
        $permissions = $this->user->permissions;

        switch ($method) {
            case 'HEAD':
            case 'GET':
                if ('read' !== $permissions && 'read_write' !== $permissions) {
                    return new WP_Error(
                        'lmfwc_rest_authentication_error',
                        __('The API key provided does not have read permissions.', 'lmfwc'),
                        array('status' => 401)
                    );
                }
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                if ('write' !== $permissions && 'read_write' !== $permissions) {
                    return new WP_Error(
                        'lmfwc_rest_authentication_error',
                        __('The API key provided does not have write permissions.', 'lmfwc' ),
                        array('status' => 401)
                    );
                }
                break;
            case 'OPTIONS':
                return true;

            default:
                return new WP_Error(
                    'lmfwc_rest_authentication_error',
                    __('Unknown request method.', 'lmfwc' ),
                    array('status' => 401)
                );
        }

        return true;
    }

    /**
     * Updated API Key last access datetime.
     */
    private function updateLastAccess() {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'lmfwc_api_keys',
            array('last_access' => current_time('mysql')),
            array('id' => $this->user->id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * If the consumer_key and consumer_secret $_GET parameters are NOT provided
     * and the Basic auth headers are either not present or the consumer secret does not match the consumer
     * key provided, then return the correct Basic headers and an error message.
     *
     * @param WP_REST_Response $response Current response being served.
     * @return WP_REST_Response
     */
    public function sendUnauthorizedHeaders($response) {
        if (is_wp_error($this->getError()) && 'basic_auth' === $this->auth_method) {
            $auth_message = __('License Manager for WooCommerce API. Use a consumer key in the username field and a consumer secret in the password field.', 'lmfwc' );
            $response->header('WWW-Authenticate', 'Basic realm="' . $auth_message . '"', true);
        }

        return $response;
    }

    /**
     * Check for user permissions and register last access.
     *
     * @param mixed           $result  Response to replace the requested version with.
     * @param WP_REST_Server  $server  Server instance.
     * @param WP_REST_Request $request Request used to generate the response.
     * @return mixed
     */
    public function checkUserPermissions($result, $server, $request) {
        if ($this->user) {
            // Check API Key permissions.
            $allowed = $this->checkPermissions($request->get_method());

            if (is_wp_error($allowed)) {
                return $allowed;
            }

            // Register last access.
            $this->updateLastAccess();
        }

        return $result;
    }
}