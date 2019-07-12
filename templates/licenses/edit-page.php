<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Edit license key', 'lmfwc'); ?></h1>
<hr class="wp-header-end">

<form method="post" action="<?php echo admin_url('admin-post.php');?>">

    <input type="hidden" name="source" value="<?php echo esc_html($license->getSource()); ?>">
    <input type="hidden" name="action" value="lmfwc_update_license_key">
    <?php wp_nonce_field('lmfwc_update_license_key'); ?>

    <table class="form-table">
        <tbody>
            <tr scope="row">
                <th scope="row"><label for="edit__license_id"><?php esc_html_e('ID', 'lmfwc');?></label></th>
                <td>
                    <input name="license_id" id="edit__license_id" class="regular-text" type="text" value="<?php echo esc_html($license->getId()); ?>" readonly>
                </td>
            </tr>

            <!-- LICENSE KEY -->
            <tr scope="row">
                <th scope="row"><label for="edit__license_key"><?php esc_html_e('License key', 'lmfwc');?></label></th>
                <td>
                    <input name="license_key" id="edit__license_key" class="regular-text" type="text" value="<?php echo esc_html($licenseKey); ?>">
                    <p class="description"><?php esc_html_e('The license key will be encrypted before it is stored inside the database.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- VALID FOR -->
            <tr scope="row">
                <th scope="row"><label for="edit__valid_for"><?php esc_html_e('Validity', 'lmfwc');?></label></th>
                <td>
                    <input name="valid_for" id="edit__valid_for" class="regular-text" type="text" value="<?php echo esc_html($license->getValidFor()); ?>">
                    <p class="description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- TIMES ACTIVATED MAX -->
            <tr scope="row">
                <th scope="row"><label for="edit__times_activated_max"><?php esc_html_e('Maximum activation count', 'lmfwc');?></label></th>
                <td>
                    <input name="times_activated_max" id="edit__times_activated_max" class="regular-text" type="number" value="<?php echo esc_html($license->getTimesActivatedMax()); ?>">
                    <p class="description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- STATUS -->
            <tr scope="row">
                <th scope="row"><label for="edit__status"><?php esc_html_e('Status', 'lmfwc');?></label></th>
                <td>
                    <select id="edit__status" name="status" class="regular-text">
                        <?php foreach($statusOptions as $option): ?>
                            <option value="<?php echo esc_html($option['value']); ?>" <?php selected($option['value'], $license->getStatus(), true); ?>>
                                <span><?php echo esc_html($option['name']); ?></span>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- ORDER -->
            <tr scope="row">
                <th scope="row"><label for="edit__order"><?php esc_html_e('Order', 'lmfwc');?></label></th>
                <td>
                    <?php if (!empty($orders)): ?>
                        <select name="order_id" id="edit__order" class="regular-text">
                            <option value=""><?php esc_html_e('Select an order...');?></option>
                            <?php
                            /** @var WC_Order $order */
                            foreach ($orders as $order) {
                                $selected = selected($order->get_id(), $license->getOrderId(), false);

                                echo sprintf(
                                    '<option value="%d" %s>#%d - %s</option>',
                                    $order->get_id(),
                                    $selected,
                                    $order->get_id(),
                                    $order->get_formatted_billing_full_name()
                                );
                            }
                            ?>
                        </select>
                    <?php else: ?>
                        <label><?php esc_html_e('You do not have any products defined.', 'lmfwc');?></label>
                    <?php endif; ?>
                    <p class="description"><?php esc_html_e('The product to which the license keys will be assigned. You can also leave this blank to manually assign them later.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- PRODUCT -->
            <tr scope="row">
                <th scope="row"><label for="edit__product"><?php esc_html_e('Product', 'lmfwc');?></label></th>
                <td>
                    <?php if (!empty($products)): ?>
                        <select name="product" id="edit__product" class="regular-text">
                            <option value=""><?php esc_html_e('Select a product...');?></option>
                            <?php
                            foreach ($products as $product) {
                                $selected = selected($product['id'], $license->getProductId(), false);

                                echo sprintf(
                                    '<option value="%d" %s>#%d - %s</option>',
                                    $product['id'],
                                    $selected,
                                    $product['id'],
                                    $product['name']
                                );
                            }
                            ?>
                        </select>
                    <?php else: ?>
                        <label><?php esc_html_e('You do not have any products defined.', 'lmfwc');?></label>
                    <?php endif; ?>
                    <p class="description"><?php esc_html_e('The product to which the license keys will be assigned. You can also leave this blank to manually assign them later.', 'lmfwc');?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <?php echo submit_button(__('Save' ,'lmfwc')); ?>

</form>
