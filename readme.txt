=== Shipping Nova Poshta for WooCommerce ===
Contributors: wppunk, seredniy
Donate link: https://www.liqpay.ua/api/3/checkout?data=eyJ2ZXJzaW9uIjozLCJhY3Rpb24iOiJwYXlkb25hdGUiLCJwdWJsaWNfa2V5IjoiaTM0ODU5MzcyNjEwIiwiYW1vdW50IjoiMCIsImN1cnJlbmN5IjoiVUFIIiwiZGVzY3JpcHRpb24iOiLQodC%2F0LDRgdC40LHQviDQsNCy0YLQvtGA0YMg0LfQsCBTaGlwcGluZyBOb3ZhIFBvc2h0YSBmb3IgV29vQ29tbWVyY2UiLCJ0eXBlIjoiZG9uYXRlIiwibGFuZ3VhZ2UiOiJydSJ9&signature=rGy8tJ7N1bDPT8o0wxvI0G59vRw%3D
Tags: Нова пошта, новая почта, nova poshta, novaposhta
Requires at least: 5.1
Tested up to: 5.4
Stable tag: 1.2.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integration Nova Poshta API with WooCommerce.

== Description ==

Integration Nova Poshta API with WooCommerce. Add shipping method for WooCommerce. Creating invoice for orders.

= Features =
* Add shipping method for WooCommerce.
* Creating internet documents for orders.
* Update user profile

== Installation ==

1. Upload `shipping-nova-poshta-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How to get a API key? =

You need to enter your [personal account](https://new.novaposhta.ua/)

1. Go to **Settings**
2. Tab a **Security**
3. Press button **Create a key**
4. In popup you click to **Create**
5. You need a copy key with the service *Business cabinet*

[Visual guide](https://github.com/wppunk/shipping-nova-poshta-for-woocommerce/wiki/%D0%9A%D0%B0%D0%BA-%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B8%D1%82%D1%8C-API-%D0%BA%D0%BB%D1%8E%D1%87%3F)

= How to change a recipient city or warehouse? =

1. Go to **Edit order** page
2. Check what you order status *On hold* or *Pending*.
3. In shipping method item click to **Edit** in the right top corner.
4. Update current recipient information
5. Save changes

= How to create an internet document? =

1. Go to **Edit order** page
2. You need to check what in shipping method item has a recipient city and warehouse.
3.
a) In order actions In select choose a create internet document for Nova Poshta.
b) Change order status to processing.
4. Check internet document in shipping method item.

= How to change the plugin? =

Please do not change the code, otherwise it will be lost during the next update. Use hooks instead. We have written [documentation](https://github.com/wppunk/shipping-nova-poshta-for-woocommerce/wiki/%D0%A5%D1%83%D0%BA%D0%B8-%D0%BF%D0%BB%D0%B0%D0%B3%D0%B8%D0%BD%D0%B0) for you with examples. If there is no necessary hook for you, then create a [task](https://wordpress.org/support/plugin/shipping-nova-poshta-for-woocommerce/) and we will do it in the near future.

== Changelog ==

= 1.0.0 =
* Initial release

= 1.1.0 =
* Update translates
* Auto detect user language

= 1.1.1 =
* Update documentation
* Add hooks

= 1.2.0 =
* Clear cache after deactivate plugin
* Delete plugin tables after deactivate plugin
* UX enhancements upon plugin activation

== Upgrade Notice ==

= 1.0.0 =
* Initial release
* Auto detect user language

== Screenshots ==

1. /assets/img/screenshot-1.png