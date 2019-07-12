<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Add license keys in bulk', 'lmfwc'); ?></h1>
<hr class="wp-header-end">

<form method="post" action="<?php echo esc_html(admin_url('admin-post.php')) ;?>" enctype="multipart/form-data">
    <input type="hidden" name="action" value="lmfwc_import_license_keys">
    <?php wp_nonce_field('lmfwc_import_license_keys'); ?>

    <table class="form-table">
        <tbody>
        <!-- FILE -->
        <tr scope="row">
            <th scope="row"><label for="bulk__file"><?php esc_html_e('File', 'lmfwc'); ?> (txt/csv)</label></th>
            <td>
                <input name="file" id="bulk__file" class="regular-text" type="file" accept=".csv,.txt">
                <p class="description">
                    <b class="text-danger"><?php esc_html_e('Important', 'lmfwc'); ?>:</b>
                    <span><?php esc_html_e('One line per license key.', 'lmfwc');?></span>
                </p>
            </td>
        </tr>

        <!-- VALID FOR -->
        <tr scope="row">
            <th scope="row"><label for="bulk__valid_for"><?php esc_html_e('Valid for (days)', 'lmfwc');?></label></th>
            <td>
                <input name="valid_for" id="bulk__valid_for" class="regular-text" type="text">
                <p class="description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'lmfwc');?></p>
            </td>
        </tr>

        <!-- TIMES ACTIVATED MAX -->
        <tr scope="row">
            <th scope="row"><label for="bulk__times_activated_max"><?php esc_html_e('Maximum activation count', 'lmfwc');?></label></th>
            <td>
                <input name="times_activated_max" id="bulk__times_activated_max" class="regular-text" type="number">
                <p class="description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'lmfwc');?></p>
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
            <th scope="row"><label for="bulk__order"><?php esc_html_e('Order', 'lmfwc');?></label></th>
            <td>
                <?php if (!empty($orders)): ?>
                    <select name="order_id" id="bulk__order" class="regular-text">
                        <option value=""><?php esc_html_e('Select an order...');?></option>
                        <?php
                        /** @var WC_Order $order */
                        foreach ($orders as $order) {
                            echo sprintf(
                                '<option value="%d">#%d - %s</option>',
                                $order->get_id(),
                                $order->get_id(),
                                $order->get_formatted_billing_full_name()
                            );
                        }
                        ?>
                    </select>
                <?php else: ?>
                    <label><?php esc_html_e('You do not have any orders.', 'lmfwc');?></label>
                <?php endif; ?>
                <p class="description"><?php esc_html_e('The order to which the license keys will be assigned.', 'lmfwc');?></p>
            </td>
        </tr>

        <!-- PRODUCT -->
        <tr scope="row">
            <th scope="row"><label for="bulk__product"><?php esc_html_e('Product', 'lmfwc');?></label></th>
            <td>
                <?php if (!empty($products)): ?>
                    <select name="product_id" id="bulk__product" class="regular-text">
                        <option value=""><?php esc_html_e('Select a product...', 'lmfwc');?></option>
                        <?php foreach ($products as $product): ?>
                            <?php
                                echo sprintf(
                                    '<option value="%d">#%d - %s</option>',
                                    $product['id'],
                                    $product['id'],
                                    $product['name']
                                );
                            ?>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <label><?php esc_html_e('You do not have any products defined.', 'lmfwc');?></label>
                <?php endif; ?>
                <p class="description"><?php esc_html_e('The product to which the license keys will be assigned. You can also leave this blank to manually assign them later.', 'lmfwc');?></p>
            </td>
        </tr>
        </tbody>
    </table>

    <p class="submit">
        <input name="submit" id="bulk__submit" class="button button-primary" value="<?php esc_html_e('Import' ,'lmfwc');?>" type="submit">
    </p>
</form>
