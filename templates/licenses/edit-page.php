<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Edit License Key', 'lmfwc'); ?></h1>
<hr class="wp-header-end">

<form method="post" action="<?php echo admin_url('admin-post.php');?>">

    <input type="hidden" name="source" value="<?php echo esc_html($license_source); ?>">
    <input type="hidden" name="action" value="lmfwc_update_license_key">
    <?php wp_nonce_field('lmfwc_update_license_key'); ?>

    <table class="form-table">
        <tbody>
            <tr scope="row">
                <th scope="row"><label><?php _e('ID', 'lmfwc');?></label></th>
                <td>
                    <input name="license_id" id="license_id" class="regular-text" type="text" value="<?php echo esc_html($license_id); ?>" readonly>
                </td>
            </tr>

            <!-- LICENSE KEY -->
            <tr scope="row">
                <th scope="row"><label><?php _e('License Key', 'lmfwc');?></label></th>
                <td>
                    <input name="license_key" id="license_key" class="regular-text" type="text" value="<?php echo esc_html($license_key); ?>">
                    <p class="description" id="tagline-description"><?php esc_html_e('The license key will be encrypted before it is stored inside the database.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- VALID FOR -->
            <tr scope="row">
                <th scope="row"><label><?php _e('Validity', 'lmfwc');?></label></th>
                <td>
                    <input name="valid_for" id="valid_for" class="regular-text" type="text" value="<?php echo esc_html($valid_for); ?>">
                    <p class="description" id="tagline-description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- TIMES ACTIVATED MAX -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Maximum activation count', 'lmfwc');?></label></th>
                <td>
                    <input name="times_activated_max" id="times_activated_max" class="regular-text" type="number" value="<?php echo esc_html($times_activated_max); ?>">
                    <p class="description" id="tagline-description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- STATUS -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Status', 'lmfwc');?></label></th>
                <td>
                    <select id="status" name="status" class="regular-text">
                        <option
                            value="<?php echo esc_html($status_active); ?>"
                            <?php selected($status_active, $license_status, true); ?>
                        >
                            <span><?php esc_html_e('Active', 'wcdpi'); ?></span>
                        </option>
                        <option
                            value="<?php echo esc_html($status_inactive); ?>"
                            <?php selected($status_inactive, $license_status, true); ?>
                        >
                            <span><?php esc_html_e('Inactive', 'wcdpi'); ?></span>
                        </option>
                    </select>
                </td>
            </tr>

            <!-- PRODUCT -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Product', 'lmfwc');?></label></th>
                <td>
                    <?php if ($products->have_posts()): ?>
                        <select name="product" id="product" class="regular-text">
                            <option value=""><?php esc_html_e('Select a product...');?></option>
                            <?php /** @var $product WP_Post */ ?>
                            <?php foreach ($products->posts as $product): ?>
                                <?php $selected = selected($product->ID, $product_id, false); ?>
                                <option value="<?=$product->ID;?>" <?php echo $selected; ?>>
                                    <span><?php echo esc_html($product->post_title); ?></span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <label><?php esc_html_e('You do not have any products defined.', 'lmfwc');?></label>
                    <?php endif; ?>
                    <p class="description" id="tagline-description"><?php esc_html_e('The product to which the keys will be assigned. You can also leave this blank to manually assign them later.', 'lmfwc');?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <?php echo submit_button(__('Save' ,'lmfwc')); ?>

</form>
