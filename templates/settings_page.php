<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

    <h1><?=__('Settings', 'lima'); ?></h1>

    <form method="post" action="<?=admin_url('admin-post.php');?>">
        <input type="hidden" name="action" value="LM_save_settings">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?=__('Encryption', 'lima');?></th>
                    <td>
                        <fieldset>
                            <label for="lima_encrypt">
                                <input
                                    id="lima_encrypt"
                                    name="lima_encrypt"
                                    type="checkbox"
                                    value="1"
                                    <?=get_option('_lima_encrypt_license_keys') ? 'checked="checked"' : '';?>
                                > <?=
                                __('Encrypt keys before saving in the database.', 'lima');
                            ?></label>
                            <p class="description" id="tagline-description"><?=__('Recommended to turn on. Turning this off will decrypt all currently encrypted keys in the database.', 'lima');?></p>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input name="submit" id="submit" class="button button-primary" value="<?=__('Save' ,'lima');?>" type="submit">
        </p>
    </form>

</div>