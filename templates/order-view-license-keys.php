<?php defined('ABSPATH') || exit; ?>

<h2><?php esc_html_e('Your license key(s)', 'lmfwc');?></h2>

<?php foreach ($data as $product_id => $row): ?>
    <table class="shop_table">
        <tbody>
            <thead>
                <tr>
                    <th><?php echo esc_html($row['name']); ?></th>
                </tr>
            </thead>
            <?php foreach ($row['keys'] as $entry): ?>
                <tr>
                    <td>
                        <span class="lmfwc-myaccount-license-key"><?php echo esc_html(apply_filters('lmfwc_decrypt', $entry->license_key)); ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

