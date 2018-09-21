<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Import Licence Keys', 'lima'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>" enctype="multipart/form-data">

        <input type="hidden" name="action" value="lima_import_license_keys">
        <?php wp_nonce_field('lima-import'); ?>

        <table class="form-table">
            <tbody>
                <!-- FILE -->
                <tr scope="row">
                    <th scope="row"><label><?=__('File (CSV/TXT)', 'lima');?></label></th>
                    <td>
                        <input name="file" id="file" class="regular-text" type="file" accept=".csv,.txt">
                        <p class="description" id="tagline-description">
                            <span><?=__('<b class="text-danger">Important:</b> One line per key. Keys must be un-encrypted.', 'lima');?></span>
                        </p>
                    </td>
                </tr>

                <!-- ACTIVATION STATUS -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Activate licenses', 'lima');?></label></th>
                    <td>
                        <label for="activate">
                            <input name="activate" id="activate" class="regular-text" type="checkbox" checked="checked">
                            <span><?=__('Activate licenses immediatelly after import.', 'lima');?></span>
                        </label>
                        <p class="description" id="tagline-description">
                            <span><?=__('Activated licenses are immediatelly availabale for sale, while inactive licenses must be activated manually.', 'lima');?></span>
                        </p>
                    </td>
                </tr>

                <!-- FILE -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Product', 'lima');?></label></th>
                    <td>
                        <?php if($products->have_posts()): ?>
                            <select name="product" id="product">
                                <option value=""><?=__('Select a product...');?></option>
                                <?php foreach($products->posts as $product): ?>
                                    <option value="<?=$product->ID;?>"><?=$product->post_title;?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <label><?=__('You do not have any products defined.', 'lima');?></label>
                        <?php endif; ?>
                        <p class="description" id="tagline-description"><?=__('The product to which the keys will be assigned. You can also leave this blank to manually assign them later.', 'lima');?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input name="submit" id="submit" class="button button-primary" value="<?=__('Import' ,'lima');?>" type="submit">
        </p>
    </form>

    <h1><?=__('Add Licence Key', 'lima'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>">

        <input type="hidden" name="action" value="lima_add_license_key">
        <?php wp_nonce_field('lima-add'); ?>

        <table class="form-table">
            <tbody>
                <!-- LICENCE KEY -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Licence Key', 'lima');?></label></th>
                    <td>
                        <input name="license_key" id="license_key" class="regular-text" type="text">
                        <p class="description" id="tagline-description"><?=__('The license key will be encrypted before saving to the database.', 'lima');?></p>
                    </td>
                </tr>

                <!-- VALID FOR -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Validity', 'lima');?></label></th>
                    <td>
                        <input name="valid_for" id="valid_for" class="regular-text" type="text">
                        <p class="description" id="tagline-description"><?=__('Number of days for which the license key is valid. Leave blank if the license key does not expire.', 'lima');?></p>
                    </td>
                </tr>

                <!-- ACTIVATION STATUS -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Activate licenses', 'lima');?></label></th>
                    <td>
                        <label for="activate">
                            <input name="activate" id="activate" class="regular-text" type="checkbox" checked="checked">
                            <span><?=__('Activate license immediatelly after import.', 'lima');?></span>
                        </label>
                        <p class="description" id="tagline-description">
                            <span><?=__('Activated licenses are immediatelly availabale for sale, while inactive licenses must be activated manually.', 'lima');?></span>
                        </p>
                    </td>
                </tr>

                <!-- FILE -->
                <tr scope="row">
                    <th scope="row"><label><?=__('Product', 'lima');?></label></th>
                    <td>
                        <?php if($products->have_posts()): ?>
                            <select name="product" id="product">
                                <option value=""><?=__('Select a product...');?></option>
                                <?php foreach($products->posts as $product): ?>
                                    <option value="<?=$product->ID;?>"><?=$product->post_title;?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <label><?=__('You do not have any products defined.', 'lima');?></label>
                        <?php endif; ?>
                        <p class="description" id="tagline-description"><?=__('The product to which the keys will be assigned. You can also leave this blank to manually assign them later.', 'lima');?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input name="submit" id="submit" class="button button-primary" value="<?=__('Add' ,'lima');?>" type="submit">
        </p>
    </form>

</div>