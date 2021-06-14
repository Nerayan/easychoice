<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ProductUnvariable
{
    public static function process()
    {
        if (empty($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'])) {
            return true;
        }

        Logger::logProtocol('maybe unvariable start');

        \wp_suspend_cache_addition(true);

        $_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers']
            = array_unique($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers']);

        foreach ($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'] as $key => $productID) {
            if (!HeartBeat::nextTerm()) {
                Logger::logProtocol('maybe unvariable - progress');

                return false;
            }

            // if product has variations in current exchange
            if (
                isset($_SESSION['IMPORT_1C']['hasVariation']) &&
                isset($_SESSION['IMPORT_1C']['hasVariation'][$productID])
            ) {
                unset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'][$key]);

                continue;
            }

            // product is not variable
            if (!get_post_meta($productID, '_is_set_variable', true)) {
                unset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'][$key]);

                continue;
            }

            Logger::logProtocol(
                '(product) unvariable processing product, ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true)]
            );

            delete_post_meta($productID, '_is_set_variable');

            Term::setObjectTerms($productID, 'simple', 'product_type');
            self::cleanVariations($productID);
            Product::saveMetaValue($productID, '_regular_price', get_post_meta($productID, '_price', true));

            unset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'][$key]);
        }

        Logger::logProtocol('maybe unvariable end');

        \wp_suspend_cache_addition(false);

        return true;
    }

    private static function cleanVariations($productID)
    {
        // https://developer.wordpress.org/reference/functions/wp_parse_id_list/
        $variationIds = wp_parse_id_list(
            get_posts(
                [
                    'post_parent' => $productID,
                    'post_type' => 'product_variation',
                    'fields' => 'ids',
                    'post_status' => ['any', 'trash', 'auto-draft'],
                    'numberposts' => -1
                ]
            )
        );

        if (!empty($variationIds)) {
            foreach ($variationIds as $variationId) {
                ProductVariation::remove($variationId);
            }
        } else {
            Logger::logChanges(
                '(product) has no variations',
                [get_post_meta($productID, '_id_1c', true)]
            );
        }

        delete_transient('wc_product_children_' . $productID);
    }
}
