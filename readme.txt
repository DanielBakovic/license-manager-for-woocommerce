=== License Manager for WooCommerce ===
Contributors: drazenbebic
Donate link: https://www.licensemanager.at/donate/
Tags: license key, license, key, software license, serial key, manager, woocommerce, wordpress
Requires at least: 4.7
Tested up to: 5.3
Stable tag: 2.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily sell and manage software license keys through your WooCommerce shop

== Description ==
The **License Manager for WooCommerce** allows you to easily sell and manage all of your digital license keys. With features like the bulk importer, automatic delivery, and database encryption, your shop will now run easier than ever.

#### Key plugin features

* Automatically sell and deliver license keys through WooCommerce
* Manually resend license keys
* Add a single license key and assign it to a specific product
* Add multiple license keys (by file upload) and assign them to a specific product
* Export license keys as PDF or CSV
* Manage the status of your license keys
* Create license key generators with custom parameters
* Assign a generator to one (or more!) WooCommerce product(s), these products then automatically create a license key whenever they are sold

#### API

The plugin also offers additional endpoints for manipulating licenses and generator resources. These routes are authorized via API keys (generated through the plugin settings) and accessed via the WordPress API. An extensive [API documentation](https://www.licensemanager.at/docs/rest-api/v2/) is also available.

#### Support

If you have any feature requests, need more hooks, or maybe have even found a bug, please let us know in the support forum or e-mail us at <support@licensemanager.at>. We look forward to hearing from you!

You can also check out the [documentation pages](https://www.licensemanager.at/docs/handbook/), as they contain the most essential information on what the plugin can do for you.

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

Yes, there is! An extensive documentation describing the plugin features and functionality in detail can be found on the [plugin homepage](https://www.licensemanager.at/docs/).

= What about the API documentation? =

Again, yes! Here you can find the [API Documentation](https://www.licensemanager.at/docs/rest-api/v2/) detailing all the new endpoint requests and responses. Have fun!

== Screenshots ==

1. The license key overview page.
2. Add a single license key.
3. Add multiple license keys in bulk.
4. WooCommerce simple product options.
5. WooCommerce variable product options.
6. The generators overview page.
7. Create a new license key generator.

== Changelog ==

= 2.1.0 - 2019-11-13 =
* Update - WordPress 5.3 compatibility
* Update - WooCommerce 3.8 compatibility
* Add - Introduced a License key meta table, along with add/update/get/delete functions.
* Add - The plugin now checks for duplicates before adding or editing license keys (this also applies to the API).
* Add - Generators can now freely generate license keys and add them directly to the database.
* Add - `lmfwc_rest_api_validation` filter for additional authentication or data validation when using the REST API.
* Add - Field for copy-pasting license keys on the "Import" page.
* Add - "Mark as sold" and "Mark as delivered" bulk actions on the license keys page.
* Add - A new "My license keys" section for customers, under the "My account" page.
* Add - The "Expires at" field can now directly be edited when adding or editing license keys. This also applies to the API.
* Tweak - Code reformat, refactor, and cleanup.
* Fix - Typo on the Settings page (the `v2/licenses/activate/{license-key}` route now displays correctly as a GET route).
* Fix - The `activate` and `deactivate` license key actions now work on the license keys overview.
* Fix - When adding or editing license keys, the "Product" field now also searches product variations.
* Fix - Multiple admin notices can now be displayed at once.
* Fix - Automatic loading of plugin translations.

= 2.0.1 - 2019-09-03 =
* Add - v2/deactivate/{license_key} route for license key deactivation.
* Add - "Clear" functionality to order and product select2 dropdown menus.
* Fix - License key status dropdown order ("Active" is first now).
* Fix - PHP fatal error when deleting license keys.
* Fix - PHP Notices when performing certain operations (license key import, generator delete).
* Fix - "lmfwc_rest_api_pre_response" hook priority is now correctly set to 1.

= 2.0.0 - 2019-08-30 =
* Add - Template override support.
* Add - Select2 dropdown fields for orders and products when adding or editing license keys.
* Add - Search box for license keys. Only accepts the complete license keys, will not find parts of it.
* Add - v2 API routes
* Add - Setting for enabling/disabling specific API routes.
* Add - `lmfwc_rest_api_pre_response` filter, which allows to edit API responses before they are sent out.
* Tweak - Complete code rework.
* Tweak - Reworked v1 API routes (maintaining compatibility)
* Fix - Users can now edit and delete all license keys, even sold/delivered ones.
* Fix - WordPress installations with large numbers of orders/products could not open the add/edit license key page.
* Fix - CSS fallback font for the license key table.
* Fix - "Valid for" text in customer emails/my account no longer shows if the field was empty.

= 1.2.3 - 2019-04-21 =
* Add - Filter to change the "Valid until" text inside the emails (`lmfwc_license_keys_table_valid_until`).
* Fix - Minor CSS fixes.
* Fix - When selling license keys, the "Expires at" field would be set even when not applicable. This does not happen anymore.

= 1.2.2 - 2019-04-19 =
* Add - German plugin translation

= 1.2.1 - 2019-04-18 =
* Fix - "There was a problem adding the license key." error message should not appear any more when adding a license key.

= 1.2.0 - 2019-04-17 =
* Add - You can now define how many times a license key can be activated using the plugin REST API endpoints.
* Add - You can now define how many license keys will be delivered on purchase.
* Add - Variable product support.
* Add - Export license keys feature (CSV/PDF)
* Add - License key activation REST API endpoint.
* Add - License key validation REST API endpoint.
* Add - New WooCommerce Order action to manually send out license keys.
* Add - "Expires on" date to Customer order emails and Customer order page.
* Add - Filter to replace the "Your License Key(s)" text in the customer email and "My account" page (`lmfwc_license_keys_table_heading`).
* Add - Generators now display the number of products to which they are assigned next to their name.
* Enhancement - Various UI improvements across the plugin.
* Tweak - The "Add/Import" button and page have been renamed to "Add license"
* Tweak - The GET license/{id} REST API endpoint now supports the license key as input parameter as well.
* Tweak - Changes to the REST API response structure.
* Tweak - Changes to the database structure.
* Fix - The license key product settings will no longer be lost when using quick edit on products.

= 1.1.4 - 2019-03-30 =
* Fix - Licenses keys will no longer be sent out more than once if you change the order status from "complete" to something else and then back to "complete".

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

= 1.2.1 =
Please deactivate the plugin and reactivate it.

= 1.1.1 =
Copy your previously backed up `defuse.txt` and `secret.txt` to the `wp-content/uploads/lmfwc-files/` folder. Overwrite the existing files, as those are incompatible with the keys you already have in your database. If you did not backup these files previously, then you will need to completely delete (not deactivate!) and install the plugin anew.

= 1.0.0 =
There is no specific upgrade process for the initial release. Simply install the plugin and you're good to go!