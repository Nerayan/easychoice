<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductVariation;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Parsing offer (`Предложение`) for a variable product.
 */
class OfferVariation
{
    /**
     * @param \SimpleXMLElement $element 'Предложение' node object.
     * @param string $productGuid
     * @param float $rate
     * @param int $postAuthor
     *
     * @return void
     * @throws \Exception
     */
    public static function process(\SimpleXMLElement $element, $productGuid, $rate, $postAuthor)
    {
        $productID = self::getParentProduct($productGuid, $element);

        if (empty($productID)) {
            Logger::logChanges('(variation) Error! Not exists parent product', [(string) $element->Ид]);

            return;
        }

        self::setHasVariationState($productID);

        $variationID = ProductVariation::getIdByMeta((string) $element->Ид);

        // maybe removed variation
        if (\apply_filters('itglx_wc1c_variation_offer_is_removed', false, $element, $variationID, $productID)) {
            if (!empty($variationID)) {
                ProductVariation::remove($variationID, $productID);
            }

            return;
        }

        /*
         * it may be useful to change or add data for the main logic, if it is not possible
         * to do this in 1C, for example, for configuration "Розница", if the characteristics are
         * not unloaded
         */
        $element = apply_filters('itglx_wc1c_variation_offer_xml_data', $element);

        // if something was wrong returned from the filter
        if (!$element instanceof \SimpleXMLElement) {
            return;
        }

        // prevent search variation if not exists
        if (!$variationID) {
            $variationID = \apply_filters('itglx_wc1c_find_product_variation_id', $variationID, $productID, $element);

            if ($variationID) {
                ProductVariation::saveMetaValue($variationID, '_id_1c', (string) $element->Ид, $productID);
            }
        }

        // resolve main variation data
        if (
            ProductVariationAttributes::hasOptions($element) ||
            ProductVariationAttributes::hasCharacteristics($element)
        ) {
            $variationEntry = ProductVariation::mainData(
                $element,
                [
                    'ID' => $variationID,
                    'post_parent' => $productID
                ],
                $postAuthor
            );

            $variationID = !$variationID && !empty($variationEntry['ID']) ? $variationEntry['ID'] : $variationID;
        }

        if (empty($variationID)) {
            Logger::logChanges('(variation) Error! Not exists variation by offer id', [(string) $element->Ид]);

            return;
        }

        if (ProductAndVariationPrices::offerHasPriceData($element)) {
            ProductAndVariationPrices::setPrices(
                ProductAndVariationPrices::resolvePrices($element,$rate),
                $variationID,
                $productID
            );
        }

        if (ProductAndVariationStock::offerHasStockData($element)) {
            if (!\apply_filters('itglx_wc1c_ignore_offer_set_stock_data', false, $variationID, $productID)) {
                ProductAndVariationStock::set($variationID, ProductAndVariationStock::resolve($element), $productID);
            } else {
                Logger::logChanges(
                    '(variation) ignore set stock data by filter - itglx_wc1c_ignore_offer_set_stock_data',
                    [(string) $element->Ид]
                );
            }
        }

        \do_action('itglx_wc1c_after_variation_offer_resolve', $variationID, $productID, $element);
    }

    /**
     * @param string $guid
     * @param \SimpleXMLElement $element
     *
     * @return int|null Product ID or null if there is no product.
     */
    private static function getParentProduct($guid, $element)
    {
        if (!isset($_SESSION['IMPORT_1C']['productParent'])) {
            $_SESSION['IMPORT_1C']['productParent'] = [];
        }

        if (isset($_SESSION['IMPORT_1C']['productParent'][$guid])) {
            return $_SESSION['IMPORT_1C']['productParent'][$guid];
        }

        $productID = Product::getProductIdByMeta($guid);

        if (empty($productID)) {
            $productID = \apply_filters('itglx_wc1c_find_product_id', $productID, $element);

            if ($productID) {
                Product::saveMetaValue($productID, '_id_1c', (string) $guid);
            }
        }

        if (empty($productID)) {
            return null;
        }

        $_SESSION['IMPORT_1C']['productParent'][$guid] = $productID;

        return $productID;
    }

    /**
     * @param int $productID
     *
     * @return void
     */
    private static function setHasVariationState($productID)
    {
        if (!isset($_SESSION['IMPORT_1C']['hasVariation'])) {
            $_SESSION['IMPORT_1C']['hasVariation'] = [];
        }

        $_SESSION['IMPORT_1C']['hasVariation'][$productID] = true;
    }
}
