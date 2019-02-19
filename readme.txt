=== License Manager for WooCommerce ===
Contributors: drazenbebic
Tags: license key, license, key, manager, woocommerce
Requires at least: 4.7
Tested up to: 5.0
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily sell and manage software license keys through your WooCommerce shop

== Description ==

The License Manager for WooCommerce allows you to easily sell and manage all of your digital license keys. With features like the bulk importer, automatic delivery, and database encryption, your shop will now run easier than ever.

If you have any feature requests, need more hooks, or maybe have even found a bug, please let us know in the support forum or e-mail us at <licensemanager@bebic.at>. We look forward to hearing from you!

== Installation ==

This section describes how to install the plugin and get it working.

### Manual installation

1. Upload the plugin files to the `/wp-content/plugins/license-manager-for-woocommerce` directory, or install the plugin through the WordPress *Plugins* page directly.
1. Activate the plugin through the *Plugins* page in WordPress.
1. Use the *License Manager* → *Settings* page to configure the plugin.

### Installation through WordPress

1. Open up your WordPress Dashboard and navigate to the *Plugins* page.
1. Click on *Add new*
1. In the search bar type "License Manager for WooCommerce"
1. Select this plugin and click on *Install now"

### Important

The plugin will create two files inside its own `assets/etc` folder. These files (`defuse.txt` and `secret.txt`) contain cryptographic secrets which are automatically generated if they don't exist. These cryptographic secrets are used to encrypt, decrypt and hash your license keys. Once they are generated please **back them up somewhere safe**. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

== Changelog ==

= 1.0.0 - 2019-02-19 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
There is no specific upgrade process for the initial release. Simply install the plugin and you're good to go!