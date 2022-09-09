# Precise Sales for WooCommerce ![Testing status](https://github.com/devpress/precise-sales-for-woocommerce/actions/workflows/php-tests.yml/badge.svg?branch=main)

-   Requires PHP: 7.0
-   WP requires at least: 6.0
-   WP tested up to: 6.0
-   WC requires at least: 5.9.1
-   WC tested up to: 6.8.0
-   Stable tag: 1.0.0
-   License: [GPLv3 or later License](http://www.gnu.org/licenses/gpl-3.0.html)

## Description

Enables product sale scheduling to the day, hour and minute in WooCommerce.

![Screenshot of sale settings.](assets/screenshot-1.png?raw=true "Screenshot")

Default WooCommerce functionality only offers scheduling to the day, which means sales are locked into rigid 24 hour increments. This plugin adds a field for hour and minute, so that sales can be scheduled to start and end at a more percise time.

This extension is especially helpful for shops that operate in a country with multiple timezones (like the United States) that want to offer a one day sale. For those shops, a sale can start at midnight in one timezone and end at midnight in another timezone- allowing a 27 hour sale (or other arbitrary period) rather than being locked into increments of 24 hours.

### Additional Information

By default WooCommerce stores scheduled sale times in a unix timestamp format using the product meta fields `_sale_price_dates_to` and `_sale_price_dates_from`.

This plugin uses the same product meta fields to store the more percise sale timestamp. If the plugin is ever disabled, any previously scheduled sales will still work and use the more percise time that was initially set unless the product is later updated, in which case it will revert to the default date functionality.

### Credits

The majority of this code was contributed by [Igor Benic](https://twitter.com/igorbenic) and developed for [Universal Yums](https://www.universalyums.com/). It coverted to a standalone plugin and polished for release by [Devin Price](https://twitter.com/devinsays/).
