<?php

use LicenseManagerForWooCommerce\Models\Resources\ApiKey as ApiKeyResourceModel;

defined('ABSPATH') || exit;

/** @var ApiKeyResourceModel $keyData */

?>

<h2><?php esc_html_e('Key details', 'lmfwc');?></h2>
<hr class="wp-header-end">

<form method="post" action="<?=admin_url('admin-post.php');?>">
    <input type="hidden" name="id" value="<?php esc_html_e($keyId); ?>">
    <?php wp_nonce_field('lmfwc-api-key-update'); ?>
    <input type="hidden" name="action" value="<?='lmfwc_api_key_update';?>">
    <input type="hidden" name="lmfwc_action" value="<?php esc_attr_e($action);?>">

    <table class="form-table">
        <tbody>
            <tr scope="row">
                <th scope="row">
                    <label for="description"><?php esc_html_e('Description', 'lmfwc');?></label>
                    <span class="text-danger">*</span>
                </th>
                <td>
                    <input
                        id="description"
                        class="regular-text"
                        name="description"
                        type="text"
                        value="<?php echo esc_attr($keyData->getDescription()); ?>"
                    >
                    <p class="description">
                        <b><?php esc_html_e('Required.', 'lmfwc');?></b>
                        <span><?php esc_html_e('Friendly name for identifying this key.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>
            <tr scope="row">
                <th scope="row">
                    <label for="user"><?php esc_html_e('User', 'lmfwc');?></label>
                    <span class="text-danger">*</span></label>
                </th>
                <td>
                    <select id="user" class="regular-text" name="user">
                        <option value=""><?php esc_html_e('Please select a user...', 'lmfwc'); ?></option>
                        <?php
                            foreach ($users as $user):
                                $selected = ($userId == $user->ID) ? 'selected="selected"' : '';

                                echo sprintf(
                                    '<option value="%s" %s>%s (#%d - %s)</option>',
                                    $user->ID,
                                    $selected,
                                    $user->user_login,
                                    $user->ID,
                                    $user->user_email
                                );
                            endforeach;
                        ?>
                    </select>
                    <p class="description">
                        <b><?php esc_html_e('Required.', 'lmfwc');?></b>
                        <span><?php esc_html_e('Owner of these keys.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>
            <tr scope="row">
                <th scope="row">
                    <label for="permissions"><?php esc_html_e('Permissions', 'lmfwc');?></label>
                    <span class="text-danger">*</span></label>
                </th>
                <td>
                    <select id="permissions" class="regular-text" name="permissions">
                        <?php foreach ($permissions as $permissionId => $permissionName) : ?>
                            <option
                                value="<?php echo esc_attr($permissionId); ?>"
                                <?php selected($keyData->getPermissions(), $permissionId, true); ?>
                            >
                                <span><?php echo esc_html($permissionName); ?></span>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <b><?php esc_html_e('Required.', 'lmfwc');?></b>
                        <span><?php esc_html_e('Select the access type of these keys.', 'lmfwc');?></span>
                    </p>
                </td>
            </tr>
            <?php if ($action === 'edit'): ?>
                <tr scope="row">
                    <th scope="row">
                        <label><?php esc_html_e('Consumer key ending in', 'lmfwc');?></label>
                    </th>
                    <td>
                        <code>&hellip;<?php echo esc_html($keyData->getTruncatedKey()); ?></code>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label><?php esc_html_e('Last access', 'lmfwc');?></label>
                    </th>
                    <td>
                        <?php 
                            if (!empty($keyData->getLastAccess())) {
                                echo esc_html(apply_filters('woocommerce_api_key_last_access_datetime', $date, $keyData->getLastAccess()));
                            } else {
                                esc_html_e('Unknown', 'lmfwc');
                            }
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (0 === intval($keyId)): ?>
        <?php submit_button(__('Generate API key', 'lmfwc'), 'primary', 'update_api_key'); ?>
    <?php else: ?>
        <p class="submit">
            <?php submit_button(__('Save changes', 'lmfwc'), 'primary', 'update_api_key', false); ?>
            <a
                style="color: #a00; text-decoration: none; margin-left: 10px;"
                href="<?php echo esc_url(
                    wp_nonce_url(
                        add_query_arg(
                            array(
                                'action' => 'revoke',
                                'key' => $keyId
                            ),
                            sprintf(
                                admin_url('admin.php?page=%s&tab=rest_api'),
                                \LicenseManagerForWooCommerce\AdminMenus::SETTINGS_PAGE
                            )
                        ),
                        'revoke'
                    )
                );?>"
            >
                <span><?php esc_html_e('Revoke key', 'lmfwc'); ?></span>
            </a>
        </p>
    <?php endif; ?>
</form>