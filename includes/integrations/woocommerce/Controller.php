<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use LicenseManagerForWooCommerce\Abstracts\IntegrationController as AbstractIntegrationController;
use LicenseManagerForWooCommerce\Enums\LicenseSource;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Exception as LMFWC_Exception;
use LicenseManagerForWooCommerce\Interfaces\IntegrationController as IntegrationControllerInterface;
use LicenseManagerForWooCommerce\Models\Resources\Generator as GeneratorResourceModel;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use stdClass;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product_Simple;
use WC_Product_Variation;

defined('ABSPATH') || exit;

class Controller extends AbstractIntegrationController implements IntegrationControllerInterface
{
    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->bootstrap();

        add_filter('lmfwc_get_customer_license_keys',     array($this, 'getCustomerLicenseKeys'),     10, 1);
        add_filter('lmfwc_insert_generated_license_keys', array($this, 'insertGeneratedLicenseKeys'), 10, 6);
        add_filter('lmfwc_insert_imported_license_keys',  array($this, 'insertImportedLicenseKeys'),  10, 5);
        add_action('lmfwc_sell_imported_license_keys',    array($this, 'sellImportedLicenseKeys'),    10, 3);
    }

    /**
     * Initializes the integration component
     */
    private function bootstrap()
    {
        new Order();
        new Email();
        new ProductData();
    }

    /**
     * Retrieves ordered license keys.
     *
     * @param WC_Order $order
     *
     * @return array
     */
    public function getCustomerLicenseKeys($order)
    {
        $data = array();

        /** @var WC_Order_Item_Product $item_data */
        foreach ($order->get_items() as $item_data) {

            /** @var WC_Product_Simple|WC_Product_Variation $product */
            $product = $item_data->get_product();

            // Check if the product has been activated for selling.
            if (!get_post_meta($product->get_id(), 'lmfwc_licensed_product', true)) {
                continue;
            }

            /** @var LicenseResourceModel[] $licenses */
            $licenses = LicenseResourceRepository::instance()->findAllBy(
                array(
                    'order_id' => $order->get_id(),
                    'product_id' => $product->get_id()
                )
            );

            $data[$product->get_id()]['name'] = $product->get_name();
            $data[$product->get_id()]['keys'] = $licenses;
        }

        return $data;
    }

    /**
     * Save the license keys for a given product to the database.
     *
     * @param int                    $orderId
     * @param int                    $productId
     * @param array                  $licenseKeys
     * @param int                    $expiresIn
     * @param int                    $status
     * @param GeneratorResourceModel $generator
     *
     * @throws LMFWC_Exception
     * @throws Exception
     */
    public function insertGeneratedLicenseKeys($orderId, $productId, $licenseKeys, $expiresIn, $status, $generator)
    {
        $cleanLicenseKeys = array();
        $cleanOrderId   = $orderId   ? absint($orderId)   : null;
        $cleanProductId = $productId ? absint($productId) : null;
        $cleanExpiresIn = $expiresIn ? absint($expiresIn) : null;
        $cleanStatus    = $status    ? absint($status)    : null;

        if (!$cleanStatus
            || !in_array($cleanStatus, LicenseStatus::$status)
        ) {
            throw new LMFWC_Exception('License Status is invalid.');
        }

        if (!is_array($licenseKeys)) {
            throw new LMFWC_Exception('License Keys must be provided as array');
        }

        foreach ($licenseKeys as $licenseKey) {
            array_push($cleanLicenseKeys, sanitize_text_field($licenseKey));
        }

        if (count($cleanLicenseKeys) === 0) {
            throw new LMFWC_Exception('No License Keys were provided');
        }

        $gmtDate           = new DateTime('now', new DateTimeZone('GMT'));
        $invalidKeysAmount = 0;
        $expiresAt         = null;

        if ($cleanExpiresIn && $status == LicenseStatus::SOLD) {
            $dateInterval  = 'P' . $cleanExpiresIn . 'D';
            $dateExpiresAt = new DateInterval($dateInterval);
            $expiresAt     = $gmtDate->add($dateExpiresAt)->format('Y-m-d H:i:s');
        }

        // Add the keys to the database table.
        foreach ($cleanLicenseKeys as $licenseKey) {
            $license = LicenseResourceRepository::instance()->findBy(
                array(
                    'hash' => apply_filters('lmfwc_hash', $licenseKey)
                )
            );

            // Key exists, up the invalid keys count.
            if ($license) {
                $invalidKeysAmount++;
                continue;
            }

            // Key doesn't exist, add it to the database table.
            $encryptedLicenseKey = apply_filters('lmfwc_encrypt', $licenseKey);
            $hashedLicenseKey    = apply_filters('lmfwc_hash', $licenseKey);

            // Save to database.
            LicenseResourceRepository::instance()->insert(
                array(
                    'order_id'            => $cleanOrderId,
                    'product_id'          => $cleanProductId,
                    'license_key'         => $encryptedLicenseKey,
                    'hash'                => $hashedLicenseKey,
                    'expires_at'          => $expiresAt,
                    'valid_for'           => $cleanExpiresIn,
                    'source'              => LicenseSource::GENERATOR,
                    'status'              => $cleanStatus,
                    'times_activated_max' => $generator->getTimesActivatedMax()
                )
            );
        }

        // There have been duplicate keys, regenerate and add them.
        if ($invalidKeysAmount > 0) {
            $newKeys = apply_filters(
                'lmfwc_create_license_keys',
                array(
                    'amount'       => $invalidKeysAmount,
                    'charset'      => $generator->getCharset(),
                    'chunks'       => $generator->getChunks(),
                    'chunk_length' => $generator->getChunkLength(),
                    'separator'    => $generator->getSeparator(),
                    'prefix'       => $generator->getPrefix(),
                    'suffix'       => $generator->getSuffix(),
                    'expires_in'   => $cleanExpiresIn
                )
            );
            $this->insertGeneratedLicenseKeys(
                $cleanOrderId,
                $cleanProductId,
                $newKeys['licenses'],
                $cleanExpiresIn,
                $cleanStatus,
                $generator
            );
        } else {
            // Keys have been generated and saved, this order is now complete.
            update_post_meta($cleanOrderId, 'lmfwc_order_complete', 1);
        }
    }

    /**
     * Imports an array of un-encrypted license keys.
     *
     * @param array $licenseKeys
     * @param int   $status
     * @param int   $productId
     * @param int   $validFor
     * @param int   $timesActivatedMax
     *
     * @return array
     * @throws LMFWC_Exception
     */
    public function insertImportedLicenseKeys($licenseKeys, $status, $productId, $validFor, $timesActivatedMax)
    {
        $result                 = array();
        $cleanLicenseKeys       = array();
        $cleanStatus            = $status            ? absint($status)            : null;
        $cleanProductId         = $productId         ? absint($productId)         : null;
        $cleanValidFor          = $validFor          ? absint($validFor)          : null;
        $cleanTimesActivatedMax = $timesActivatedMax ? absint($timesActivatedMax) : null;

        if (!is_array($licenseKeys)) {
            throw new LMFWC_Exception('License Keys must be an array');
        }

        if (!$cleanStatus) {
            throw new LMFWC_Exception('Status enumerator is missing');
        }

        if (!in_array($cleanStatus, LicenseStatus::$status)) {
            throw new LMFWC_Exception('Status enumerator is invalid');
        }

        foreach ($licenseKeys as $licenseKey) {
            array_push($cleanLicenseKeys, sanitize_text_field($licenseKey));
        }

        $result['added']  = 0;
        $result['failed'] = 0;

        // Add the keys to the database table.
        foreach ($cleanLicenseKeys as $licenseKey) {
            $license = LicenseResourceRepository::instance()->insert(
                array(
                    'order_id'            => null,
                    'product_id'          => $cleanProductId,
                    'license_key'         => apply_filters('lmfwc_encrypt', $licenseKey),
                    'hash'                => apply_filters('lmfwc_hash', $licenseKey),
                    'expires_at'          => null,
                    'valid_for'           => $cleanValidFor,
                    'source'              => LicenseSource::IMPORT,
                    'status'              => $cleanStatus,
                    'times_activated_max' => $cleanTimesActivatedMax,
                )
            );

            if ($license) {
                $result['added']++;
            } else {
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * Mark the imported license keys as sold.
     *
     * @param LicenseResourceModel[] $licenses
     * @param int                    $orderId
     * @param int                    $amount
     *
     * @throws LMFWC_Exception
     * @throws Exception
     */
    public function sellImportedLicenseKeys($licenses, $orderId, $amount)
    {
        $cleanLicenseKeys = $licenses;
        $cleanOrderId     = $orderId ? absint($orderId) : null;
        $cleanAmount      = $amount  ? absint($amount)  : null;

        if (!is_array($licenses) || count($licenses) <= 0) {
            throw new LMFWC_Exception('License Keys are invalid.');
        }

        if (!$cleanOrderId) {
            throw new LMFWC_Exception('Order ID is invalid.');
        }

        if (!$cleanOrderId) {
            throw new LMFWC_Exception('Amount is invalid.');
        }

        for ($i = 0; $i < $cleanAmount; $i++) {
            /** @var LicenseResourceModel $license */
            $license   = $cleanLicenseKeys[$i];
            $validFor  = intval($license->getValidFor());
            $expiresAt = null;

            if ($validFor) {
                $date         = new DateTime();
                $dateInterval = new DateInterval('P' . $validFor . 'D');
                $expiresAt    = $date->add($dateInterval)->format('Y-m-d H:i:s');
            }

            LicenseResourceRepository::instance()->update(
                $license->getId(),
                array(
                    'order_id'   => $cleanOrderId,
                    'expires_at' => $expiresAt,
                    'status'     => LicenseStatus::SOLD
                )
            );
        }
    }
}