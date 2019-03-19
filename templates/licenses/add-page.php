<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Import License Keys', 'lmfwc'); ?></h1>
<hr class="wp-header-end">

<form method="post" action="<?php echo esc_html(admin_url('admin-post.php'));?>" enctype="multipart/form-data">

    <input type="hidden" name="action" value="lmfwc_import_license_keys">
    <?php wp_nonce_field('lmfwc_import_license_keys'); ?>

    <table class="form-table">
        <tbody>
            <!-- FILE -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('File (TXT)', 'lmfwc');?></label></th>
                <td>
                    <input name="file" id="file" class="regular-text" type="file" accept=".csv,.txt">
                    <p class="description" id="tagline-description">
                        <b class="text-danger"><?php esc_html_e('Important', 'lmfwc'); ?>:</b>
                        <span><?php esc_html_e('One line per key. Keys must be decrypted.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- VALID FOR -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Validity', 'lmfwc');?></label></th>
                <td>
                    <input name="valid_for" id="valid_for" class="regular-text" type="text">
                    <p class="description" id="tagline-description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- ACTIVATION STATUS -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Activate licenses', 'lmfwc');?></label></th>
                <td>
                    <label for="activate">
                        <input name="activate" id="activate" class="regular-text" type="checkbox" checked="checked">
                        <span><?php esc_html_e('Activate licenses immediately after import.', 'lmfwc');?></span>
                    </label>
                    <p class="description" id="tagline-description">
                        <span><?php esc_html_e('Activated licenses are immediately availabale for sale, while inactive licenses must be activated manually.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- PRODUCT -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Product', 'lmfwc');?></label></th>
                <td>
                    <?php if ($products->have_posts()): ?>
                        <select name="product" id="product">
                            <option value=""><?php esc_html_e('Select a product...');?></option>
                            <?php foreach($products->posts as $product): ?>
                                <option value="<?=$product->ID;?>"><?=$product->post_title;?></option>
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

    <p class="submit">
        <input name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Import' ,'lmfwc');?>" type="submit">
    </p>
</form>

<h1><?php esc_html_e('Add License Key', 'lmfwc'); ?></h1>

<form method="post" action="<?php echo esc_html(admin_url('admin-post.php'));?>">

    <input type="hidden" name="action" value="lmfwc_add_license_key">
    <?php wp_nonce_field('lmfwc_add_license_key'); ?>

    <table class="form-table">
        <tbody>
            <!-- LICENCE KEY -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Licence Key', 'lmfwc');?></label></th>
                <td>
                    <input name="license_key" id="license_key" class="regular-text" type="text">
                    <p class="description" id="tagline-description"><?php esc_html_e('The license key will be encrypted before it is stored inside the database.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- VALID FOR -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Validity', 'lmfwc');?></label></th>
                <td>
                    <input name="valid_for" id="valid_for" class="regular-text" type="text">
                    <p class="description" id="tagline-description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- ACTIVATION STATUS -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Activate licenses', 'lmfwc');?></label></th>
                <td>
                    <label for="activate">
                        <input name="activate" id="activate" class="regular-text" type="checkbox" checked="checked">
                        <span><?php esc_html_e('Activate license immediately after import.', 'lmfwc');?></span>
                    </label>
                    <p class="description" id="tagline-description">
                        <span><?php esc_html_e('Activated licenses are immediately availabale for sale, while inactive licenses must be activated manually.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- FILE -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Product', 'lmfwc');?></label></th>
                <td>
                    <?php if ($products->have_posts()): ?>

                        <select name="product" id="product">
                            <option value=""><?php esc_html_e('Select a product...');?></option>
                            <?php foreach ($products->posts as $product): ?>
                                <option value="<?=$product->ID;?>"><?=$product->post_title;?></option>
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

    <p class="submit">
        <input name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Add' ,'lmfwc');?>" type="submit">
    </p>
</form>
