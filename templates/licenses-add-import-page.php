<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Import Licence Keys', 'lmfwc'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>" enctype="multipart/form-data">

        <input type="hidden" name="action" value="lmfwc_import_license_keys">
        <?php wp_nonce_field('lmfwc-import'); ?>

        <table class="form-table">
            <tbody>
                <!-- FILE -->
                <tr scope="row">
                    <th scope="row"><label><?=__('File (CSV/TXT)', 'lmfwc');?></label></th>
                    <td>
                        <input name="file" id="file" class="regular-text" type="file" accept=".csv,.txt">
                        <p class="description" id="tagline-description">
                            <span><?=__('<b class="text-danger">Important:</b> One line per key. Keys must be un-encrypted.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>

                <!-- ACTIVATION STATUS -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Activate licenses', 'lmfwc');?></label></th>
                    <td>
                        <label for="activate">
                            <input name="activate" id="activate" class="regular-text" type="checkbox" checked="checked">
                            <span><?=__('Activate licenses immediatelly after import.', 'lmfwc');?></span>
                        </label>
                        <p class="description" id="tagline-description">
                            <span><?=__('Activated licenses are immediatelly availabale for sale, while inactive licenses must be activated manually.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>

                <!-- FILE -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Product', 'lmfwc');?></label></th>
                    <td>
                        <?php if($products->have_posts()): ?>
                            <select name="product" id="product">
                                <option value=""><?=__('Select a product...');?></option>
                                <?php foreach($products->posts as $product): ?>
                                    <option value="<?=$product->ID;?>"><?=$product->post_title;?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <label><?=__('You do not have any products defined.', 'lmfwc');?></label>
                        <?php endif; ?>
                        <p class="description" id="tagline-description"><?=__('The product to which the keys will be assigned. You can also leave this blank to manually assign them later.', 'lmfwc');?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input name="submit" id="submit" class="button button-primary" value="<?=__('Import' ,'lmfwc');?>" type="submit">
        </p>
    </form>

    <h1><?=__('Add Licence Key', 'lmfwc'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>">

        <input type="hidden" name="action" value="lmfwc_add_license_key">
        <?php wp_nonce_field('lmfwc-add'); ?>

        <table class="form-table">
            <tbody>
                <!-- LICENCE KEY -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Licence Key', 'lmfwc');?></label></th>
                    <td>
                        <input name="license_key" id="license_key" class="regular-text" type="text">
                        <p class="description" id="tagline-description"><?=__('The license key will be encrypted before saving to the database.', 'lmfwc');?></p>
                    </td>
                </tr>

                <!-- VALID FOR -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Validity', 'lmfwc');?></label></th>
                    <td>
                        <input name="valid_for" id="valid_for" class="regular-text" type="text">
                        <p class="description" id="tagline-description"><?=__('Number of days for which the license key is valid. Leave blank if the license key does not expire.', 'lmfwc');?></p>
                    </td>
                </tr>

                <!-- ACTIVATION STATUS -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Activate licenses', 'lmfwc');?></label></th>
                    <td>
                        <label for="activate">
                            <input name="activate" id="activate" class="regular-text" type="checkbox" checked="checked">
                            <span><?=__('Activate license immediatelly after import.', 'lmfwc');?></span>
                        </label>
                        <p class="description" id="tagline-description">
                            <span><?=__('Activated licenses are immediatelly availabale for sale, while inactive licenses must be activated manually.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>

                <!-- FILE -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Product', 'lmfwc');?></label></th>
                    <td>
                        <?php if($products->have_posts()): ?>
                            <select name="product" id="product">
                                <option value=""><?=__('Select a product...');?></option>
                                <?php foreach($products->posts as $product): ?>
                                    <option value="<?=$product->ID;?>"><?=$product->post_title;?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <label><?=__('You do not have any products defined.', 'lmfwc');?></label>
                        <?php endif; ?>
                        <p class="description" id="tagline-description"><?=__('The product to which the keys will be assigned. You can also leave this blank to manually assign them later.', 'lmfwc');?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input name="submit" id="submit" class="button button-primary" value="<?=__('Add' ,'lmfwc');?>" type="submit">
        </p>
    </form>

</div>