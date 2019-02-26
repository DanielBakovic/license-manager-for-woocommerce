<h2><?php esc_html_e('Key details', 'lmfwc');?></h2>
<hr class="wp-header-end">

<form method="post" action="<?=admin_url('admin-post.php');?>">
    <input type="hidden" name="id" value="<?=$key_id;?>">
    <?php wp_nonce_field('lmfwc-api-key-update'); ?>
    <input type="hidden" name="action" value="<?='lmfwc_api_key_update';?>">
    <input type="hidden" name="lmfwc_action" value="<?php echo esc_attr($action);?>">

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
                        value="<?php echo esc_attr($key_data['description']); ?>"
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
                    <?php wp_dropdown_users(array(
                        'show_option_none' => __('Please select a user...', 'lmfwc'),
                        'show' => 'user_login',
                        'class' => 'regular-text',
                        'name' => 'user',
                        'selected' => $user_id ? $user_id : false
                    )); ?>
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
                        <?php foreach ($permissions as $permission_id => $permission_name) : ?>
                            <option
                                value="<?php echo esc_attr($permission_id); ?>"
                                <?php selected($key_data['permissions'], $permission_id, true); ?>
                            >
                                <span><?php echo esc_html($permission_name); ?></span>
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
                        <code>&hellip;<?php echo esc_html($key_data['truncated_key']); ?></code>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label><?php esc_html_e('Last access', 'lmfwc');?></label>
                    </th>
                    <td>
                        <?php 
                            if (!empty($key_data['last_access'])) {
                                echo esc_html(apply_filters('woocommerce_api_key_last_access_datetime', $date, $key_data['last_access']));
                            } else {
                                esc_html_e('Unknown', 'lmfwc');
                            }
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (0 === intval($key_id)): ?>
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
                                'key' => $key_id
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