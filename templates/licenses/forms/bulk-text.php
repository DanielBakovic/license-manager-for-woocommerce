<?php defined('ABSPATH') || exit; ?>

<form method="post" action="<?php echo esc_html(admin_url('admin-post.php')) ;?>" enctype="multipart/form-data">
    <input type="hidden" name="action" value="lmfwc_import_license_keys">
    <?php wp_nonce_field('lmfwc_import_license_keys'); ?>

    <table class="form-table">
        <tbody>
            <!-- FILE -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('File', 'lmfwc'); ?> (txt/csv)</label></th>
                <td>
                    <input name="file" id="bulk__file" class="regular-text" type="file" accept=".csv,.txt">
                    <p class="description">
                        <b class="text-danger"><?php esc_html_e('Important', 'lmfwc'); ?>:</b>
                        <span><?php esc_html_e('One line per key. Keys must be decrypted.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- VALID FOR -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Valid for (days)', 'lmfwc');?></label></th>
                <td>
                    <input name="valid_for" id="bulk__valid_for" class="regular-text" type="text">
                    <p class="description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- TIMES ACTIVATED MAX -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Maximum activation count', 'lmfwc');?></label></th>
                <td>
                    <input name="times_activated_max" id="bulk__times_activated_max" class="regular-text" type="number">
                    <p class="description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- ACTIVATION STATUS -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Activate licenses', 'lmfwc');?></label></th>
                <td>
                    <label for="activate">
                        <input name="activate" id="bulk__activate" class="regular-text" type="checkbox" checked="checked">
                        <span><?php esc_html_e('Activate licenses immediately after import.', 'lmfwc');?></span>
                    </label>
                    <p class="description">
                        <span><?php esc_html_e('Activated licenses are immediately availabale for sale, while inactive licenses must be activated manually.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- PRODUCT -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Product', 'lmfwc');?></label></th>
                <td>
                    <?php if ($products->have_posts()): ?>
                        <select name="product" id="bulk__product" class="regular-text">
                            <option value=""><?php esc_html_e('Select a product...', 'lmfwc');?></option>
                            <?php foreach($products->posts as $product): ?>
                                <option value="<?=$product->ID;?>"><?=$product->post_title;?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <label><?php esc_html_e('You do not have any products defined.', 'lmfwc');?></label>
                    <?php endif; ?>
                    <p class="description"><?php esc_html_e('The product to which the keys will be assigned. You can also leave this blank to manually assign them later.', 'lmfwc');?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <input name="submit" id="bulk__submit" class="button button-primary" value="<?php esc_html_e('Import' ,'lmfwc');?>" type="submit">
    </p>
</form>
