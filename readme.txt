=== License Manager for WooCommerce ===
Contributors: drazenbebic
Donate link: https://www.bebic.at/license-manager-for-woocommerce/donate
Tags: license key, license, key, software license, serial key, manager, woocommerce
Requires at least: 4.7
Tested up to: 5.1
Stable tag: 1.2.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily sell and manage software license keys through your WooCommerce shop

== Description ==
The **License Manager for WooCommerce** allows you to easily sell and manage all of your digital license keys. With features like the bulk importer, automatic delivery, and database encryption, your shop will now run easier than ever.

#### Plugin features

* Automatically sell and deliver license keys through WooCommerce
* Add a single license key and assign it to a specific product
* Add multiple license keys (by file upload) and assign them to a specific product
* Manage the status of your license keys
* Create license key generators with custom parameters
* Assign a generator to one (or more!) WooCommerce product(s), these products then automatically create a license key whenever they are sold

#### API

The plugin also offers two additional routes for manipulating licenses and generator resources. These routes are authorized via API keys (generated through the plugin settings) and accessed via the WordPress API. Here you can [download the Postman collection](https://www.bebic.at/assets/lmfwc.postman_collection.json) (v2.1) for these new routes. An extensive [API documentation](https://www.bebic.at/license-manager-for-woocommerce/apidocs) is also available on the plugin homepage.

#### Support

If you have any feature requests, need more hooks, or maybe have even found a bug, please let us know in the support forum or e-mail us at <licensemanager@bebic.at>. We look forward to hearing from you!

#### Important

The plugin will create two files inside the `wp-content/uploads/lmfwc-files` folder. These files (`defuse.txt` and `secret.txt`) contain cryptographic secrets which are automatically generated if they don't exist. These cryptographic secrets are used to encrypt, decrypt and hash your license keys. Once they are generated please **back them up somewhere safe**. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

== Installation ==

#### Manual installation

1. Upload the plugin files to the `/wp-content/plugins/license-manager-for-woocommerce` directory, or install the plugin through the WordPress *Plugins* page directly.
1. Activate the plugin through the *Plugins* page in WordPress.
1. Use the *License Manager* → *Settings* page to configure the plugin.

#### Installation through WordPress

1. Open up your WordPress Dashboard and navigate to the *Plugins* page.
1. Click on *Add new*
1. In the search bar type "License Manager for WooCommerce"
1. Select this plugin and click on *Install now*

#### Important

The plugin will create two files inside the `wp-content/uploads/lmfwc-files` folder. These files (`defuse.txt` and `secret.txt`) contain cryptographic secrets which are automatically generated if they don't exist. These cryptographic secrets are used to encrypt, decrypt and hash your license keys. Once they are generated please **back them up somewhere safe**. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

== Frequently Asked Questions ==

= Is there a documentation? =

Yes, there is! An extensive documentation describing the plugin features and functionality in detail can be found on the [plugin homepage](https://www.bebic.at/license-manager-for-woocommerce).

== Changelog ==

= 1.2.0 - 2019-04-INSERT_DAY =
* Add - You can now define how many times a license key can be activated using the plugin REST API endpoints.
* Add - You can now define how many license keys will be delivered on purchase.
* Add - Variable product support.
* Add - Export license keys feature (CSV/PDF)
* Add - License key activation REST API endpoint.
* Add - License key validation REST API endpoint.
* Enhancement - The old "Add/Import" page has been renamed to "Add license" and reworked with an intuitive GUI.
* Enhancement - Various minor UI improvements across the plugin.
* Tweak - Changes to the REST API response structure.
* Tweak - Changes to the database structure.

= 1.1.3 - 2019-03-24 =
* Fix - On some environments the activate hook wouldn't work properly and the needed cryptographic secrets weren't generated. I negotiated a deal for this not to happen anymore.
* Fix - When going to the REST API settings page you no longer get a 500 error. Once again, my mistake.
* Fix - Removed unused JavaScript code. It was just lurking there for no purpose, at all.

= 1.1.2 - 2019-03-24 =
* Feature - Clicking license keys inside the table now copies them into your clipboard. Cool huh?
* Fix - CSV and TXT upload of license keys now works as expected again. I hope.
* Tweak - Minor UI improvements on the licenses page. I made stuff look cool(er).

= 1.1.1 - 2019-03-23 =
* Fix - The cryptographic secrets were being deleted on plugin update, causing the plugin to become unusable after the 1.1.0 update. I'm really sorry for this one.

= 1.1.0 - 2019-03-23 =
* Feature - Added license and generator api routes. Currently available calls are GET (single/all), POST (create), and PUT (update) for both resources.
* Feature - API Authentication for the new routes. Currently only basic authentication over SSL is supported.
* Feature - Editing license keys is now possible.
* Feature - Added a "valid for" field on the bulk import of license keys.
* Tweak - The plugin now supports license key sizes of up to 255 characters.
* Tweak - Major code restructuring. Laid the foundation for future features.
* Tweak - Reworked the whole plugin to make use of filters and actions.
* Enhancement - Minor visual upgrades across the plugin.

= 1.0.1 - 2019-02-24 =
* Update - WordPress 5.1 compatibility.
* Update - readme.txt

= 1.0.0 - 2019-02-19 =
* Initial release.

== Upgrade Notice ==

= 1.1.1 =
Copy your previously backed up `defuse.txt` and `secret.txt` to the `wp-content/uploads/lmfwc-files/` folder. Overwrite the existing files, as those are incompatible with the keys you already have in your database. If you did not backup these files previously, then you will need to completely delete (not deactivate!) and install the plugin anew.

= 1.0.0 =
There is no specific upgrade process for the initial release. Simply install the plugin and you're good to go!