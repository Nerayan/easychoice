<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base\Parser;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\NomenclatureCategories;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\OfferSimple;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\PriceTypes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAndVariationPrices;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductImages;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAndVariationStock;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\GlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Groups;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductVariationAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Stocks;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Tags;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Units;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductUnvariable;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductVariableSync;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductVariation;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\SetVariationAttributeToProducts;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;

use Itgalaxy\Wc\Exchange1c\Includes\Cron;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ParserXml extends Parser
{
    public function parse($filename)
    {
        global $wpdb;

        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $valid = false;

        $reader = new \XMLReader();
        $reader->open($filename);

        while ($reader->read()) {
            if ($reader->name === 'Каталог' && $this->onlyChanges === '') {
                $this->onlyChanges = $reader->getAttribute('СодержитТолькоИзменения');
            }

            if (
                $reader->name === 'Классификатор' &&
                (
                    !Groups::isParsed() ||
                    !isset($_SESSION['IMPORT_1C']['tagsIsParse']) ||
                    !GlobalProductAttributes::isParsed()
                )
            ) {
                $valid = true;

                $reader->read();

                while (
                    $reader->read() &&
                    !($reader->name === 'Классификатор' && $reader->nodeType === \XMLReader::END_ELEMENT)
                ) {
                    // resolve attributes
                    if (
                        !GlobalProductAttributes::isParsed() &&
                        $reader->name === 'Свойства' &&
                        $reader->nodeType === \XMLReader::ELEMENT &&
                        !$this->isEmptyNode($reader, 'Свойства')
                    ) {
                        GlobalProductAttributes::process($reader);

                        return false;
                    }

                    // resolve groups
                    if (!Groups::isDisabled() && in_array($reader->name, ['Группы', 'Группа'])) {
                        // time limit check
                        if (!Groups::process($reader)) {
                            return false;
                        }
                    }

                    // resolve tags
                    if (in_array($reader->name, ['Метки', 'Метка'])) {
                        if (!Tags::process($reader)) {
                            return false;
                        }
                    }

                    // resolve price types
                    if (PriceTypes::isPriceTypesNode($reader)) {
                        PriceTypes::process($reader);
                    }

                    // resolve units
                    if (Units::isUnitsNode($reader) && !$this->isEmptyNode($reader, 'ЕдиницыИзмерения')) {
                        Units::process($reader);
                    }

                    // resolve stocks
                    if (!Stocks::isParsed() && Stocks::isStocksNode($reader)) {
                        Stocks::process($reader);
                    }

                    // resolve `Категории -> Свойства`
                    if (NomenclatureCategories::isNomenclatureCategoriesNode($reader)) {
                        NomenclatureCategories::process($reader);
                    }
                }

                Groups::setParsed();
                delete_option('product_cat_children');
                wp_cache_flush();
            } // 'Классификатор'

            if ($reader->name === 'Товары') {
                $valid = true;

                if (!isset($_SESSION['IMPORT_1C']['products_parse'])) {
                    if (empty($settings['skip_categories'])) {
                        if (!isset($_SESSION['IMPORT_1C']['categoryIds'])) {
                            $_SESSION['IMPORT_1C']['categoryIds'] = Term::getProductCatIDs();
                            $categoryIds = $_SESSION['IMPORT_1C']['categoryIds'];
                        } else {
                            $categoryIds = $_SESSION['IMPORT_1C']['categoryIds'];
                        }
                    } else {
                        $categoryIds = [];
                    }

                    while (
                        $reader->read() &&
                        !($reader->name === 'Товары' && $reader->nodeType === \XMLReader::END_ELEMENT)
                    ) {
                        if ($reader->name === 'Товар' && $reader->nodeType === \XMLReader::ELEMENT) {
                            if (!HeartBeat::next('Товар', $reader)) {
                                return false;
                            }

                            $element = $reader->readOuterXml();
                            $element = simplexml_load_string(trim($element));
                            $element = apply_filters('itglx_wc1c_product_xml_data', $element);

                            if (apply_filters('itglx_wc1c_skip_product_by_xml', false, $element)) {
                                unset($element);

                                continue;
                            }

                            // resolve xml id
                            $xmlID = explode('#', (string) $element->Ид);
                            $resolveOldVariation = !empty($xmlID[1]);
                            $xmlID = $xmlID[0];

                            $product = Product::getProductIdByMeta($xmlID);

                            // prevent search product if not exists
                            if (!$product) {
                                $product = apply_filters('itglx_wc1c_find_product_id', $product, $element);

                                if ($product) {
                                    Product::saveMetaValue($product, '_id_1c', (string) $element->Ид);
                                }
                            } else {
                                // if duplicate product
                                if (in_array($product, $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'])) {
                                    if (
                                        version_compare($_SESSION['xmlVersion'], '2.04', '<=') &&
                                        $resolveOldVariation
                                    ) {
                                        ProductVariation::resolveOldVariant($element, $this->postAuthor);
                                    }

                                    continue;
                                }
                            }

                            /**
                             * Filters the sign when an product is considered deleted.
                             *
                             * @since 1.61.1
                             *
                             * @param bool $isRemoved
                             * @param \SimpleXMLElement $element
                             * @param int $product
                             *
                             * @see Filters\ProductIsRemoved
                             */
                            if (apply_filters('itglx_wc1c_product_is_removed', false, $element, $product)) {
                                if ($product) {
                                    Product::removeProduct($product);
                                }

                                unset($element);
                                continue;
                            }

                            $productEntry = [
                                'ID' => $product
                            ];

                            $isNewProduct = empty($productEntry['ID']);

                            if (!$isNewProduct) {
                                do_action('itglx_wc1c_before_exists_product_info_resolve', $productEntry['ID'], $element);
                            } else {
                                do_action('itglx_wc1c_before_new_product_info_resolve', $element);
                            }

                            $productHash = md5(json_encode((array) $element));

                            if (
                                !$isNewProduct &&
                                empty($settings['force_update_product']) &&
                                $productHash == get_post_meta($productEntry['ID'], '_md5', true)
                            ) {
                                $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'][] = $productEntry['ID'];

                                if (
                                    !empty($settings['more_check_image_changed']) &&
                                    empty($settings['skip_post_images'])
                                ) {
                                    // it is necessary to check the change of images,
                                    // since the photo can be changed without changing the file name,
                                    // which means the hash matches
                                    $stop = ProductImages::process($element, $productEntry, $this->postAuthor);

                                    if ($stop) {
                                        return false;
                                    }
                                }

                                Logger::logChanges(
                                    '(product) Product not changed - skip, ID - ' . $productEntry['ID'],
                                    [get_post_meta($productEntry['ID'], '_id_1c', true)]
                                );

                                unset($productEntry, $element);

                                continue;
                            }

                            $productEntry = Product::mainProductData(
                                $element,
                                $productEntry,
                                trim(\wp_strip_all_tags((string) $element->Наименование)),
                                $categoryIds,
                                $productHash,
                                $this->postAuthor
                            );

                            if (empty($productEntry)) {
                                unset($productEntry, $element);

                                continue;
                            }

                            if (
                                version_compare($_SESSION['xmlVersion'], '2.04', '<=') &&
                                $resolveOldVariation
                            ) {
                                ProductVariation::resolveOldVariant($element, $this->postAuthor);
                            }

                            // is new or not disabled image data processing
                            if ($isNewProduct || empty($settings['skip_post_images'])) {
                                $stop = ProductImages::process($element, $productEntry, $this->postAuthor);
                            } else {
                                $stop = false;
                            }

                            do_action('itglx_wc1c_after_product_info_resolve', $productEntry['ID'], $element);

                            $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'][] = $productEntry['ID'];

                            unset($productEntry, $element);

                            if ($stop) {
                                return false;
                            }
                        }
                    }

                    // support position Каталог -> СодержитТолькоИзменения
                    /*
                    * Example xml structure
                    * position - Каталог -> СодержитТолькоИзменения
                    *
                        </Товары>
		                <СодержитТолькоИзменения>false</СодержитТолькоИзменения>
                	</Каталог>
                   */

                    // 2 step read as last step was last product
                    $reader->read();
                    $reader->read();

                    if ($reader->name === 'СодержитТолькоИзменения') {
                        $element = $reader->readOuterXml();
                        $element = simplexml_load_string(trim($element));

                        $this->onlyChanges = (string) $element;
                    }

                    $_SESSION['IMPORT_1C']['products_parse'] = true;
                }

                if (
                    isset($_SESSION['IMPORT_1C']['products_parse']) &&
                    !empty($settings['remove_missing_products']) &&
                    $this->onlyChanges === 'false'
                ) {
                    /*------------------REMOVAL OF THE PRODUCTS OUT OF FULL EXCHANGE--------------------------*/
                    if (
                        !isset($_SESSION['IMPORT_1C_PROCESS']['missingProductsIsRemove']) &&
                        !empty($_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'])
                    ) {
                        $productIds = [];
                        $posts = $wpdb->get_results(
                            "SELECT `meta_value`, `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_id_1c'"
                        );

                        foreach ($posts as $post) {
                            $productIds[$post->meta_value] = $post->post_id;
                        }

                        unset($posts);

                        if (!isset($_SESSION['IMPORT_1C_PROCESS']['countProductRemove'])) {
                            $_SESSION['IMPORT_1C_PROCESS']['countProductRemove'] = 0;
                        }

                        $kol = 0;

                        foreach ($productIds as $productID) {
                            if (!HeartBeat::nextTerm()) {
                                return false;
                            }

                            $kol++;

                            if ($kol <= $_SESSION['IMPORT_1C_PROCESS']['countProductRemove']) {
                                continue;
                            }

                            if (!in_array($productID, $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'])) {
                                Product::removeProduct($productID);
                                $kol--;
                            }

                            $_SESSION['IMPORT_1C_PROCESS']['countProductRemove'] = $kol;
                        }

                        $_SESSION['IMPORT_1C_PROCESS']['missingProductsIsRemove'] = true;
                    }
                    /*------------------REMOVAL OF THE PRODUCTS OUT OF FULL EXCHANGE--------------------------*/

                    /*------------------REMOVAL OF THE CATEGORIES OUT OF FULL EXCHANGE--------------------------*/
                    if (
                        !isset($_SESSION['IMPORT_1C_PROCESS']['missingTermsIsRemove']) &&
                        !empty($_SESSION['IMPORT_1C_PROCESS']['currentCategories1c'])
                    ) {
                        if (!isset($_SESSION['IMPORT_1C_PROCESS']['countTermRemove'])) {
                            $_SESSION['IMPORT_1C_PROCESS']['countTermRemove'] = 0;
                        }

                        $kol = 0;

                        foreach (Term::getProductCatIDs() as $id => $category) {
                            if (!HeartBeat::nextTerm()) {
                                return false;
                            }

                            $kol++;

                            if ($kol <= $_SESSION['IMPORT_1C_PROCESS']['countTermRemove']) {
                                continue;
                            }

                            if (
                                \get_term($category, 'product_cat') &&
                                !in_array($id, $_SESSION['IMPORT_1C_PROCESS']['currentCategories1c'])
                            ) {
                                \wp_delete_term($category, 'product_cat');

                                $kol--;
                            }

                            $_SESSION['IMPORT_1C_PROCESS']['countTermRemove'] = $kol;
                        }

                        $_SESSION['IMPORT_1C_PROCESS']['missingTermsIsRemove'] = true;
                    }
                    /*------------------REMOVAL OF THE CATEGORIES OUT OF FULL EXCHANGE--------------------------*/
                }

                delete_option('product_cat_children');
                wp_cache_flush();

                // recalculate product counts
                if (isset($_SESSION['IMPORT_1C']['products_parse'])) {
                    $cron = Cron::getInstance();
                    $cron->createCronTermRecount();
                }

                $ready = SetVariationAttributeToProducts::process();

                if (!$ready) {
                    return false;
                }
            }

            if (in_array($reader->name, ['ПакетПредложений', 'ИзмененияПакетаПредложений'])) {
                $valid = true;

                if (!isset($_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'])) {
                    $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'] = [];
                }

                if (!isset($_SESSION['IMPORT_1C']['offers_parse'])) {
                    while ($reader->read() &&
                        !(in_array($reader->name, ['ПакетПредложений', 'ИзмененияПакетаПредложений']) &&
                            $reader->nodeType === \XMLReader::END_ELEMENT)
                    ) {
                        // resolve price types
                        if (PriceTypes::isPriceTypesNode($reader)) {
                            PriceTypes::process($reader);
                        }

                        // resolve stocks
                        if (!Stocks::isParsed() && Stocks::isStocksNode($reader)) {
                            Stocks::process($reader);
                        }

                        if ($reader->name === 'Предложения') {
                            while (
                                $reader->read() &&
                                !($reader->name === 'Предложения' && $reader->nodeType === \XMLReader::END_ELEMENT)
                            ) {
                                if ($reader->name === 'Предложение' && $reader->nodeType === \XMLReader::ELEMENT) {
                                    if (!HeartBeat::next('Предложение', $reader)) {
                                        return false;
                                    }

                                    $element = $reader->readOuterXml();
                                    $element = simplexml_load_string(trim($element));

                                    if (!isset($element->Ид)) {
                                        continue;
                                    }

                                    $element = apply_filters('itglx_wc1c_offer_xml_data', $element);

                                    // if duplicate offer
                                    if (in_array((string) $element->Ид, $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'])) {
                                        continue;
                                    }

                                    $productEntry = [];
                                    $parseID = explode('#', (string) $element->Ид);

                                    // not empty variation hash
                                    if (!empty($parseID[1])) {
                                        $productEntry['post_parent'] = $this->getVariationParent($parseID[0]);

                                        // prevent search product if not exists
                                        if (!$productEntry['post_parent']) {
                                            $productEntry['post_parent'] = apply_filters(
                                                'itglx_wc1c_find_product_id',
                                                $productEntry['post_parent'],
                                                $element
                                            );

                                            if ($productEntry['post_parent']) {
                                                Product::saveMetaValue($productEntry['post_parent'], '_id_1c', (string) $parseID[0]);
                                            }
                                        }

                                        if (empty($productEntry['post_parent'])) {
                                            Logger::logChanges(
                                                '(variation) Error! Not exists parent product',
                                                [(string) $element->Ид]
                                            );

                                            $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'][] = (string) $element->Ид;

                                            unset($element);
                                            continue;
                                        }

                                        $this->setHasVariationState($productEntry['post_parent']);

                                        $productEntry['ID'] = ProductVariation::getIdByMeta((string) $element->Ид, '_id_1c');

                                        // maybe removed variation
                                        $removed = apply_filters(
                                            'itglx_wc1c_variation_offer_is_removed',
                                            false,
                                            $element,
                                            $productEntry['ID'],
                                            $productEntry['post_parent']
                                        );

                                        if ($removed) {
                                            if (!empty($productEntry['ID'])) {
                                                ProductVariation::remove($productEntry['ID'], $productEntry['post_parent']);
                                            }

                                            $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'][] = (string) $element->Ид;

                                            unset($element);
                                            continue;
                                        }

                                        /*
                                         * it may be useful to change or add data for the main logic, if it is not possible
                                         * to do this in 1C, for example, for configuration "Розница", if the characteristics are
                                         * not unloaded
                                         */
                                        $element = apply_filters('itglx_wc1c_variation_offer_xml_data', $element);

                                        // if something was wrong returned from the filter
                                        if (!$element instanceof \SimpleXMLElement) {
                                            continue;
                                        }

                                        // prevent search variation if not exists
                                        if (!$productEntry['ID']) {
                                            $productEntry['ID'] = apply_filters(
                                                'itglx_wc1c_find_product_variation_id',
                                                $productEntry['ID'],
                                                $productEntry['post_parent'],
                                                $element
                                            );

                                            if ($productEntry['ID']) {
                                                ProductVariation::saveMetaValue($productEntry['ID'], '_id_1c', (string) $element->Ид, $productEntry['post_parent']);
                                            }
                                        }

                                        // resolve main variation data
                                        if (
                                            ProductVariationAttributes::hasOptions($element) ||
                                            ProductVariationAttributes::hasCharacteristics($element)
                                        ) {
                                            $productEntry = ProductVariation::mainData(
                                                $element,
                                                $productEntry,
                                                $this->postAuthor
                                            );
                                        }

                                        if (empty($productEntry['ID'])) {
                                            Logger::logChanges(
                                                '(variation) Error! Not exists variation by offer id',
                                                [(string) $element->Ид]
                                            );
                                        } else {
                                            if (ProductAndVariationPrices::offerHasPriceData($element)) {
                                                ProductAndVariationPrices::setPrices(
                                                    ProductAndVariationPrices::resolvePrices(
                                                        $element,
                                                        $this->rate
                                                    ),
                                                    $productEntry['ID'],
                                                    $productEntry['post_parent']
                                                );
                                            }

                                            if (ProductAndVariationStock::offerHasStockData($element)) {
                                                $ignoreSetStockData = apply_filters(
                                                    'itglx_wc1c_ignore_offer_set_stock_data',
                                                    false,
                                                    $productEntry['ID'],
                                                    $productEntry['post_parent']
                                                );

                                                if (!$ignoreSetStockData) {
                                                    ProductAndVariationStock::set(
                                                        $productEntry['ID'],
                                                        ProductAndVariationStock::resolve($element),
                                                        $productEntry['post_parent']
                                                    );
                                                } else {
                                                    Logger::logChanges(
                                                        '(variation) ignore set stock data by filter - itglx_wc1c_ignore_offer_set_stock_data',
                                                        [(string) $element->Ид]
                                                    );
                                                }
                                            }

                                            do_action(
                                                'itglx_wc1c_after_variation_offer_resolve',
                                                $productEntry['ID'],
                                                $productEntry['post_parent'],
                                                $element
                                            );
                                        }
                                    } else {
                                        OfferSimple::process($element, $this->rate);
                                    }

                                    $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'][] = (string) $element->Ид;

                                    unset($element, $productEntry);
                                }
                            }
                        }
                    }

                    $_SESSION['IMPORT_1C']['offers_parse'] = true;
                }

                // maybe unvariable
                $ready = ProductUnvariable::process();

                if (!$ready) {
                    return false;
                }

                $ready = ProductVariableSync::process();

                if (!$ready) {
                    return false;
                }

                $ready = SetVariationAttributeToProducts::process();

                if (!$ready) {
                    return false;
                }

                // recalculate product cat counts
                $cron = Cron::getInstance();
                $cron->createCronTermRecount();

                // clear sitemap cache
                if (class_exists('\\WPSEO_Sitemaps_Cache')) {
                    remove_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');
                    \WPSEO_Sitemaps_Cache::clear();
                }
            } // end 'Предложения'
        } // end parse

        \wp_defer_term_counting(false);

        return $valid;
    }
}
