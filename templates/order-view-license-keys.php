<?php

use LicenseManagerForWooCommerce\Models\Resources\License;

defined('ABSPATH') || exit; ?>

<h2><?php esc_html_e($heading); ?></h2>

<?php foreach ($data as $productId => $row): ?>
    <table class="shop_table">
        <tbody>
            <thead>
                <tr>
                    <th colspan="2"><?php echo esc_html($row['name']); ?></th>
                </tr>
            </thead>
            <?php
                /** @var License $license */
                foreach ($row['keys'] as $license):
            ?>
                <tr>
                    <td colspan="<?php echo ($license->getExpiresAt()) ? '1' : '2'; ?>">
                        <span class="lmfwc-myaccount-license-key"><?php echo esc_html($license->getDecryptedLicenseKey()); ?></span>
                    </td>
                    <?php if ($license->getExpiresAt()): ?>
                        <?php
                            try {
                                $date = new DateTime($license->getExpiresAt());
                            } catch (Exception $e) {
                            }
                        ?>
                        <td>
                        <span class="lmfwc-myaccount-license-key"><?php
                            printf('%s <b>%s</b>', $validUntil, $date->format($dateFormat));
                        ?></span>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

