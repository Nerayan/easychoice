<?php
namespace Itgalaxy\Wc\Exchange1c\Includes\Filters;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class WcGetPriceHtmlShowPriceListDetailProductPage
{
    private static $instance = false;

    public static $priceRules = ['regular_and_show_list', 'regular_and_show_list_and_apply_price_depend_cart_totals'];

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $showPriceWorkRules = apply_filters('itglx_wc1c_price_work_rules_product_page_show_list', self::$priceRules);

        if (
            !empty($settings['price_work_rule']) &&
            in_array($settings['price_work_rule'], $showPriceWorkRules, true)
        ) {
            // https://docs.woocommerce.com/wc-apidocs/hook-docs.html
            add_filter('woocommerce_get_price_html', [$this, 'priceHtml'], 10, 2);
        }
    }

    public function priceHtml($price, $product)
    {
        if (!is_product() || !is_single(get_the_ID())) {
            return $price;
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $productPrices = get_post_meta($product->get_id(), '_all_prices', true);

        if (empty($productPrices)) {
            return $price;
        }

        $allPriceTypes = get_option('all_prices_types');

        if (empty($allPriceTypes)) {
            return $price;
        }

        $return = '';
        $countPriceTypes = (int) count($allPriceTypes);

        for ($i = 1; $i <= $countPriceTypes; $i++) {
            if (!empty($settings['price_type_' . $i]) && !empty($productPrices[$settings['price_type_' . $i]])) {
                $return .= '<span class="product-price-list-item">'
                    . wc_price($productPrices[$settings['price_type_' . $i]])
                    . (
                    !empty($settings['price_type_' . $i . '_text'])
                        ? ' <small class="product-price-list-item-name">('
                        . esc_html($settings['price_type_' . $i . '_text'])
                        . ')</small>'
                        : ''
                    )
                    . '</span><br>';
            }
        }

        return $return;
    }
}
