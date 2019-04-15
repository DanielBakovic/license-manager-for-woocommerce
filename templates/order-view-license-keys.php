<?php defined('ABSPATH') || exit; ?>

<h2><?php esc_html_e($heading);?></h2>

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

                        <?php if ($entry->expires_at): ?>
                            <?php $date = new \DateTime($entry->expires_at); ?>
                            <br>
                            <small><strong><?php printf(__('Expires at: %s', 'lmwfc'), $date->format($date_format)); ?></strong></small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

