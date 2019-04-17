<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Edit Generator', 'lmfwc'); ?></h1>
<hr class="wp-header-end">

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="lmfwc_update_generator">
    <input type="hidden" name="id" value="<?php echo esc_html(absint($_GET['id']));?>">
    <?php wp_nonce_field('lmfwc_update_generator'); ?>

    <table class="form-table">
        <tbody>
            <!-- NAME -->
            <tr scope="row">
                <th scope="row">
                    <label for="name"><?php esc_html_e('Name', 'lmfwc');?></label>
                    <span class="text-danger">*</span></label>
                </th>
                <td>
                    <input name="name" id="name" class="regular-text" type="text" value="<?php echo esc_html($generator->name); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Required.', 'lmfwc');?></b>
                        <span><?php esc_html_e('A short name to describe the generator.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- TIMES ACTIVATED MAX -->
            <tr scope="row">
                <th scope="row"><label><?php esc_html_e('Maximum activation count', 'lmfwc');?></label></th>
                <td>
                    <input name="times_activated_max" id="times_activated_max" class="regular-text" type="number" value="<?php echo esc_html($generator->times_activated_max); ?>">
                    <p class="description" id="tagline-description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'lmfwc');?></p>
                </td>
            </tr>

            <!-- CHARSET -->
            <tr scope="row">
                <th scope="row">
                    <label for="charset"><?php esc_html_e('Character map', 'lmfwc');?></label>
                    <span class="text-danger">*</span></label>
                </th>
                <td>
                    <input name="charset" id="charset" class="regular-text" type="text" value="<?php echo esc_html($generator->charset); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Required.', 'lmfwc');?></b>
                        <span><?php _e('The characters which will be used for generating a license key, i.e. for <code>12-AB-34-CD</code> the character map is <code>ABCD1234</code>.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- NUMBER OF CHUNKS -->
            <tr scope="row">
                <th scope="row">
                    <label for="chunks"><?php esc_html_e('Number of chunks', 'lmfwc');?></label>
                    <span class="text-danger">*</span></label>
                </th>
                <td>
                    <input name="chunks" id="chunks" class="regular-text" type="text" value="<?php echo esc_html($generator->chunks); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Required.', 'lmfwc');?></b>
                        <span><?php _e('The number of separated character sets, i.e. for <code>12-AB-34-CD</code> the number of chunks is <code>4</code>.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- CHUNK LENGTH -->
            <tr scope="row">
                <th scope="row">
                    <label for="chunk_length"><?php esc_html_e('Chunk length', 'lmfwc');?></label>
                    <span class="text-danger">*</span></label>
                </th>
                <td>
                    <input name="chunk_length" id="chunk_length" class="regular-text" type="text" value="<?php echo esc_html($generator->chunk_length); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Required.', 'lmfwc');?></b>
                        <span><?php _e('The character length of an individual chunk, i.e. for <code>12-AB-34-CD</code> the chunk length is <code>2</code>.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- SEPARATOR -->
            <tr scope="row">
                <th scope="row"><label for="separator"><?php esc_html_e('Separator', 'lmfwc');?></label></th>
                <td>
                    <input name="separator" id="separator" class="regular-text" type="text" value="<?php echo esc_html($generator->separator); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Optional.', 'lmfwc');?></b>
                        <span><?php _e('The special character separating the individual chunks, i.e. for <code>12-AB-34-CD</code> the separator is <code>-</code>.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- PREFIX -->
            <tr scope="row">
                <th scope="row"><label for="prefix"><?php esc_html_e('Prefix', 'lmfwc');?></label></th>
                <td>
                    <input name="prefix" id="prefix" class="regular-text" type="text" value="<?php echo esc_html($generator->prefix); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Optional.', 'lmfwc');?></b>
                        <span><?php _e('Adds a character set at the start of a license key (separator <b>not</b> included), i.e. for <code>PRE-12-AB-34-CD</code> the prefix is <code>PRE-</code>.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- SUFFIX -->
            <tr scope="row">
                <th scope="row"><label for="suffix"><?php esc_html_e('Suffix', 'lmfwc');?></label></th>
                <td>
                    <input name="suffix" id="suffix" class="regular-text" type="text" value="<?php echo esc_html($generator->suffix); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Optional.', 'lmfwc');?></b>
                        <span><?php _e('Adds a character set at the end of a license key (separator <b>not</b> included), i.e. for <code>12-AB-34-CD-SUF</code> the suffix is <code>-SUF</code>.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>

            <!-- EXPIRES IN -->
            <tr scope="row">
                <th scope="row"><label for="expires_in"><?php esc_html_e('Expires in', 'lmfwc');?></label></th>
                <td>
                    <input name="expires_in" id="expires_in" class="regular-text" type="text" value="<?php echo esc_html($generator->expires_in); ?>">
                    <p class="description" id="tagline-description">
                        <b><?php esc_html_e('Optional.', 'lmfwc');?></b>
                        <span><?php esc_html_e('Number of days for which the license is valid after generation. Leave blank if it doesn\'t expire.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <input name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Update' ,'lmfwc');?>" type="submit">
    </p>

</form>

<h2><?php esc_html_e('Assigned Products', 'lmfwc');?></h2>

<?php if ($products): ?>
    <p><?php esc_html_e('The generator is assigned to the following product(s)', 'lmfwc');?>:</p>

    <ul>
        <?php foreach ($products as $product): ?>
            <li>
                <a href="<?php esc_html_e(get_edit_post_link($product->get_id()));?>">
                    <span><?php esc_html_e($product->get_name());?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><?php esc_html_e('The generator does not have any products assigned to it.', 'lmfwc');?></p>
<?php endif; ?>
