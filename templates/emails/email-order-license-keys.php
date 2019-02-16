<?php defined('ABSPATH') || exit(); ?>

<h2><?=esc_html_e('Your License Key(s)', 'lima');?></h2>

<div style="margin-bottom: 40px;">
    <?php foreach ($data as $row): ?>
        <table class="td" cellspacing="0" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <tbody>
                <thead>
                    <tr>
                        <th class="td" scope="col" style="text-align: left;">
                            <span><?=$row['name']?></span>
                        </th>
                    </tr>
                </thead>
                <?php foreach ($row['keys'] as $entry): ?>
                    <tr>
                        <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                            <code><?=$this->crypto->decrypt($entry->license_key);?></code>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
