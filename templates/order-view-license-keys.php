<?php defined('ABSPATH') || exit; ?>

<h2><?php esc_html_e($heading);?></h2>

<?php foreach ($data as $product_id => $row): ?>
    <table class="shop_table">
        <tbody>
            <thead>
                <tr>
                    <th colspan="2"><?php echo esc_html($row['name']); ?></th>
                </tr>
            </thead>
            <?php foreach ($row['keys'] as $entry): ?>
                <tr>
                    <td colspan="<?php echo ($entry->expires_at) ? '1' : '2'; ?>">
                        <span class="lmfwc-myaccount-license-key"><?php echo esc_html(apply_filters('lmfwc_decrypt', $entry->license_key)); ?></span>
                    </td>
                    <?php if ($entry->expires_at): ?>
                        <?php $date = new \DateTime($entry->expires_at); ?>
                        <td>
                        <span class="lmfwc-myaccount-license-key"><?php
                            printf('%s <b>%s</b>', $valid_until, $date->format($date_format));
                        ?></span>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

