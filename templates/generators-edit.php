<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Edit Generator', 'lmfwc'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>">
        <input type="hidden" name="action" value="lmfwc_update_generator">
        <input type="hidden" name="id" value="<?=absint($_GET['id']);?>">
        <?php wp_nonce_field('lmfwc_update_generator'); ?>

        <table class="form-table">
            <tbody>
                <tr scope="row">
                    <th scope="row">
                        <label for="name"><?=__('Name', 'lmfwc');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="name" id="name" class="regular-text" type="text" value="<?=$generator->name;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lmfwc');?></b>
                            <span><?=__('A short name to describe the generator.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label for="charset"><?=__('Character map', 'lmfwc');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="charset" id="charset" class="regular-text" type="text" value="<?=$generator->charset;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lmfwc');?></b>
                            <span><?=__('The characters which will be used for generating a license key, i.e. for <code>12-AB-34-CD</code> the character map is <code>ABCD1234</code>.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label for="chunks"><?=__('Number of chunks', 'lmfwc');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="chunks" id="chunks" class="regular-text" type="text" value="<?=$generator->chunks;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lmfwc');?></b>
                            <span><?=__('The number of separated character sets, i.e. for <code>12-AB-34-CD</code> the number of chunks is <code>4</code>.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label for="chunk_length"><?=__('Chunk length', 'lmfwc');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="chunk_length" id="chunk_length" class="regular-text" type="text" value="<?=$generator->chunk_length;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lmfwc');?></b>
                            <span><?=__('The character length of an individual chunk, i.e. for <code>12-AB-34-CD</code> the chunk length is <code>2</code>.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="separator"><?=__('Separator', 'lmfwc');?></label></th>
                    <td>
                        <input name="separator" id="separator" class="regular-text" type="text" value="<?=$generator->separator;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lmfwc');?></b>
                            <span><?=__('The special character separating the individual chunks, i.e. for <code>12-AB-34-CD</code> the separator is <code>-</code>.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="prefix"><?=__('Prefix', 'lmfwc');?></label></th>
                    <td>
                        <input name="prefix" id="prefix" class="regular-text" type="text" value="<?=$generator->prefix;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lmfwc');?></b>
                            <span><?=__('Adds a character set at the start of a license key (separator <b>not</b> included), i.e. for <code>PRE-12-AB-34-CD</code> the prefix is <code>PRE-</code>.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="suffix"><?=__('Suffix', 'lmfwc');?></label></th>
                    <td>
                        <input name="suffix" id="suffix" class="regular-text" type="text" value="<?=$generator->suffix;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lmfwc');?></b>
                            <span><?=__('Adds a character set at the end of a license key (separator <b>not</b> included), i.e. for <code>12-AB-34-CD-SUF</code> the suffix is <code>-SUF</code>.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="expires_in"><?=__('Expires in', 'lmfwc');?></label></th>
                    <td>
                        <input name="expires_in" id="expires_in" class="regular-text" type="text" value="<?=$generator->expires_in;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lmfwc');?></b>
                            <span><?=__('Number of days for which the license is valid after generation. Leave blank if it doesn\'t expire.', 'lmfwc');?></span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input name="submit" id="submit" class="button button-primary" value="<?=__('Update' ,'lmfwc');?>" type="submit">
        </p>

    </form>

    <h2><?=__('Assigned Products', 'lmfwc');?></h2>

    <?php if ($products): ?>
        <p><?=__('The generator is assigned to the following product(s):', 'lmfwc');?></p>

        <ul>
            <?php foreach ($products as $product): ?>
                <li>
                    <a href="<?=esc_html_e(get_edit_post_link($product->get_id()));?>">
                        <span><?=esc_html_e($product->get_name());?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><?=__('The generator does not have any products assigned to it.', 'lmfwc');?></p>
    <?php endif; ?>

</div>