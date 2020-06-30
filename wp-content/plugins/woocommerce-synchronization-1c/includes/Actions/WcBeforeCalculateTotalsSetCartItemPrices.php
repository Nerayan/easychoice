<?php
namespace Itgalaxy\Wc\Exchange1c\Includes\Actions;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class WcBeforeCalculateTotalsSetCartItemPrices
{
    private static $instance = false;

    public static $priceRules = ['regular_and_show_list_and_apply_price_depend_cart_totals'];

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

        if (
            !empty($settings['price_work_rule']) &&
            in_array($settings['price_work_rule'], self::$priceRules, true)
        ) {
            // https://docs.woocommerce.com/wc-apidocs/hook-docs.html
            add_action('woocommerce_before_calculate_totals', [$this, 'setCartItemPrices'], 20, 1);
        }
    }

    public function setCartItemPrices($cart)
    {
        // not run in admin without ajax
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        // once execute
        if (did_action('woocommerce_before_calculate_totals') > 1) {
            return;
        }

        $allPriceTypes = get_option('all_prices_types');

        if (empty($allPriceTypes)) {
            return;
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $setPriceType = '';

        $cartTotal = 0;

        foreach ($cart->get_cart() as $item) {
            $cartTotal += $item['data']->get_regular_price() * $item['quantity'];
        }

        $countPriceTypes = (int) count($allPriceTypes);

        // start 2 as first price type is base
        for ($i = 2; $i <= $countPriceTypes; $i++) {
            if (
                !empty($settings['price_type_' . $i]) &&
                !empty($settings['price_type_' . $i . '_summ']) &&
                $cartTotal > (float) $settings['price_type_' . $i . '_summ']
            ) {
                $setPriceType = $settings['price_type_' . $i];
            }
        }

        if (!$setPriceType) {
            return;
        }

        foreach ($cart->get_cart() as $item) {
            $postID = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];

            $productPrices = get_post_meta($postID, '_all_prices', true);

            if (empty($productPrices) || empty($productPrices[$setPriceType])) {
                continue;
            }

            $item['data']->set_price($productPrices[$setPriceType]);
            $item['data']->set_sale_price($productPrices[$setPriceType]);
        }
    }
}
