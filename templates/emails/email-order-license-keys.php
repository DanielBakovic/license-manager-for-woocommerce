<?php defined('ABSPATH') || exit; ?>

<h2><?php esc_html_e($heading);?></h2>

<div style="margin-bottom: 40px;">
    <?php foreach ($data as $row): ?>
        <table class="td" cellspacing="0" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <tbody>
                <thead>
                    <tr>
                        <th class="td" scope="col" style="text-align: left;" colspan="2">
                            <span><?php echo esc_html($row['name']); ?></span>
                        </th>
                    </tr>
                </thead>
                <?php foreach ($row['keys'] as $entry): ?>
                    <tr>
                        <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" colspan="<?php echo ($entry->expires_at) ? '1' : '2'; ?>">
                            <code><?php echo esc_html(apply_filters('lmfwc_decrypt', $entry->license_key)) ;?></code>
                        </td>

                        <?php if ($entry->expires_at): ?>
                            <?php $date = new \DateTime($entry->expires_at); ?>
                            <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                                <code><?php printf(__('Valid until <b>%s</b>', 'lmfwc'), $date->format($date_format)); ?></code>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
