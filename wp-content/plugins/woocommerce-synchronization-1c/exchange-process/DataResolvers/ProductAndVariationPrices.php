<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ProductAndVariationPrices
{
    public static function resolvePrices($element, $rate)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['skip_product_prices'])) {
            return [
                'regular' => '',
                'all' => []
            ];
        }

        $allPriceTypes = get_option('all_prices_types', []);
        $basePriceType = isset($settings['price_type_1'])
            ? $settings['price_type_1']
            : '';

        $priceValue = false;
        $allPrices = [];

        // if empty or no isset, then use the first
        if (empty($basePriceType) || !isset($allPriceTypes[$basePriceType])) {
            if ($allPriceTypes) {
                $value = array_shift($allPriceTypes);
                $basePriceType = $value['id'];
            }
        }

        if (!$basePriceType || !self::offerHasPriceData($element)) {
            return [
                'regular' => $priceValue,
                'all' => $allPrices
            ];
        }

        foreach ($element->Цены->Цена as $price) {
            $price = (array) $price;

            $value = (float) str_replace(
                [' ', ','],
                ['', '.'],
                (string) $price['ЦенаЗаЕдиницу']
            );
            $value = (float) apply_filters(
                'itglx_wc1c_parsed_offer_price_value',
                $value / (float) $rate,
                (string) $price['ИдТипаЦены']
            );
            $allPrices[(string) $price['ИдТипаЦены']] = $value;

            if ((string) $price['ИдТипаЦены'] === $basePriceType) {
                $priceValue = $value;
            }
        }

        return [
            'regular' => $priceValue,
            'all' => $allPrices
        ];
    }

    public static function setPrices($resolvePrices, $productId, $parentProductID = 0)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['skip_product_prices'])) {
            return;
        }

        $priceWorkRule = !empty($settings['price_work_rule']) ? $settings['price_work_rule'] : 'regular';
        $removeSalePrice = !empty($settings['remove_sale_price']) ? (int) $settings['remove_sale_price'] : '';
        $priceValue = (float) $resolvePrices['regular'];
        $priceType2 = !empty($settings['price_type_2']) ? $settings['price_type_2'] : '';

        Product::saveMetaValue($productId, '_all_prices', $resolvePrices['all'], $parentProductID);

        if (!$priceValue) {
            if ($parentProductID) {
                Logger::logChanges(
                    '(variation) empty price value - skip - for ID - '
                    . $productId
                    . ', parent ID - '
                    . $parentProductID,
                    [$priceValue, get_post_meta($productId, '_id_1c', true)]
                );
            } else {
                Logger::logChanges(
                    '(product) empty price value - skip - for ID - ' . $productId,
                    [$priceValue, get_post_meta($productId, '_id_1c', true)]
                );
            }

            do_action('itglx_wc1c_product_or_variation_has_empty_price', $productId, $parentProductID);
            do_action('itglx_wc1c_after_set_product_price', $productId, $priceValue, $priceWorkRule);

            return;
        }

        Product::saveMetaValue($productId, '_regular_price', $priceValue, $parentProductID);

        if ($parentProductID) {
            Logger::logChanges(
                '(variation) Updated `_regular_price` for ID - '
                . $productId
                . ', parent ID - '
                . $parentProductID,
                [$priceValue, get_post_meta($productId, '_id_1c', true)]
            );
        } else {
            Logger::logChanges(
                '(product) Updated `_regular_price` for ID - ' . $productId,
                [$priceValue, get_post_meta($productId, '_id_1c', true)]
            );
        }

        switch ($priceWorkRule) {
            case 'regular':
                if ($removeSalePrice === 1) {
                    Product::saveMetaValue($productId, '_sale_price', '', $parentProductID);

                    if ($parentProductID) {
                        Logger::logChanges(
                            '(variation) Clean `_sale_price` (as enabled - remove_sale_price) for ID - '
                            . $productId
                            . ', parent ID - '
                            . $parentProductID,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    } else {
                        Logger::logChanges(
                            '(product) Clean `_sale_price` (as enabled - remove_sale_price) for ID - ' . $productId,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    }
                }

                $salePrice = get_post_meta($productId, '_sale_price', true);

                if ((float) $salePrice <= 0) {
                    Product::saveMetaValue($productId, '_price', $priceValue, $parentProductID);

                    if ($parentProductID) {
                        Logger::logChanges(
                            '(variation) Updated `_price` for ID - '
                            . $productId
                            . ', parent ID - '
                            . $parentProductID,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    } else {
                        Logger::logChanges(
                            '(product) Updated `_price` for ID - ' . $productId,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    }
                }

                break;
            case 'regular_and_sale':
                $salePrice = '';

                if (
                    !empty($priceType2) &&
                    !empty($resolvePrices['all'][$priceType2]) &&
                    (float) $resolvePrices['all'][$priceType2] !== (float) $priceValue
                ) {
                    $salePrice = $resolvePrices['all'][$priceType2];
                }

                Product::saveMetaValue($productId, '_sale_price', $salePrice, $parentProductID);

                if ($parentProductID) {
                    Logger::logChanges(
                        '(variation) Updated `_sale_price` for ID - '
                        . $productId
                        . ', parent ID - '
                        . $parentProductID,
                        [$salePrice, get_post_meta($productId, '_id_1c', true)]
                    );
                } else {
                    Logger::logChanges(
                        '(product) Updated `_sale_price` for ID - '
                        . $productId,
                        [$salePrice, get_post_meta($productId, '_id_1c', true)]
                    );
                }

                if ((float) $salePrice <= 0) {
                    Product::saveMetaValue($productId, '_price', $priceValue, $parentProductID);

                    if ($parentProductID) {
                        Logger::logChanges(
                            '(variation) Updated `_price` for ID - '
                            . $productId
                            . ', parent ID - '
                            . $parentProductID,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    } else {
                        Logger::logChanges(
                            '(product) Updated `_price` for ID - ' . $productId,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    }
                } else {
                    Product::saveMetaValue($productId, '_price', $salePrice, $parentProductID);

                    if ($parentProductID) {
                        Logger::logChanges(
                            '(variation) Updated `_price` for ID - '
                            . $productId
                            . ', parent ID - '
                            . $parentProductID,
                            [$salePrice, get_post_meta($productId, '_id_1c', true)]
                        );
                    } else {
                        Logger::logChanges(
                            '(product) Updated `_price` for ID - ' . $productId,
                            [$salePrice, get_post_meta($productId, '_id_1c', true)]
                        );
                    }
                }

                break;
            case 'regular_and_show_list':
            case 'regular_and_show_list_and_apply_price_depend_cart_totals':
                $salePrice = get_post_meta($productId, '_sale_price', true);

                if ((float) $salePrice <= 0) {
                    Product::saveMetaValue($productId, '_price', $priceValue, $parentProductID);

                    if ($parentProductID) {
                        Logger::logChanges(
                            '(variation) Updated `_price` for ID - '
                            . $productId
                            . ', parent ID - '
                            . $parentProductID,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    } else {
                        Logger::logChanges(
                            '(product) Updated `_price` for ID - ' . $productId,
                            [$priceValue, get_post_meta($productId, '_id_1c', true)]
                        );
                    }
                }

                break;
            default:
                // Nothing
                break;
        }

        do_action('itglx_wc1c_after_set_product_price', $productId, $priceValue, $priceWorkRule);
    }

    /**
     * The method allows to determine whether the offer contains data on price.
     *
     * @param \SimpleXMLElement $element Node `Предложение` object
     *
     * @return bool
     */
    public static function offerHasPriceData(\SimpleXMLElement $element)
    {
        if (isset($element->Цены)) {
            return true;
        }

        return false;
    }
}