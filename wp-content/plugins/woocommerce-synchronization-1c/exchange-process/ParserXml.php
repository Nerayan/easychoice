<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\NomenclatureCategories;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\PriceTypes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAndVariationPrices;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductImages;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAndVariationStock;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\GlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Groups;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Stocks;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\VariationCharacteristicsToGlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductUnvariable;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\SetVariationAttributeToProducts;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;

use Itgalaxy\Wc\Exchange1c\Includes\Cron;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ParserXml
{
    private $rate = 1;

    private $postAuthor = 0;

    // true or false
    private $onlyChanges = '';

    public function __construct()
    {
        HeartBeat::start();
    }

    public function parce($filename)
    {
        global $wpdb;

        // https://developer.wordpress.org/reference/functions/wp_defer_term_counting/
        // disable allows to make the exchange much faster, since a large number of resources are saved for
        // each quantity recount, and the final recount is performed through the cron plugin task
        wp_defer_term_counting(true);

        if (class_exists('\\WPSEO_Sitemaps_Cache')) {
            add_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        $this->postAuthor = !empty($settings['exchange_post_author'])
            ? $settings['exchange_post_author']
            : '';

        if (!$this->postAuthor) {
            if ($users = get_users(['role' => 'administrator'])) {
                $this->postAuthor = array_shift($users)->ID;
            } else {
                $this->postAuthor = 1;
            }
        }

        if (!isset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'])) {
            $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'] = [];
        }

        $valid = false;

        $reader = new \XMLReader();
        $reader->open($filename);

        while ($reader->read()) {
            if ($reader->name === 'Каталог' && $this->onlyChanges === '') {
                $this->onlyChanges = $reader->getAttribute('СодержитТолькоИзменения');
            }

            if (
                $reader->name === 'Классификатор' &&
                (!isset($_SESSION['IMPORT_1C']['categoryIsParse']) || !isset($_SESSION['IMPORT_1C']['optionsIsParse']))
            ) {
                $valid = true;

                $processDataGroups = [
                    'numberOfCategories' => 0,
                    'currentCategoryId' => isset($_SESSION['IMPORT_1C']['currentCategoryId'])
                        ? $_SESSION['IMPORT_1C']['currentCategoryId']
                        : 0,
                    'categoryIdStack' => isset($_SESSION['IMPORT_1C']['categoryIdStack'])
                        ? $_SESSION['IMPORT_1C']['categoryIdStack']
                        : []
                ];

                $reader->read();

                while (
                    $reader->read() &&
                    !($reader->name === 'Классификатор' && $reader->nodeType === \XMLReader::END_ELEMENT)
                ) {
                    // resolve attributes
                    if (
                        !isset($_SESSION['IMPORT_1C']['optionsIsParse']) &&
                        $reader->name === 'Свойства' &&
                        $reader->nodeType === \XMLReader::ELEMENT &&
                        str_replace(' ', '', $reader->readOuterXml()) !== '<Свойства/>'
                    ) {
                        GlobalProductAttributes::process($reader);

                        return false;
                    }

                    // resolve groups
                    if (empty($settings['skip_categories']) && in_array($reader->name, ['Группы', 'Группа'])) {
                        $processDataGroups = Groups::process($reader, $processDataGroups);

                        // time limit check
                        if ($processDataGroups === false) {
                            return false;
                        }
                    }

                    // resolve price types
                    if ($reader->name === 'ТипыЦен' && $reader->nodeType !== \XMLReader::END_ELEMENT) {
                        PriceTypes::process($reader);
                    }

                    // resolve stocks
                    if ($reader->name === 'Склады' && $reader->nodeType !== \XMLReader::END_ELEMENT) {
                        Stocks::process($reader);
                    }

                    // resolve `Категории -> Свойства`
                    if ($reader->name === 'Категории' && $reader->nodeType !== \XMLReader::END_ELEMENT) {
                        NomenclatureCategories::process($reader);
                    }
                }

                $_SESSION['IMPORT_1C']['categoryIsParse'] = 'yes';
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
                                    update_post_meta($product, '_id_1c', (string) $element->Ид);
                                }
                            } else {
                                // if duplicate product
                                if (in_array($product, $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'])) {
                                    if ($_SESSION['xmlVersion'] === 2.04 && $resolveOldVariation) {
                                        $this->resolveOldVariation($element);
                                    }

                                    continue;
                                }
                            }

                            // maybe removed
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

                            $isNewProduct = true;

                            if (!empty($productEntry['ID'])) {
                                $isNewProduct = false;
                                do_action('itglx_wc1c_before_exists_product_info_resolve', $productEntry['ID'], $element);
                            } else {
                                do_action('itglx_wc1c_before_new_product_info_resolve', $element);
                            }

                            $productHash = md5(json_encode((array) $element));

                            if (
                                !empty($productEntry['ID']) &&
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

                            if ($_SESSION['xmlVersion'] === 2.04 && $resolveOldVariation) {
                                $this->resolveOldVariation($element);
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
                        !empty($_SESSION['IMPORT_1C_PROCESS']['currentCategorys1c'])
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
                                !in_array($id, $_SESSION['IMPORT_1C_PROCESS']['currentCategorys1c'])
                            ) {
                                \wp_delete_term($category, 'product_cat');

                                $kol--;
                            }

                            $_SESSION['IMPORT_1C_PROCESS']['countTermRemove'] = $kol;
                        }

                        global $wp_object_cache;

                        if ($wp_object_cache) {
                            $wp_object_cache->flush();
                        }

                        $_SESSION['IMPORT_1C_PROCESS']['missingTermsIsRemove'] = true;
                    }
                    /*------------------REMOVAL OF THE CATEGORIES OUT OF FULL EXCHANGE--------------------------*/
                }

                delete_option('product_cat_children');
                wp_cache_flush();

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
                        if ($reader->name === 'ТипыЦен' && $reader->nodeType !== \XMLReader::END_ELEMENT) {
                            PriceTypes::process($reader);
                        }

                        // resolve stocks
                        if (
                            !isset($_SESSION['IMPORT_1C']['stocks_parse']) &&
                            $reader->name === 'Склады' &&
                            $reader->nodeType !== \XMLReader::END_ELEMENT
                        ) {
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
                                                update_post_meta($productEntry['post_parent'], '_id_1c', (string) $parseID[0]);
                                            }
                                        }

                                        if (empty($productEntry['post_parent'])) {
                                            Logger::logChanges(
                                                '(variation) Error! Not exists parent product',
                                                [(string) $element->Ид]
                                            );

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
                                                isset($element->Количество) ||
                                                // the old exchange may not contain a stock node when the value is 0
                                                (!isset($_GET['version']) && isset($element->Наименование) && isset($element->Цены))
                                            ) {
                                                ProductAndVariationStock::set(
                                                    $productEntry['ID'],
                                                    ProductAndVariationStock::resolve($element),
                                                    $productEntry['post_parent']
                                                );

                                                \WC_Product_Variable::sync($productEntry['post_parent']);
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
                                                update_post_meta($productId, '_id_1c', (string) $element->Ид);
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
                                                isset($element->Количество) ||
                                                // the old exchange may not contain a stock node when the value is 0
                                                (!isset($_GET['version']) && isset($element->Наименование) && isset($element->Цены))
                                            ) {
                                                ProductAndVariationStock::set(
                                                    $productId,
                                                    ProductAndVariationStock::resolve($element)
                                                );
                                            }

                                            do_action('itglx_wc1c_after_product_offer_resolve', $productId, $element);
                                        }
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
        } // end parce

        \wp_defer_term_counting(false);

        return $valid;
    }

    private function resolveOldVariation($element)
    {
        $parseID = explode('#', (string) $element->Ид);

        // old format - resolve main variation data
        if (
            !empty($parseID[1]) && // not empty variation hash
            isset($element->ХарактеристикиТовара) &&
            isset($element->ХарактеристикиТовара->ХарактеристикаТовара)
        ) {
            VariationCharacteristicsToGlobalProductAttributes::process($element);

            $variationEntry['ID'] = Product::getProductIdByMeta((string) $element->Ид, '_id_1c', true);
            $variationEntry['post_parent'] = Product::getProductIdByMeta($parseID[0]);

            if (empty($variationEntry['post_parent'])) {
                Logger::logChanges(
                    '(variation) Error! Not exists parent product',
                    [(string) $element->Ид]
                );
            }

            Product::mainVariationData(
                $element,
                $variationEntry,
                $this->postAuthor
            );
        }
    }
}
