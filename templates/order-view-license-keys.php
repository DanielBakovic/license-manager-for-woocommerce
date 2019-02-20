<?php defined('ABSPATH') || exit; ?>

<h2><?=esc_html_e('Your License Key(s)', 'lmfwc');?></h2>

<?php foreach ($data as $row): ?>
    <table class="shop_table">
        <tbody>
            <thead>
                <tr>
                    <th><?=$row['name']?></th>
                </tr>
            </thead>
            <?php foreach ($row['keys'] as $entry): ?>
                <tr>
                    <td>
                        <span class="lmfwc-myaccount-license-key"><?=$this->crypto->decrypt($entry->license_key);?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

