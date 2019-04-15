<?php defined('ABSPATH') || exit; ?>

<h2><?php esc_html_e($heading);?></h2>

<div style="margin-bottom: 40px;">
    <?php foreach ($data as $row): ?>
        <table class="td" cellspacing="0" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <tbody>
                <thead>
                    <tr>
                        <th class="td" scope="col" style="text-align: left;">
                            <span><?php echo esc_html($row['name']); ?></span>
                        </th>
                    </tr>
                </thead>
                <?php foreach ($row['keys'] as $entry): ?>
                    <tr>
                        <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                            <code><?php echo esc_html(apply_filters('lmfwc_decrypt', $entry->license_key)) ;?></code>

                            <?php if ($entry->expires_at): ?>
                                <?php $date = new \DateTime($entry->expires_at); ?>

                                <div style="font-size: 0.66em; font-weight: 600; margin-top: 5px;">
                                    <span><?php printf(__('Expires at: %s', 'lmfwc'), $date->format($date_format)); ?></span>
                                </div>

                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
