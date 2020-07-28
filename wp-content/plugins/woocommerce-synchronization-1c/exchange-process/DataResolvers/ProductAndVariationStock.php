<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ProductAndVariationStock
{
    public static function resolve($element)
    {
        $stock = 0;

        if (
            isset($element->КоличествоНаСкладах) &&
            isset($element->КоличествоНаСкладах->КоличествоНаСкладе)
        ) {
            foreach ($element->КоличествоНаСкладах->КоличествоНаСкладе as $store) {
                $stock += (float) $store->Количество;
            }
            // schema 3.1
        } elseif (
            isset($element->Остатки) &&
            isset($element->Остатки->Остаток)
        ) {
            foreach ($element->Остатки->Остаток as $stockElement) {
                if (isset($stockElement->Склад)) {
                    $stock += (float) $stockElement->Склад->Количество;
                } elseif (isset($stockElement->Количество)) {
                    $stock += (float) $stockElement->Количество;
                }
            }
        } elseif (isset($element->Количество)) {
            $stock = (float) $element->Количество;
        } elseif (isset($element->Склад)) {
            foreach ($element->Склад as $store) {
                $stock += (float) $store['КоличествоНаСкладе'];
            }
        }

        return [
            '_stock' => $stock,
            '_separate_warehouse_stock' => self::resolveSeparate($element)
        ];
    }

    public static function resolveSeparate($element)
    {
        $stocks = [];

        if (isset($element->Склад)) {
            foreach ($element->Склад as $store) {
                if (!isset($stocks[(string) $store['ИдСклада']])) {
                    $stocks[(string) $store['ИдСклада']] = 0;
                }

                $stocks[(string) $store['ИдСклада']] += (float) $store['КоличествоНаСкладе'];
            }
        } elseif (isset($element->Склады)) {
            foreach ($element->Склады as $store) {
                if (!isset($stocks[(string) $store['ИдСклада']])) {
                    $stocks[(string) $store['ИдСклада']] = 0;
                }

                $stocks[(string) $store['ИдСклада']] += (float) $store['КоличествоНаСкладе'];
            }
            // schema 3.1
        } elseif (
            isset($element->Остатки) &&
            isset($element->Остатки->Остаток) &&
            isset($element->Остатки->Остаток->Склад)
        ) {
            foreach ($element->Остатки->Остаток->Склад as $stockElement) {
                if (!isset($stocks[(string) $stockElement->Ид])) {
                    $stocks[(string) $stockElement->Ид] = 0;
                }

                $stocks[(string) $stockElement->Ид] += (float) $stockElement->Количество;
            }
        } elseif (
            isset($element->КоличествоНаСкладах) &&
            isset($element->КоличествоНаСкладах->КоличествоНаСкладе)
        ) {
            foreach ($element->КоличествоНаСкладах->КоличествоНаСкладе as $stockElement) {
                $stockID = isset($stockElement->ИдСклада)
                    ? (string) $stockElement->ИдСклада
                    : (string) $stockElement->Ид;

                if (!isset($stocks[$stockID])) {
                    $stocks[$stockID] = 0;
                }

                $stocks[$stockID] += (float) $stockElement->Количество;
            }
        }

        return $stocks;
    }

    public static function set($productId, $stockData, $parentProductID = false)
    {
        global $wpdb;

        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $products1cStockNull = isset($settings['products_stock_null_rule'])
            ? $settings['products_stock_null_rule']
            : '0';

        update_post_meta($productId, '_stock', $stockData['_stock']);
        update_post_meta($productId, '_separate_warehouse_stock', $stockData['_separate_warehouse_stock']);

        if ($parentProductID) {
            Logger::logChanges(
                '(variation) Updated stock set for ID - '
                . $productId
                . ', parent ID - '
                . $parentProductID,
                [$stockData['_stock'], get_post_meta($productId, '_id_1c', true)]
            );
        } else {
            Logger::logChanges(
                '(product) Updated stock set for ID - '
                . $productId,
                [$stockData['_stock'], get_post_meta($productId, '_id_1c', true)]
            );
        }

        // resolve stock status
        if (!self::resolveHide($products1cStockNull, $stockData, $productId, $parentProductID)) {
            if (self::resolveDisableManageStock($products1cStockNull, $stockData, $productId, $parentProductID)) {
                \update_post_meta($productId, '_manage_stock', 'no');
            } else {
                \update_post_meta(
                    $productId,
                    '_manage_stock',
                    get_option('woocommerce_manage_stock')
                );
            }

            // enable variable
            if ($parentProductID && get_option('woocommerce_manage_stock') === 'yes') {
                $wpdb->update(
                    $wpdb->posts,
                    ['post_status' => 'publish'],
                    ['ID' => $productId]
                );
            }

            Product::show(
                $productId,
                true,
                apply_filters(
                    'itglx_wc1c_stock_status_value_if_not_hide',
                    self::resolveStockStatus($products1cStockNull, $stockData),
                    $stockData['_stock'],
                    $productId,
                    $parentProductID
                )
            );

            // set backorders value
            if ($stockData['_stock'] > 0) {
                update_post_meta(
                    $productId,
                    '_backorders',
                    empty($settings['products_onbackorder_stock_positive_rule'])
                        ? 'no'
                        : $settings['products_onbackorder_stock_positive_rule']
                );
            }

            // set stock variation
            if ($parentProductID) {
                $_SESSION['IMPORT_1C']['setTerms'][$parentProductID]['is_visible'] = true;
            }
        } else {
            if ($parentProductID && get_option('woocommerce_manage_stock') === 'yes') {
                // disable variation
                $wpdb->update(
                    $wpdb->posts,
                    ['post_status' => 'private'],
                    ['ID' => $productId]
                );
            }

            // has logic with $products1cStockNull = 2
            Product::hide($productId, true);
        }

        // fired save product
        $productObject = false;

        if (!$parentProductID) {
            $productObject = \wc_get_product($productId);
        }

        if (
            $productObject &&
            !is_wp_error($productObject) &&
            method_exists($productObject, 'save')
        ) {
            $productObject->save();

            unset($productObject);
        }

        do_action('itglx_wc1c_after_set_product_stock', $productId, $stockData['_stock'], $parentProductID);
    }

    private static function resolveStockStatus($products1cStockNull, $stockData)
    {
        if ($stockData['_stock'] > 0) {
            return 'instock';
        }

        if ($products1cStockNull !== 'not_hide_and_put_basket_with_disable_manage_stock_and_stock_status_onbackorder') {
            return 'instock';
        }

        return 'onbackorder';
    }


    private static function resolveHide($products1cStockNull, $stockData, $productId, $parentProductID = null)
    {
        $hide = true;

        switch ($products1cStockNull) {
            case '0':
                $hide = $stockData['_stock'] <= 0;
                break;
            case '1':
                $hide = false;
                break;
            case 'not_hide_and_put_basket_with_disable_manage_stock_and_stock_status_onbackorder':
                $hide = false;
                break;
            case '2':
                $hide = $stockData['_stock'] <= 0;
                break;
            case 'with_negative_not_hide_and_put_basket_with_zero_hide_and_not_put_basket':
                $hide = $stockData['_stock'] === 0;
                break;
            default:
                // Nothing
                break;
        }

        // if the price is empty, hide in any case
        $hide = !get_post_meta($productId, '_price', true) ? true : $hide;

        if ($parentProductID) {
            $hide = (bool) apply_filters(
                'itglx_wc1c_hide_variation_by_stock_value',
                $hide,
                $stockData['_stock'],
                $productId,
                $parentProductID
            );
        } else {
            $hide = (bool) apply_filters(
                'itglx_wc1c_hide_product_by_stock_value',
                $hide,
                $stockData['_stock'],
                $productId
            );
        }

        return $hide;
    }

    private static function resolveDisableManageStock($products1cStockNull, $stockData, $productId, $parentProductID)
    {
        $disableManageStock = false;

        switch ($products1cStockNull) {
            case '0':
                // Nothing
                break;
            case '1':
                $disableManageStock =  $stockData['_stock'] <= 0;
                break;
            case 'not_hide_and_put_basket_with_disable_manage_stock_and_stock_status_onbackorder':
                $disableManageStock =  $stockData['_stock'] <= 0;
                break;
            case '2':
                // Nothing
                break;
            case 'with_negative_not_hide_and_put_basket_with_zero_hide_and_not_put_basket':
                $disableManageStock =  $stockData['_stock'] < 0;
                break;
            default:
                // Nothing
                break;
        }

        if ($parentProductID) {
            $disableManageStock = (bool) apply_filters(
                'itglx_wc1c_disable_manage_stock_variation_by_stock_value',
                $disableManageStock,
                $stockData['_stock'],
                $productId,
                $parentProductID
            );
        } else {
            $disableManageStock = (bool) apply_filters(
                'itglx_wc1c_disable_manage_stock_product_by_stock_value',
                $disableManageStock,
                $stockData['_stock'],
                $productId
            );
        }

        return $disableManageStock;
    }
}
