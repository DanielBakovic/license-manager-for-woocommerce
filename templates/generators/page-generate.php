<?php

use LicenseManagerForWooCommerce\Models\Resources\Generator as GeneratorResourceModel;

defined('ABSPATH') || exit;

/** @var GeneratorResourceModel $generator */

?>

<h1 class="wp-heading-inline"><?php esc_html_e('Generate license keys', 'lmfwc'); ?></h1>
<hr class="wp-header-end">

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="lmfwc_generate_license_keys">
    <?php wp_nonce_field('lmfwc_generate_license_keys'); ?>

    <table class="form-table">
        <tbody>
        <!-- GENERATOR -->
        <tr scope="row">
            <th scope="row">
                <label for="generate__generator"><?php esc_html_e('Generator', 'lmfwc');?></label>
                <span class="text-danger">*</span></label>
            </th>
            <td>
                <select id="generate__generator" name="generator_id" class="regular-text">
                    <?php foreach($generators as $generator): ?>
                        <option value="<?php esc_attr_e($generator->getId()); ?>"><?php esc_attr_e($generator->getName()); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('The selected generator\'s rules will be used to generate the license keys.', 'lmfwc');?></p>
            </td>
        </tr>

        <!-- AMOUNT -->
        <tr scope="row">
            <th scope="row"><label for="generate__amount"><?php esc_html_e('Amount', 'lmfwc');?></label></th>
            <td>
                <input name="amount" id="generate__amount" class="regular-text" type="number">
                <p class="description"><?php esc_html_e('Define how many license keys will be generated.', 'lmfwc');?></p>
            </td>
        </tr>

        <!-- STATUS -->
        <tr scope="row">
            <th scope="row"><label for="edit__status"><?php esc_html_e('Status', 'lmfwc');?></label></th>
            <td>
                <select id="edit__status" name="status" class="regular-text">
                    <?php foreach($statusOptions as $option): ?>
                        <option value="<?php echo esc_html($option['value']); ?>"><?php echo esc_html($option['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- ORDER -->
        <tr scope="row">
            <th scope="row"><label for="generate__order"><?php esc_html_e('Order', 'lmfwc');?></label></th>
            <td>
                <select name="order_id" id="generate__order" class="regular-text"></select>
                <p class="description"><?php esc_html_e('The order to which the license keys will be assigned.', 'lmfwc');?></p>
            </td>
        </tr>

        <!-- PRODUCT -->
        <tr scope="row">
            <th scope="row"><label for="generate__product"><?php esc_html_e('Product', 'lmfwc');?></label></th>
            <td>
                <select name="product_id" id="generate__product" class="regular-text"></select>
                <p class="description"><?php esc_html_e('The product to which the license keys will be assigned.', 'lmfwc');?></p>
            </td>
        </tr>

        </tbody>
    </table>

    <?php submit_button(__('Generate', 'lmfwc')); ?>

</form>