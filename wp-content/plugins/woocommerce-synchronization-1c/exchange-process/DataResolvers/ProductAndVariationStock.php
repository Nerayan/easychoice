<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ProductAndVariationStock
{
    /**
     * Parsing information about the stock according to the offer data.
     *
     * @param \SimpleXMLElement $element Node `Предложение` object.
     *
     * @return array The result of parsing the offer stock data, key `_stock` contains (float) the total value of the stock,
     *              and key `_separate_warehouse_stock` contains (array) the stock with separate by warehouses.
     */
    public static function resolve(\SimpleXMLElement $element)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['skip_product_stocks'])) {
            return [
                '_stock' => 0,
                '_separate_warehouse_stock' => []
            ];
        }

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

    /**
     * Resolve of information on stocks with division by warehouses.
     *
     * @param \SimpleXMLElement $element Node `Предложение` object
     *
     * @return array The result of parsing data on the separate of stocks by warehouses. In the form of an array,
     *              where the key is the warehouse id, and the value is the stock in this warehouse.
     */
    public static function resolveSeparate(\SimpleXMLElement $element)
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
            foreach ($element->Остатки->Остаток as $stockElement) {
                if (!isset($stocks[(string) $stockElement->Склад->Ид])) {
                    $stocks[(string) $stockElement->Склад->Ид] = 0;
                }

                $stocks[(string) $stockElement->Склад->Ид] += (float) $stockElement->Склад->Количество;
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

    /**
     * Write of stock values, as well as actions based on the stock.
     *
     * Visibility of product / variation, enabling and disabling stock management.
     *
     * @param int $productId Product ID (if simple) or variation ID.
     * @param array $stockData {@see resolve()}
     * @param bool|int $parentProductID If a simple product, then false, otherwise the product ID of the parent of the variation.
     */
    public static function set($productId, $stockData, $parentProductID = false)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['skip_product_stocks'])) {
            return;
        }

        global $wpdb;

        $products1cStockNull = isset($settings['products_stock_null_rule'])
            ? $settings['products_stock_null_rule']
            : '0';

        Product::saveMetaValue($productId, '_stock', $stockData['_stock'], $parentProductID);
        Product::saveMetaValue($productId, '_separate_warehouse_stock', $stockData['_separate_warehouse_stock'], $parentProductID);

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

            // enable variation
            if ($parentProductID) {
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

            $backorders = $stockData['_stock'] > 0
                ? (
                    empty($settings['products_onbackorder_stock_positive_rule'])
                        ? 'no'
                        : $settings['products_onbackorder_stock_positive_rule']
                ) : null;

            /**
             * Filters the value to allow backorders.
             *
             * @since 1.97.0
             *
             * @param string|null $backorders
             * @param int $productId Product ID (if simple) or variation ID.
             * @param false|int $parentProductID If a simple product, then false, otherwise the product ID of the parent of the variation.
             * @param array $stockData {@see resolve()}
             */
            $backorders = \apply_filters(
                'itglx_wc1c_stock_value_backorders',
                $backorders,
                $productId,
                $parentProductID,
                $stockData
            );

            // set backorders value
            if ($backorders !== null) {
                update_post_meta($productId, '_backorders', $backorders);
            }

            // set stock variation
            if ($parentProductID) {
                $_SESSION['IMPORT_1C']['setTerms'][$parentProductID]['is_visible'] = true;
            }
        } else {
            // disable variation
            if ($parentProductID) {
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

        /**
         * Fires after write of the stock data of the product / variation.
         *
         * By the time of the call, all actions, including setting the visibility and stock managing, have already been performed.
         *
         * @since 1.58.1
         * @since 1.90.1 The `$stockData` parameter was added
         *
         * @param int $productId Product ID (if simple) or variation ID.
         * @param float $stockValue Current stock value.
         * @param bool|int $parentProductID If a simple product, then false, otherwise the product ID of the parent of the variation.
         * @param array $stockData {@see resolve()}
         */
        do_action('itglx_wc1c_after_set_product_stock', $productId, $stockData['_stock'], $parentProductID, $stockData);
    }

    /**
     * The method allows to determine whether the offer contains data on stock.
     *
     * @param \SimpleXMLElement $element Node `Предложение` object
     *
     * @return bool
     */
    public static function offerHasStockData(\SimpleXMLElement $element)
    {
        if (
            isset($element->Остатки) ||
            isset($element->КоличествоНаСкладах) ||
            isset($element->Количество) ||
            // the old exchange may not contain a stock node when the value is 0
            (
                !isset($_GET['version']) &&
                isset($element->Наименование) &&
                ProductAndVariationPrices::offerHasPriceData($element)
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * The method allows to get what status of the stock should be set.
     *
     * @param string $products1cStockNull Rule of operation when the stock is less than or equal to zero.
     * @param array $stockData {@see resolve()}
     *
     * @return string The value of the stock status.
     */
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

    /**
     * The method allows to determine whether a product / variation should be hidden or not.
     *
     * @param string $products1cStockNull Rule of operation when the stock is less than or equal to zero.
     * @param array $stockData
     * @param int $productId Product ID (if simple) or variation ID.
     * @param null|int $parentProductID If a simple product, then null, otherwise the product ID of the parent of the variation.
     *
     * @return bool
     */
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

    /**
     * The method allows to determine whether the stock management for a product / variation should be disabled or not.
     *
     * @param string $products1cStockNull Rule of operation when the stock is less than or equal to zero.
     * @param array $stockData
     * @param int $productId Product ID (if simple) or variation ID.
     * @param bool|int $parentProductID If a simple product, then false, otherwise the product ID of the parent of the variation.
     *
     * @return bool
     */
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
