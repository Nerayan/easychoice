<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SetVariationAttributeToProducts
{
    public static function process()
    {
        if (empty($_SESSION['IMPORT_1C']['setTerms'])) {
            return true;
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        foreach ($_SESSION['IMPORT_1C']['setTerms'] as $productID => $tax) {
            if (!HeartBeat::nextTerm()) {
                Logger::logProtocol('SetVariationAttributeToProducts - progress');

                return false;
            }

            if (isset($_SESSION['IMPORT_1C']['setTerms'][$productID]['is_visible'])) {
                Product::show($productID, true);
            } else {
                Product::hide($productID, true);
            }

            // ignore set attributes if only visible status
            if (count($tax) === 1 && isset($tax['is_visible'])) {
                unset($_SESSION['IMPORT_1C']['setTerms'][$productID]);

                continue;
            }

            $productAttributes = get_post_meta($productID, '_product_attributes', true);

            if (!is_array($productAttributes)) {
                $productAttributes = [];
            }

            $allCurrentVariableTaxes = self::setAttributes($productAttributes, $tax, $productID);

            // remove non exists variation attributes
            $resolvedAttributes = self::removeNonExistsVariationAttributes(
                $productAttributes,
                $allCurrentVariableTaxes,
                $productID
            );

            // clean up missing product variations
            if (
                !empty($settings['remove_missing_variation']) &&
                !empty($_SESSION['IMPORT_1C']['productVariations']) &&
                !empty($_SESSION['IMPORT_1C']['productVariations'][$productID])
            ) {
                self::cleanupMissingProductVariations($productID);
            }

            update_post_meta($productID, '_product_attributes', $resolvedAttributes);

            unset($_SESSION['IMPORT_1C']['setTerms'][$productID]);
        }

        unset($_SESSION['IMPORT_1C']['setTerms']);

        return true;
    }

    private static function setAttributes(&$productAttributes, $taxesInfo, $productID)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $allCurrentVariableTaxes = [];

        foreach ($taxesInfo as $taxonomy => $ids) {
            if ($taxonomy === 'is_visible') {
                continue;
            }

            $allCurrentVariableTaxes[] = $taxonomy;

            // skip updating data on variable attributes if disabled and attributes are already configured
            if (
                isset($productAttributes[$taxonomy]) &&
                !empty($settings['skip_update_set_attribute_for_variations'])
            ) {
                Logger::logChanges(
                    '(product) update set variation attributes skip as is enabled - '
                    . 'skip_update_set_attribute_for_variations, ID - '
                    . $productID,
                    [get_post_meta($productID, '_id_1c', true), $taxonomy]
                );
            } else {
                $productAttributes[$taxonomy] = [
                    'name' => \wc_clean($taxonomy),
                    'value' => '',
                    'position' => 0,
                    'is_visible' => 0,
                    'is_variation' => 1,
                    'is_taxonomy' => 1
                ];

                Logger::logChanges(
                    '(product) Set variation attribute, ID - ' . $productID,
                    [get_post_meta($productID, '_id_1c', true), $taxonomy]
                );
            }

            $ids = array_map('intval', $ids);

            Term::setObjectTerms($productID, $ids, $taxonomy);
            Logger::logChanges(
                '(product) Set attribute terms, ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true), $ids, $taxonomy]
            );
        }

        return $allCurrentVariableTaxes;
    }

    private static function removeNonExistsVariationAttributes($productAttributes, $allCurrentTaxes, $productID)
    {
        $resolvedAttributes = $productAttributes;

        foreach ($productAttributes as $key => $value) {
            if (empty($key)) {
                unset($resolvedAttributes[$key]);

                continue;
            }

            if (!$value['is_variation'] || in_array($key, $allCurrentTaxes, true)) {
                continue;
            }

            unset($resolvedAttributes[$key]);

            Term::setObjectTerms($productID, [], $key);
            Logger::logChanges(
                '(product) Unset variation attribute, ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true), $key]
            );
        }

        return $resolvedAttributes;
    }

    private static function cleanupMissingProductVariations($productID)
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

        Logger::logChanges(
            '(product) Current exchange variation list, ID - ' . $productID,
            [
                get_post_meta($productID, '_id_1c', true),
                json_encode($_SESSION['IMPORT_1C']['productVariations'][$productID])
            ]
        );

        if (!empty($variationIds)) {
            foreach ($variationIds as $variationId) {
                if (in_array($variationId, $_SESSION['IMPORT_1C']['productVariations'][$productID])) {
                    continue;
                }

                Logger::logChanges(
                    '(variation) Removed variation, ID - ' . $variationId,
                    [get_post_meta($variationId, '_id_1c', true)]
                );

                // https://developer.wordpress.org/reference/functions/has_post_thumbnail/
                if (has_post_thumbnail($variationId)) {
                    Product::removeProductImages($variationId);
                }

                // https://developer.wordpress.org/reference/functions/wp_delete_post/
                wp_delete_post($variationId, true);
            }
        }

        delete_transient('wc_product_children_' . $productID);
    }
}
