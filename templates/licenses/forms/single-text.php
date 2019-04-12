<?php defined('ABSPATH') || exit; ?>

<form method="post" action="<?php echo esc_html(admin_url('admin-post.php'));?>">
    <input type="hidden" name="action" value="lmfwc_add_license_key">
    <?php wp_nonce_field('lmfwc_add_license_key'); ?>

    <table class="form-table">
        <tbody>
            <!-- LICENSE KEY -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('License key', 'lmfwc');?></label></th>
                <td>
                    <input name="license_key" id="single__license_key" class="regular-text" type="text">
                    <p class="description"><?php esc_html_e('The license key will be encrypted before it is stored inside the database.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- VALID FOR -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Valid for (days)', 'lmfwc');?></label></th>
                <td>
                    <input name="valid_for" id="single__valid_for" class="regular-text" type="text">
                    <p class="description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- TIMES ACTIVATED MAX -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Maximum activation count', 'lmfwc');?></label></th>
                <td>
                    <input name="times_activated_max" id="single__times_activated_max" class="regular-text" type="number">
                    <p class="description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- ACTIVATION STATUS -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Activate licenses', 'lmfwc');?></label></th>
                <td>
                    <label for="activate">
                        <input name="activate" id="single__activate" class="regular-text" type="checkbox" checked="checked">
                        <span><?php esc_html_e('Activate license immediately after import.', 'lmfwc');?></span>
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

                        <select name="product" id="single__product" class="regular-text">
                            <option value=""><?php esc_html_e('Select a product...', 'lmfwc');?></option>
                            <?php foreach ($products->posts as $product): ?>
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
        <input name="submit" id="single__submit" class="button button-primary" value="<?php esc_html_e('Add' ,'lmfwc');?>" type="submit">
    </p>
</form>
