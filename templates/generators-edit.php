<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Edit Generator', 'lima'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>">
        <input type="hidden" name="action" value="lima_update_generator">
        <input type="hidden" name="id" value="<?=absint($_GET['id']);?>">
        <?php wp_nonce_field('lima_update_generator'); ?>

        <table class="form-table">
            <tbody>
                <tr scope="row">
                    <th scope="row">
                        <label for="name"><?=__('Name', 'lima');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="name" id="name" class="regular-text" type="text" value="<?=$generator->name;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lima');?></b>
                            <span><?=__('A short name to describe the generator.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label for="charset"><?=__('Character map', 'lima');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="charset" id="charset" class="regular-text" type="text" value="<?=$generator->charset;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lima');?></b>
                            <span><?=__('i.e. for "12-AB-34-CD" the character map is <kbd>ABCD1234</kbd>.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label for="chunks"><?=__('Number of chunks', 'lima');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="chunks" id="chunks" class="regular-text" type="text" value="<?=$generator->chunks;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lima');?></b>
                            <span><?=__('i.e. for "12-AB-34-CD" the number of chunks is <kbd>4</kbd>.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label for="chunk_length"><?=__('Chunk length', 'lima');?></label>
                        <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="chunk_length" id="chunk_length" class="regular-text" type="text" value="<?=$generator->chunk_length;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Required.', 'lima');?></b>
                            <span><?=__('i.e. for "12-AB-34-CD" the chunk length is <kbd>2</kbd>.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="separator"><?=__('Separator', 'lima');?></label></th>
                    <td>
                        <input name="separator" id="separator" class="regular-text" type="text" value="<?=$generator->separator;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lima');?></b>
                            <span><?=__('i.e. for "12-AB-34-CD" the separator is <kbd>-</kbd>.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="prefix"><?=__('Prefix', 'lima');?></label></th>
                    <td>
                        <input name="prefix" id="prefix" class="regular-text" type="text" value="<?=$generator->prefix;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lima');?></b>
                            <span><?=__('Adds a word at the start (separator <b>not</b> included), i.e. <kbd><b>PRE-</b>12-AB-34-CD</kbd>.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="suffix"><?=__('Suffix', 'lima');?></label></th>
                    <td>
                        <input name="suffix" id="suffix" class="regular-text" type="text" value="<?=$generator->suffix;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lima');?></b>
                            <span><?=__('Adds a word at the end (separator <b>not</b> included), i.e. <kbd>12-AB-34-CD<b>-SUF</b></kbd>.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row"><label for="expires_in"><?=__('Expires in', 'lima');?></label></th>
                    <td>
                        <input name="expires_in" id="expires_in" class="regular-text" type="text" value="<?=$generator->expires_in;?>">
                        <p class="description" id="tagline-description">
                            <b><?=__('Optional.', 'lima');?></b>
                            <span><?=__('Number of days for which the license is valid after generation. Leave blank if it doesn\'t expire.', 'lima');?></span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input name="submit" id="submit" class="button button-primary" value="<?=__('Update' ,'lima');?>" type="submit">
        </p>

    </form>

</div>