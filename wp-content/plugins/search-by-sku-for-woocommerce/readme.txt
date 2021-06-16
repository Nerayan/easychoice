=== Search by SKU for Woocommerce ===
Contributors: mattsgarage
Donate link: http://www.mattyl.co.uk/donate/
Tags: search, sku, stock keeping unit, woocommerce, ecommerce, e-commerce, commerce, woothemes, wordpress ecommerce
Requires at least: 3.0.1
Tested up to: 5.4.2
Requires PHP: 5.2.4
Stable tag: 0.7.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Extend the search functionality of woocommerce to include searching of sku

== Description ==

The search functionality in woocommerce doesn't search by sku by default.

This simple plugin adds this functionality search facility of your site, in both the public site and admin panel

Just install and activate - no config required.

Tested with Woocommerce 1.5.6, 2.0.7, 2.0.18, 2.2.4, 2.2.10, 3.4.4, 4.0.1 and 4.2.0

See the [Accompanying blog post](http://www.mattyl.co.uk/2012/12/11/woocommerce-plugin-to-search-products-by-sku/ "accompanying blog post") for more information.


== Installation ==

1. Upload `woocommerce-searchbysku.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. No configuration required :)

== Changelog ==
= 0.7.1 =
Testing with Wordpress 5.4.2 and Woocommerce 4.2.0
Remove compatibility warnings from plugin view. Achieved by adding woocommerce metadata flags.
= 0.7.0 =
Fix a long standing bug to search for multiple products. If you search for multiple SKUs at once EG `WB-12, WB-20` you will now search results for both products.
Fix leading or trailing whitespace issue around SKUs
Update contact information of [plugin maintainer - Unroll Digital](https://unrolldigital.com)
= 0.6.3 =
Testing the plugin still works with the latest versions of wordpress (5.4) and woocommerce (4.0.1).
= 0.6.2 =
Testing the plugin still works with the latest versions of wordpress (4.9.8) and woocommerce (3.4.4).
= 0.6.1 =
0.6.0 had issues working with Relevanssi search plugin, this update provides compatibility once again.

* Compatibility with Relevanssi search.
* If you use [Relevanssi](https://wordpress.org/plugins/relevanssi/) and the Search by SKU plugin you may find some of the Woocommerce widgets such as the layered nav do not work 100% correctly.
* Tweak to provide compatibility for older versions of woocommerce. wc_clean() function does not exist in older versions of woocommerce.

If you feel you / your clients have benefited from the plugin, [please consider a donation :)](http://www.mattyl.co.uk/donate/)
= 0.6.0 =
Minor release functionally - Major release development wise :)

* Compatibility with Woocommerce layered nav widget!
* Fix issue with search results appearing in strange places.
* Compatibility with Woocommerce Price filter widget!
* Fix issue with incorrect result count.

This is probably the most significant release of the plugin so far as it addresses most of the issues previously raised, so please keep all bug reports / enhancement suggestions coming :)

If you feel you / your clients have benefited from the plugin, [please consider a donation :)](http://www.mattyl.co.uk/donate/)

= 0.5.2 =
Bug fix release!
0.5.1 removed the plugin when searching in the Wordpress admin as Woocommerce has implemented searching for simple products by SKU.
I didn't realise they had not added searching for variable products by SKU. This release re-enables that feature, other new features:

* Fix long standing bug of the product filtering not playing nicely with the plugin. (Searching within categories etc)
* Handle multiple comma separated skus in search (Admin only)
* Admin search re-implemented to be much closer to Woocommerce search and make more appropriate use of Wordpress filters. (This will make it easier to *hopefully* integrate back into woocommerce core)
* General code tidy up.
= 0.5.1 =
* Disabling SKU search in admin to let Woocommerce handle it.
= 0.5 =
* Improved support for sites running [wpml](http://wpml.org/)
= 0.4 =
* Remove hidden products from search results
* "Total Found" search count works in more themes.
= 0.3 =
* Releasing to wordpress.org

== Frequently Asked Questions ==

= How do I configure the plugin? =

Simply activate in the plugin menu, there is no configuration required :)
