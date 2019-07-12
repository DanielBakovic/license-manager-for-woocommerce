<?php

/**
 * Upgrade
 */
$table_generators = $wpdb->prefix . \LicenseManagerForWooCommerce\Setup::GENERATORS_TABLE_NAME;
$table_api_keys = $wpdb->prefix . \LicenseManagerForWooCommerce\Setup::API_KEYS_TABLE_NAME;

if ($wpdb->get_var("SHOW TABLES LIKE '{$table_generators}'") != $table_generators) {
    return;
}

if ($migration_mode == 'up') {
    $sql ="
        ALTER TABLE {$table_generators}
            ADD COLUMN `created_at` DATETIME NULL COMMENT 'Creation Date' AFTER `expires_in`,
            ADD COLUMN `created_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `created_at`,
            ADD COLUMN `updated_at` DATETIME NULL COMMENT 'Update Date' AFTER `created_by`,
            ADD COLUMN `updated_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `updated_at`;
    ";

    $wpdb->query($sql);

    $sql ="
        ALTER TABLE {$table_api_keys}
            ADD COLUMN `created_at` DATETIME NULL COMMENT 'Creation Date' AFTER `last_access`,
            ADD COLUMN `created_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `created_at`,
            ADD COLUMN `updated_at` DATETIME NULL COMMENT 'Update Date' AFTER `created_by`,
            ADD COLUMN `updated_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `updated_at`;
    ";

    $wpdb->query($sql);
}

/**
 * Downgrade
 */
if ($migration_mode == 'down') {
    $sql = "
        ALTER TABLE {$table_generators}
            DROP COLUMN `created_at`,
            DROP COLUMN `created_by`,
            DROP COLUMN `updated_at`,
            DROP COLUMN `updated_by`;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$table_api_keys}
            DROP COLUMN `created_at`,
            DROP COLUMN `created_by`,
            DROP COLUMN `updated_at`,
            DROP COLUMN `updated_by`;
    ";

    $wpdb->query($sql);
}