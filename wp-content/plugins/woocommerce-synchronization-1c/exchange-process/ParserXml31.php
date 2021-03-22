<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base\Parser;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\NomenclatureCategories;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\PriceTypes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAndVariationPrices;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductImages;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAndVariationStock;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\GlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Groups;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Stocks;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Units;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\VariationCharacteristicsToGlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductUnvariable;
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
                    if (!Groups::isDisabled() && in_array($reader->name, ['Группы', 'Группа'])) {
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
                                $productEntry['ID'] = Product::getProductIdByMeta((string) $element->Ид, '_id_1c', true);
                                $productEntry['post_parent'] = Product::getProductIdByMeta($parseID[0]);

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

                                    continue;
                                }

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
                                        Product::removeVariation($productEntry['ID'], $productEntry['post_parent']);
                                    }

                                    $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'][] = (string) $element->Ид;

                                    unset($element);
                                    continue;
                                }

                                // resolve main variation data
                                if (
                                    isset($element->ЗначенияСвойств) &&
                                    isset($element->ЗначенияСвойств->ЗначенияСвойства)
                                ) {
                                    $productEntry = Product::mainVariationData(
                                        $element,
                                        $productEntry,
                                        $this->postAuthor
                                    );
                                    // simple variant without ids
                                } elseif (
                                    isset($element->ХарактеристикиТовара) &&
                                    isset($element->ХарактеристикиТовара->ХарактеристикаТовара)
                                ) {
                                    VariationCharacteristicsToGlobalProductAttributes::process($element);

                                    $productEntry = Product::mainVariationData(
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
                                    if (isset($element->Цены)) {
                                        ProductAndVariationPrices::setPrices(
                                            ProductAndVariationPrices::resolvePrices(
                                                $element,
                                                $this->rate
                                            ),
                                            $productEntry['ID'],
                                            $productEntry['post_parent']
                                        );

                                        \WC_Product_Variable::sync($productEntry['post_parent']);
                                    }

                                    if (
                                        isset($element->Остатки) ||
                                        isset($element->КоличествоНаСкладах) ||
                                        isset($element->Количество)
                                    ) {
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

                                            \WC_Product_Variable::sync($productEntry['post_parent']);
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
                                $productId = Product::getProductIdByMeta((string) $element->Ид);

                                // prevent search product if not exists
                                if (!$productId) {
                                    $productId = apply_filters('itglx_wc1c_find_product_id', $productId, $element);

                                    if ($productId) {
                                        Product::saveMetaValue($productId, '_id_1c', (string) $element->Ид);
                                    }
                                }

                                if (empty($productId)) {
                                    Logger::logChanges(
                                        '(product) Error! Not exists product by offer id',
                                        [(string) $element->Ид]
                                    );
                                }

                                if ($productId) {
                                    if (!isset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'])) {
                                        $_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'] = [];
                                    }

                                    $_SESSION['IMPORT_1C_PROCESS']['allCurrentProductIdBySimpleOffers'][] = $productId;

                                    if (isset($element->Цены)) {
                                        ProductAndVariationPrices::setPrices(
                                            ProductAndVariationPrices::resolvePrices(
                                                $element,
                                                $this->rate
                                            ),
                                            $productId
                                        );
                                    }

                                    if (
                                        isset($element->Остатки) ||
                                        isset($element->КоличествоНаСкладах) ||
                                        isset($element->Количество)
                                    ) {
                                        $ignoreSetStockData = apply_filters(
                                            'itglx_wc1c_ignore_offer_set_stock_data',
                                            false,
                                            $productId,
                                            null
                                        );

                                        if (!$ignoreSetStockData) {
                                            ProductAndVariationStock::set(
                                                $productId,
                                                ProductAndVariationStock::resolve($element)
                                            );
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

                            $_SESSION['IMPORT_1C_PROCESS']['allCurrentOffers'][] = (string) $element->Ид;

                            unset($element, $productEntry);
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
