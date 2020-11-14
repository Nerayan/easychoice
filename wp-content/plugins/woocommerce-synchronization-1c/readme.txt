=== WooCommerce - 1C - Data Exchange ===
Contributors: https://codecanyon.net/user/itgalaxycompany
Tags: 1c, woocommerce, woocommerce 1c, woocommerce data exchange

== Description ==

The main task of the plugin is to implement the ability to synchronize data between the 1C accounting system and the WooCommerce product catalog.

= Features =

* Create and update categories.
* Create and update product properties and their values.
* Create and update products (and variations, if records are kept on the characteristics), images, prices and stocks.
* Support full exchange or change only.
* Unloading orders.
* Possibility of automatic set of the image category (for the first product with the image).
* Support for the adoption of data in the archive.
* The ability to control the size of the part when transferring files from 1C.
* Support for sequential loading (files received from 1C are processed with control of runtime).
* Ability to select the type of prices (if there are several unloading).
* All settings on the site through the administrative panel.
* Image previews.

== Installation ==

1. Extract `woocommerce-synchronization-1c.zip` and upload it to your `WordPress` plugin directory
(usually /wp-content/plugins ), or upload the zip file directly from the WordPress plugins page.
Once completed, visit your plugins page.
2. Be sure `WooCommerce` Plugin is enabled.
3. Activate the plugin through the `Plugins` menu in WordPress.
4. Go to the `WooCommerce` -> `1C Data Exchange`.
5. Specify login details for authorization 1c.
6. Save settings.
7. Make the exchange setting on the 1c side.

== Changelog ==

= 1.78.3 =
Chore: added new filters `itglx_wc1c_root_image_directory` and `itglx_wc1c_image_path_from_xml`.
Fixed: checking the existence of a media file entry when searching by meta.
Fixed: processing an existing but empty property node.
Feature: ability to write the title of the product in the meta `_wp_attachment_image_alt` and the title of the media file.
Feature: support processing length, width and height not only in the set of requisites, but also in the position `Товар->$node`.
Feature: support for a variant of the scheme, when guid of the characteristic is in a separate node `ИдХарактеристики`, and not in node `Ид` through `#` (offers).
Feature: support for processing multiple nodes `Значение` in node `ЗначенияСвойства` for the main properties of the product.

= 1.74.3 =
Chore: compatibility check with WC 4.6
Chore: added new filter `itglx_wc1c_ignore_offer_set_stock_data`.
Chore: added new filter `itglx_wc1c_attribute_ignore_guid_array`.
Feature: ability to skip stocks processing.
Feature: ability specify currency according to order data (by default the currency is used from the base price type).

= 1.72.1 =
Chore: do not form empty nodes if there is no data (unloading orders).
Feature: ability to skip prices processing.

= 1.71.7 =
Fixed: break step processing logic error.
Chore: added new action `itglx_wc1c_product_or_variation_has_empty_price`.
Chore: added new filter `itglx_wc1c_do_not_delete_images_if_xml_does_not_contain`.
Fixed: the number of units of a product can be 0, which results in a division by zero (unloading orders).
Chore: admin notification in the list of products when there are products with a guid in the trash.
Chore: compatibility check with WC 4.5
Chore: delete meta entry if no real term exists.
Feature: processing of variations in the schema less than 2.04 when the data on characteristics can be in the main product data.

= 1.70.5 =
Fixed: maybe empty group progress.
Chore: compatibility check with WC 4.4
Chore: added new filter `itglx_wc1c_variation_offer_is_removed`.
Fixed: generate unique attribute name.
Chore: compatibility check with WP 5.5
Feature: ability to search for an existing category by name, before creating a new one.

= 1.69.1 =
Chore: search for a product / variation by guid without an object cache, as this consumes a lot of memory in large sizes.
Feature: generating transliterated attribute slugs.
Feature: the ability to combine data on variations and pass it as one line with the main product (unloading orders).
Feature: the ability to set `allow backorders?` variant for product with stock > 0.
Feature: the ability to generate attribute data for variations (if there is no 1C guid) when unloading orders.
Feature: formation of usual slugs for attribute values if possible.
Feature: there can be more than one value for one attribute in a product.

= 1.63.6 =
Chore: disable object cache when `maybe unvariable` process - since a large number of metadata requests consume memory.
Chore: added new filter `itglx_wc1c_stock_status_value_if_not_hide`.
Chore: compatibility check with WC 4.3
Fixed: set onbackorder for simple product (stock <= 0).
Fixed: ability to use the product/variation id from the site (if there is no 1C guid).
Chore: more support in stock xml schemes.
Feature: another option for working with stock <= 0 - "Do not hide and give the opportunity to put in the basket (manage stock - disable, stock status - onbackorder)".
Feature: ability to forming an upload of orders using the mandatory features of the scheme 3.1

= 1.61.1 =
Chore: added new filter `itglx_wc1c_product_is_removed`.
Feature: support for changing in the set of products of the order on the site based on data from 1C.
Feature: ability do not unload orders in selected statuses.
Feature: ability to search for an existing product by SKU, when processing offers data - it may be useful if the unloading nomenclature is not used (only offers).

= 1.58.1 =
Chore: added new filter `itglx_wc1c_parsed_offer_price_value`.
Feature: the ability to enable the creation of products in draft status, instead of published.

= 1.57.2 =
Chore: added new filter `itglx_wc1c_insert_post_new_product_params`.
Chore: compatibility check with WC 4.2
Feature: progress not only in the processing of properties, but also in the processing of values within a property.
Feature: index in tables `postmeta` and `termmeta`, to improve performance.

= 1.55.1 =
Chore: if the order has a delivery method and the delivery address is empty, then add the payer address to the data of the delivery address in the unloading of orders.
Feature: support processing `Изготовитель` as attribute of the product.
Feature: ability to download current temp in zip archive, ability to clear temp through the admin interface.
Fixed: wrong html entity encoding in the name of the products in the unloading of orders.
Feature: support old scheme attributes with tag name `СвойствоНоменклатуры` instead `Свойство`.

= 1.52.1 =
Chore: in the block for unloading orders, add information about how many and which orders will be unloaded at the next request and a link for viewing.
Feature: ability to download current logs in zip archive, ability to clear logs through the admin interface.
Feature: ability to skip product weight and sizes processing.
Chore: do not add address data for empty values when generating order unload data.
Chore: more filters - `itglx_wc1c_hide_variation_by_stock_value`, `itglx_wc1c_hide_product_by_stock_value`, `itglx_wc1c_disable_manage_stock_variation_by_stock_value` and `itglx_wc1c_disable_manage_stock_product_by_stock_value`.
Feature: the ability to use the product/variation id from the site (if there is no 1C guid) for the value in node "Ид" when unloading orders.
Feature: another option for working with stock <= 0 - "Do not hide with a negative stock and give an opportunity to put in a basket, with a zero stock hide and do not give an opportunity to put in a basket.".

= 1.48.1 =
Fixed: compatibility with `WooCommerce` < 3.9
Feature: added new filter `itglx_wc1c_order_xml_contragent_data_array` to modify contragent info.
Feature: `real-time` exchange support for orders unload, if it supports 1C exchange module.
Feature: request processing support - type `sale` mode `info` - status, payment gateway and shipping method lists.
Feature: selection of a set of status of orders at which to transfer requisite `Заказ оплачен` in the value `true`.
Chore: settings page improvement.
Fixed: the price of the products in the order in the unloading of orders, if there is a discount.
Feature: adding `Артикул` to the product in the unloading of orders, if the product was not received from 1C, that is, it does not have a guid.

= 1.43.1 =
Fixed: formation of the name of the contragent `last name + first name` instead of `first name + last name`.
Feature: ability to not change product attribute set for variations.
Feature: sort attributes based on nomenclature category settings.
Feature: ability to not update the category name.

= 1.40.1 =
Fixed: processing mode `deactivate`.
Feature: more supports scheme variants position product stock.
Feature: support for changing the status of the order on the site based on data from 1C.
Feature: support for redefining the name of the status of the order during unloading for 1C.
Feature: more supports scheme variants position product weight.
Feature: support set image for variation, if set for a characteristic in 1C.
Feature: the ability to set the lower limit of the date of creation of the order, which can be unloaded.

= 1.34.6=
Fixed: down start exchange hook priority - after registering additional image sizes.
Fixed: item name order processing.
Fixed: removal of pre-existing attributes of the product, but now missing.
Fixed: progress set variation attributes to parent product.
Chore: use monolog.
Fixed: reset lookup `total_sales` after exchange.
Feature: another option for working with stock <= 0 - "Do not hide, but do not give the opportunity to put in the basket".

= 1.33.1 =
Chore: more customization of the order formation xml process.
Feature: more supports scheme variants position product code.
Feature: ability to choose which value will be recorded in the sku - the code from the requisites or the sku value.
Feature: if there is shipping, then use the real name of the shipping method.

= 1.30.0 =
Feature: ability to not update the product attributes.
Feature: ability to not update the product images.
Fixed: compatibility with `Admin Menu Editor`.
Feature: support for simultaneous exchange with multiple sites when using multisite mode - own exchange directory for each site.

= 1.27.1 =
Fixed: reset lookup `onsale` after exchange.
Feature: ability to change the start date of the upload of orders on the settings page.
Feature: do not erase product links with categories that have been manually added.
Fixed: do not delete tag `featured`.
Feature: support for the strange position of information on characteristics in the nomenclature instead of trading offers.

= 1.24.3 =
Fixed: do not change the description when a short description recording is activated.
Fixed: check, maybe the product property has been manually deleted.
Chore: more logs.
Feature: support processing length, width and height.
Feature: support processing of variations without properties, only with characteristics.
Feature: ability to select the encoding for the response with orders.

= 1.21.1 =
Fixed: (cgi/fcgi) filling in empty variables user and password.
Feature: support for linking multiple categories to one product.
Feature: processing of duplicated products as one of the information on the item, when 1C incorrectly generates data in xml.
Fixed: possible use of image upload error like image.
Fixed: set price / stock for variation at first creation.
Feature: saving all `ЗначениеРеквизита` in the product metadata `_all_product_requisites`.

= 1.18.0 =
Feature: processing of warehouses, as well as additional storage of stocks with separation by warehouses in the metadata of the products, if transferred with separation.
Fixed: set order currency if one price type.
Fixed: order currency is now dependent on the currency of the price type.
Feature: ability to not update the product title.

= 1.16.3 =
Fixed: unlink the category from the media file, when deleting the media file (when manually deleting).
Chore: more hooks to interact with the exchange order process.
Chore: added a filter to search for an existing product category before creating a new one.
Feature: more support in stock xml schemes.
Feature: ability apply price types depending on the amount in the cart.

= 1.14.2 =
Fixed: disable only variation - not parent product.
Fixed: encoding and version when forming a response to a request for orders.
Feature: сlean up missing product variations (optional).

= 1.13.3 =
Fixed: remove product relation old (non exists) variation attributes.
Fixed: disable the variation if it is not in the stock so that the values are not displayed for selection.
Chore: more logs order process.
Feature: ability to skip group processing.
Feature: ability to skip product post content/excerpt processing.

= 1.12.2 =
Fixed: disable stock management if stock 0 and the rule does not hide products.
Fixed: post counters in the term list.
Feature: support for new tag for offer package.

= 1.11.1 =
Fixed: set default `order` meta for terms to sorting.
Feature: ability to display a list of prices on the product page.
Feature: ability to search for an existing product by SKU, before creating a new one.

= 1.9.2 =
Chore: more set stock value logs.
Chore: more hooks to interact with the exchange process.
Feature: support for set sale price.

= 1.8.3 =
Chore: clean product transients after exchange.
Fixed: possible problem when processing variable properties in several variants of schemes.
Fixed: image processing cache based on the hash, since different images may come with the same name.
Feature: processing offer options if the type is not a "Справочник".

= 1.7.1 =
Chore: removed upper and lower limit for values `file limit` and `time limit`.
Feature: optional - write the product description in short description.
Feature: optional - skip products without photo.
Feature: ability to run queries manually.

= 1.4.2 =
Fixed: support new protocol order exchange.
Fixed: processing options if the type is not a "Справочник".
Feature: the ability to use the full description from the "Description file for the site".

= 1.3.5 =
Fixed: set `manage stock` based on setting `WooCommerce`.
Chore: more optimization when handling variations.
Chore: more optimization when handling requisites.

= 1.3.4 =
Fixed: resolve product options for variations for several formats.
Fixed: logic when processing images using the new protocol.
Fixed: save settings.
Fixed: incorrect deletion of old images.

= 1.3.0 =
Feature: support for more than one image (all but the first fall into the gallery).
Feature: the ability to ignore control hash of products by hash from the contents.
Feature: the ability to completely disable the removal of xml files received during the exchange.

= 1.2.0 =
Feature: support new protocol and scheme 3.1.

= 1.1.0 =
Feature: more support in stock xml schemes.

= 1.0.2 =
Fixed: reindex `Relevanssi`.
Fixed: reset cache `wc product lookup`.

= 1.0.0 =
Initial public release.
