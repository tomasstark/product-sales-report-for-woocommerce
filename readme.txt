=== Product Sales Report for WooCommerce ===
Contributors: hearken
Tags: woocommerce, sales, report, reporting, export, csv, excel, spreadsheet
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generates a report on individual WooCommerce products sold during a specified time period.

== Description ==

The Product Sales Report plugin generates reports on the quantity and gross sales of individual WooCommerce products sold over a specified date range. Reports can be downloaded in CSV (Comma-Separated Values) format for further analysis in your spreadsheet software, or for import into other software that supports CSV-formatted data files.

Features:

* Use a date range preset, or specify custom start and end dates.
* Report on all products in your store, or limit the report to only products within a single category.
* Customize the report sorting order (sort by Product ID, Quantity Sold, or Gross Sales).

A [pro version](http://hearkenmedia.com/landing-wp-plugin.php?utm_source=product-sales-report&utm_medium=link&utm_campaign=wp-repo-upgrade-link) with the following additional features is also available:

* Report on product variations individually.
* Optionally include products with no sales (note: does not report on individual product variations with no sales).
* Change the names of fields in the report.
* Change the order of the fields/columns in the report.
* Include any custom field defined by WooCommerce or another plugin and associated with a product (note: custom fields associated with individual product variations are not supported at this time).
* Save multiple report presets to save time when generating different reports.
* Send the report as an email attachment.


== Installation ==

1. Click "Plugins" > "Add New" in the WordPress admin menu.
1. Search for "Product Sales Report".
1. Click "Install Now".
1. Click "Activate Plugin".

Alternatively, you can manually upload the plugin to your wp-content/plugins directory.

== Frequently Asked Questions ==

== Screenshots ==

1. Report generation screen
2. Sample output (simulated)

== Changelog ==

= 1.3 =
* Added a View Report option

= 1.2.4 =
* Added a date picker for browsers without support for the HTML5 date input

= 1.2.2 =
* Removed anonymous function to improve compatibility with old versions of PHP

= 1.2.1 =
* Fixed bug affecting products with no categories

= 1.2 =
* Added Product Categories field

= 1.1.7 =
* Added Variation SKU field

= 1.1.6 =
* Added Gross Sales (After Discounts) as sort field

= 1.1.5 =
* Added field for gross sales after discounts

= 1.1.4 =
* Added Pro version info

= 1.1.2 =
* Made report settings persistent (options are saved when a report is generated)

= 1.1.1 =
* Fixed timezone issue affecting the report period

= 1.1 =
* Added checkboxes to select which fields to include in the report
* Added the Product SKU field

= 1.0 =
* Initial release

== Upgrade Notice ==