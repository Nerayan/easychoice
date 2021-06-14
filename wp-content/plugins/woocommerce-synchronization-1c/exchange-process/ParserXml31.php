<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base\Parser;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\NomenclatureCategories;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\OfferSimple;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\OfferVariation;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\PriceTypes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductImages;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\GlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Groups;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Stocks;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Units;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductUnvariable;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductVariableSync;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\SetVariationAttributeToProducts;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ParserXml31 extends Parser
{
    public function __construct()
    {
        parent::__construct();

        // setting the flag to enable the saved group list in the option
        Groups::$saveGroupListToOption = true;
    }

    public function parse($filename)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $valid = false;

        $reader = new \XMLReader();
        $reader->open($filename);

        while ($reader->read()) {
            if ($reader->name === 'Каталог' && $this->onlyChanges === '') {
                $this->onlyChanges = $reader->getAttribute('СодержитТолькоИзменения');
            }

            if ($reader->name === 'Классификатор') {
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
                    if (!Groups::isDisabled() && Groups::isGroupNode($reader)) {
                        // time limit check
                        if (!Groups::process($reader)) {
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
                    if (Stocks::isStocksNode($reader)) {
                        Stocks::process($reader);
                    }

                    // resolve `Категории -> Свойства`
                    if (NomenclatureCategories::isNomenclatureCategoriesNode($reader)) {
                        NomenclatureCategories::process($reader);
                    }
                }

                delete_option('product_cat_children');
                wp_cache_flush();
            } // 'Классификатор'


            if ($reader->name === 'Товары') {
                $valid = true;

                $all1cProducts = (array) get_option('all1cProducts');

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

                        if (\has_action('itglx_wc1c_product_custom_processing')) {
                            /**
                             * The action allows to organize custom processing of the product.
                             *
                             * If an action is registered, then it is triggered for every product.
                             *
                             * @since 1.95.0
                             *
                             * @param \SimpleXMLElement $element 'Товар' node object.
                             */
                            \do_action('itglx_wc1c_product_custom_processing', $element);

                            unset($element);
                            continue;
                        }

                        $element = apply_filters('itglx_wc1c_product_xml_data', $element);

                        if (apply_filters('itglx_wc1c_skip_product_by_xml', false, $element)) {
                            unset($element);

                            continue;
                        }

                        // resolve xml id
                        $xmlID = explode('#', (string) $element->Ид);
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
                            $all1cProducts[] = $productEntry['ID'];

                            update_option('all1cProducts', $all1cProducts);

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

                        $all1cProducts[] = $productEntry['ID'];

                        // is new or not disabled image data processing
                        if ($isNewProduct || empty($settings['skip_post_images'])) {
                            $stop = ProductImages::process($element, $productEntry, $this->postAuthor);
                        } else {
                            $stop = false;
                        }

                        do_action('itglx_wc1c_after_product_info_resolve', $productEntry['ID'], $element);

                        update_option('all1cProducts', $all1cProducts);

                        $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'][] = $productEntry['ID'];

                        unset($productEntry, $element);

                        if ($stop) {
                            return false;
                        }
                    }
                }

                delete_option('product_cat_children');
                wp_cache_flush();
            }

            if ($reader->name === 'ПакетПредложений') {
                $this->onlyChanges = $reader->getAttribute('СодержитТолькоИзменения');
                $valid = true;

                if (!isset($_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'])) {
                    $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'] = [];
                }

                if (!isset($_SESSION['IMPORT_1C']['offers_parse'])) {
                    while ($reader->read() &&
                        !($reader->name === 'ПакетПредложений' &&
                            $reader->nodeType === \XMLReader::END_ELEMENT)) {
                        if ($reader->name === 'Предложение' && $reader->nodeType === \XMLReader::ELEMENT) {
                            if (!HeartBeat::next('Предложение', $reader)) {
                                return false;
                            }

                            $element = $reader->readOuterXml();
                            $element = simplexml_load_string(trim($element));

                            if (\has_action('itglx_wc1c_offer_custom_processing')) {
                                /**
                                 * The action allows to organize custom processing of the offer.
                                 *
                                 * If an action is registered, then it is triggered for every offer.
                                 *
                                 * @since 1.95.0
                                 *
                                 * @param \SimpleXMLElement $element 'Предложение' node object.
                                 */
                                \do_action('itglx_wc1c_offer_custom_processing', $element);

                                unset($element);
                                continue;
                            }

                            if (!isset($element->Ид)) {
                                continue;
                            }

                            $element = apply_filters('itglx_wc1c_offer_xml_data', $element);

                            // if duplicate offer
                            if (in_array((string) $element->Ид, $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'])) {
                                continue;
                            }

                            $parseID = explode('#', (string) $element->Ид);

                            // not empty variation hash
                            if (!empty($parseID[1])) {
                                OfferVariation::process($element, $parseID[0], $this->rate, $this->postAuthor);
                            } else {
                                OfferSimple::process($element, $this->rate);
                            }

                            $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'][] = (string) $element->Ид;

                            unset($element);
                        }
                    }

                    $_SESSION['IMPORT_1C']['offers_parse'] = true;
                }

                $baseName = basename(RootProcessStarter::getCurrentExchangeFileAbsPath());

                // rests are the last processing file in protocol - is the stock data
                if (strpos($baseName, 'rests') !== false) {
                    // maybe unvariable
                    $ready = ProductUnvariable::process();

                    if (!$ready) {
                        return false;
                    }

                    $ready = ProductVariableSync::process();

                    if (!$ready) {
                        return false;
                    }
                }

                $ready = SetVariationAttributeToProducts::process();

                if (!$ready) {
                    return false;
                }
            } // end 'Предложения'
        } // end parse

        \wp_defer_term_counting(false);

        return $valid;
    }
}
