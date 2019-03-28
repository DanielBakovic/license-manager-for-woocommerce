<?php
/**
 * Export handler
 * PHP Version: 5.6
 * 
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 */

namespace LicenseManagerForWooCommerce;

use \LicenseManagerForWooCommerce\Exception as LMFWC_Exception;
use \LicenseManagerForWooCommerce\Enums\LicenseSource as LicenseSourceEnum;
use \LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce Export
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <1.1.0>
 * @link     https://www.bebic.at/license-manager-for-woocommerce
 * @since    1.0.0
 */
class Export
{
    /**
     * Export Constructor.
     */
    public function __construct()
    {
        add_action(
            'lmfwc_export_license_keys_pdf',
            array($this, 'exportLicenseKeysPdf'),
            10,
            1
        );
        add_action(
            'lmfwc_export_license_keys_csv',
            array($this, 'exportLicenseKeysCsv'),
            10,
            1
        );
    }

    /**
     * Creates a PDF of license keys by the given array of IDs
     * 
     * @param array $ids License Key ID's
     * 
     * @return null
     */
    public function exportLicenseKeysPdf($ids)
    {
        $license_keys = array();

        foreach ($ids as $license_key_id) {
            try {
                $license = apply_filters('lmfwc_get_license_key', $license_key_id);
            } catch (LMFWC_Exception $e) {
                continue;
            }

            $license_keys[] = array(
                'id' => $license['id'],
                'order_id' => $license['order_id'],
                'product_id' => $license['product_id'],
                'license_key' => apply_filters('lmfwc_decrypt', $license['license_key'])
            );
        }

        $header = array(
            'id' => __('ID', 'lmfwc'),
            'order_id' => __('Order ID', 'lmwfc'),
            'product_id' => __('Product ID', 'lmwfc'),
            'license_key' => __('License Key', 'lmfwc')
        );

        ob_start();

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->AddFont('Roboto-Bold', '', 'Roboto-Bold.php');
        $pdf->AddFont('Roboto-Regular', '', 'Roboto-Regular.php');
        $pdf->AddFont('RobotoMono-Regular', '', 'RobotoMono-Regular.php');
        $pdf->SetFont('Roboto-Bold', '', 10);

        // Header
        $pdf->Image(LMFWC_IMG_URL . 'lmfwc_logo.jpg', 10, 10, -300);
        $pdf->Ln(25);

        // Table Header
        $pdf->SetDrawColor(200, 200, 200);

        foreach ($header as $col_name => $col) {
            $width = 40;

            if ($col_name == 'id') {
                $width = 12;
            }

            if ($col_name == 'order_id'
                || $col_name == 'product_id'
            ) {
                $width = 20;
            }

            if ($col_name == 'license_key') {
                $width = 0;
            }

            $pdf->Cell($width, 10, $col, 'B');
        }

        // Data
        $pdf->Ln();

        foreach ($license_keys as $row) {
            foreach ($row as $col_name => $col) {
                $pdf->SetFont('Roboto-Regular', '', 8);
                $width = 40;

                if ($col_name == 'id') {
                    $width = 12;
                }

                if ($col_name == 'order_id'
                    || $col_name == 'product_id'
                ) {
                    $width = 20;
                }

                if ($col_name == 'license_key') {
                    $pdf->SetFont('RobotoMono-Regular', '', 8);
                    $width = 0;
                }

                $pdf->Cell($width, 6, $col, 'B');
            }

            $pdf->Ln();
        }

        $pdf->Output();

        ob_end_flush();
    }

    /**
     * Creates a CSV of license keys by the given array of IDs
     * 
     * @param array $ids License Key ID's
     * 
     * @return null
     */
    public function exportLicenseKeysCsv($ids)
    {
        $license_keys = array();

        foreach ($ids as $license_key_id) {
            try {
                $license = apply_filters('lmfwc_get_license_key', $license_key_id);
            } catch (LMFWC_Exception $e) {
                continue;
            }

            $license_keys[] = array(
                'order_id' => $license['order_id'],
                'product_id' => $license['product_id'],
                'license_key' => apply_filters('lmfwc_decrypt', $license['license_key']),
                'created_at' => $license['created_at'],
                'expires_at' => $license['expires_at'],
                'valid_for' => $license['valid_for'],
                'source' => LicenseSourceEnum::getExportLabel($license['source']),
                'status' => LicenseStatusEnum::getExportLabel($license['status'])
            );
        }

        $filename = gmdate('Y_m_d-H_i_s-') . 'LICENSE_KEYS_EXPORT.csv';

        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

        ob_clean();
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($license_keys)));

        foreach ($license_keys as $row) {
           fputcsv($df, $row);
        }

        fclose($df);
        ob_end_flush();

        exit();
    }
}
