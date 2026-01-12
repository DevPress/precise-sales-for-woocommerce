=== Precise Sales for WooCommerce ===

Contributors: devpress, downstairsdev
Tags: woocommerce, sales
Requires at least: 6.4
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 8.2
WC requires at least: 8.0.0
WC tested up to: 10.4.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Enables WooCommerce product sale scheduling to the day, hour and minute.

== Description ==

Enables WooCommerce product sale scheduling to the day, hour and minute.

This extends the default WooCommerce functionality which only offers product sale scheduling to the day (in 24 hour increments). This plugin adds a field for hour and minute, so that sales can be scheduled to start and end at a more precise time.

This extension is especially helpful for shops that operate in a country with multiple timezones (like the United States) that want to offer a one day sale across timezones. For those shops, a sale can start at midnight in one timezone and end at midnight in another timezone- allowing a 27 hour sale (or other arbitrary period).

= Credits =

The majority of this code was contributed by [Igor Benic](https://twitter.com/igorbenic) and developed for [Universal Yums](https://www.universalyums.com/). It was converted to a standalone plugin and polished for release by [Devin Price](https://twitter.com/devinsays/).

== Frequently Asked Questions ==

= What timezone is the sale scheduled in? =

The sale schedule uses the default timezone for your WordPress site. You can find this setting under "Settings > General".

= What happens if a sale is scheduled and I disable this plugin? =

By default WooCommerce stores scheduled sale times in a unix timestamp format using the product meta fields `_sale_price_dates_to` and `_sale_price_dates_from`.

This plugin uses the same product meta fields to store the more precise sale timestamp. If the plugin is ever disabled, any previously scheduled sales will still work and use the more precise time that was initially set unless the product is later updated, in which case it will revert to the default date functionality.

== Screenshots ==

1. Time fields added to the WooCommerce product sale schedule settings.

== Changelog ==

= 1.1.0 =

* Updated compatibility for WooCommerce 10.4.3 and WordPress 6.9.
* Updated minimum PHP requirement to 8.2.
* Updated minimum WooCommerce requirement to 8.0.0.
* Fixed text domain for translation strings.
* Improved JavaScript compatibility.
* Added additional unit tests for edge cases.
* Expanded CI test matrix to include PHP 8.x versions.
* Standardized license to GPL-3.0.

= 1.0 =

* Initial release.
