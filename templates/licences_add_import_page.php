<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Add/Import Licence Keys', 'lima'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>" enctype="multipart/form-data">

        <input type="hidden" name="action" value="LM_import_licence_keys">
        <?php wp_nonce_field('lima-import'); ?>

        <table class="form-table">
            <tbody>
                <tr scope="row">
                    <th scope="row"><label><?=__('File (CSV/TXT)', 'lima');?></label></th>
                    <td>
                        <input name="file" id="file" class="regular-text" type="file" accept=".csv,.txt">
                        <p class="description" id="tagline-description"><?=__('One line per key. Keys must be un-encrypted.', 'lima');?></p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label><?=__('Duplicate licences', 'lima');?></label></th>
                    <td>
                        <label for="duplicate">
                            <input name="duplicate" id="duplicate" class="regular-text" type="checkbox">
                            <span><?=__('Abort if duplicates are found.');?></span>
                        </label>
                        <p class="description" id="tagline-description"><?=__('By selecting this option the whole import process will abort if even a single duplicate is found. Leaving this option unselected will skip duplicate keys.', 'lima');?></p>
                    </td>
                </tr>
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


</div>