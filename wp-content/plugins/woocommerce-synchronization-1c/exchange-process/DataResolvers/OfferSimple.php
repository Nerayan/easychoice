<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Parsing offer (`Предложение`) for a simple product.
 */
class OfferSimple
{
    /**
     * @param \SimpleXMLElement $element 'Предложение' node object.
     * @param float $rate
     *
     * @return void
     */
    public static function process(\SimpleXMLElement $element, $rate)
    {
        $productId = Product::getProductIdByMeta((string) $element->Ид);

        // prevent search product if not exists
        if (!$productId) {
            $productId = apply_filters('itglx_wc1c_find_product_id', $productId, $element);

            if ($productId) {
                Product::saveMetaValue($productId, '_id_1c', (string) $element->Ид);
            }
        }

        if (empty($productId)) {
            Logger::logChanges('(product) Error! Not exists product by offer id', [(string) $element->Ид]);

            return;
        }

        if (!isset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'])) {
            $_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'] = [];
        }

        $_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'][] = $productId;

        if (ProductAndVariationPrices::offerHasPriceData($element)) {
            ProductAndVariationPrices::setPrices(ProductAndVariationPrices::resolvePrices($element, $rate), $productId);
        }

        if (ProductAndVariationStock::offerHasStockData($element)) {
            if (!apply_filters('itglx_wc1c_ignore_offer_set_stock_data', false, $productId, null)) {
                ProductAndVariationStock::set($productId, ProductAndVariationStock::resolve($element));
            } else {
                Logger::logChanges(
                    '(product) ignore set stock data by filter - itglx_wc1c_ignore_offer_set_stock_data',
                    [(string) $element->Ид]
                );
            }
        }

        do_action('itglx_wc1c_after_product_offer_resolve', $productId, $element);
    }
}
