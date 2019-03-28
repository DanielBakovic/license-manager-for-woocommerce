<?php

/**
 * Upgrade
 */
$table_licenses = $wpdb->prefix . \LicenseManagerForWooCommerce\Setup::LICENSES_TABLE_NAME;

if ($wpdb->get_var("SHOW TABLES LIKE '{$table_licenses}'") != $table_licenses) {
    return;
}

if ($migration_mode == 'up') {
    $sql = "
        ALTER TABLE $table_licenses
            ADD COLUMN `times_activated` INT(10) NULL DEFAULT NULL COMMENT 'Number of activations' AFTER `status`,
            ADD COLUMN `times_activated_max` INT(10) NULL DEFAULT NULL COMMENT 'Maximum number of activations' AFTER `times_activated`,
            CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL COMMENT 'Creation timestamp' AFTER `times_activated_max`,
            ADD COLUMN `created_by` BIGINT(20) NULL DEFAULT NULL COMMENT 'Creation User' AFTER `created_at`,
            ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Update timestamp' AFTER `created_by`,
            ADD COLUMN `updated_by` BIGINT(20) NULL DEFAULT NULL COMMENT 'Update user' AFTER `updated_at`;
    ";

    $wpdb->query($sql);
}

/**
 * Downgrade
 */
if ($migration_mode == 'down') {
    $sql = "
        ALTER TABLE {$table_licenses}
            CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL COMMENT 'Creation timestamp' AFTER `hash`,
            DROP COLUMN `times_activated`,
            DROP COLUMN `times_activated_max`,
            DROP COLUMN `created_by`,
            DROP COLUMN `updated_at`,
            DROP COLUMN `updated_by`;
    ";
}