<?php
namespace Itgalaxy\Wc\Exchange1c\Includes;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;

class Cron
{
    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'createCron']);

        // not bind if run not cron mode
        if (!defined('DOING_CRON')  || !DOING_CRON) {
            return;
        }

        add_action(Bootstrap::CRON, [$this, 'cronAction']);
        add_action('termsRecount1cSynchronization', [$this, 'actionTermRecount']);
        add_action('disableItems1cSynchronization', [$this, 'actionDisableItems']);
    }

    public function createCron()
    {
        if (!wp_next_scheduled(Bootstrap::CRON)) {
            wp_schedule_event(time(), 'weekly', Bootstrap::CRON);
        }
    }

    public function createCronTermRecount()
    {
        // https://developer.wordpress.org/reference/functions/wp_next_scheduled/
        if (!wp_next_scheduled('termsRecount1cSynchronization')) {
            Logger::logProtocol('termsRecount1cSynchronization - task register');

            // https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
            wp_schedule_single_event(time(), 'termsRecount1cSynchronization');
        }
    }

    public function createCronDisableItems()
    {
        // https://developer.wordpress.org/reference/functions/wp_next_scheduled/
        if (!wp_next_scheduled('disableItems1cSynchronization')) {
            Logger::logProtocol('disableItems1cSynchronization - task register');

            // https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
            wp_schedule_single_event(time(), 'disableItems1cSynchronization');
        }
    }

    public function cronAction()
    {
        $response = PluginRequest::call('cron_code_check');

        if (is_wp_error($response)) {
            return;
        }

        if ($response->status === 'stop') {
            update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, '');
        }
    }

    public function actionTermRecount()
    {
        global $wpdb;

        Logger::startProcessingRequestLogProtocolEntry(true);
        Logger::logProtocol('termsRecount1cSynchronization - started');

        delete_option('product_cat_children');

        $taxes = [
            'product_cat',
            'product_tag'
        ];

        foreach ($taxes as $tax) {
            // https://docs.woocommerce.com/wc-apidocs/function-_wc_term_recount.html
            _wc_term_recount(
                // https://developer.wordpress.org/reference/functions/get_terms/
                get_terms(
                    [
                        'taxonomy' => $tax,
                        'hide_empty' => false,
                        'fields' => 'id=>parent'
                    ]
                ),
                // https://developer.wordpress.org/reference/functions/get_taxonomy/
                get_taxonomy($tax),
                true,
                false
            );

            $this->recalculatePostCountInTax($tax);
        }

        // recalculate attribute terms post count
        if (function_exists('wc_get_attribute_taxonomies')) {
            $attributeTaxonomies = \wc_get_attribute_taxonomies();

            if ($attributeTaxonomies) {
                foreach ($attributeTaxonomies as $tax) {
                    // widget filter by attribute clean transient
                    \delete_transient('wc_layered_nav_counts_pa_' . $tax->attribute_name);

                    $this->recalculatePostCountInTax(
                        // https://docs.woocommerce.com/wc-apidocs/function-wc_attribute_taxonomy_name.html
                        \wc_attribute_taxonomy_name($tax->attribute_name)
                    );
                }
            }
        }

        // update wc search/ordering table
        if (function_exists('wc_update_product_lookup_tables_column')) {
            // Make a row per product in lookup table.
            $wpdb->query(
                "
        		INSERT IGNORE INTO {$wpdb->wc_product_meta_lookup} (`product_id`)
        		SELECT
        			posts.ID
        		FROM {$wpdb->posts} posts
        		WHERE
        			posts.post_type IN ('product', 'product_variation')
        		"
            );

            // https://docs.woocommerce.com/wc-apidocs/function-wc_update_product_lookup_tables_column.html
            wc_update_product_lookup_tables_column('min_max_price');
            wc_update_product_lookup_tables_column('stock_quantity');
            wc_update_product_lookup_tables_column('sku');
            wc_update_product_lookup_tables_column('stock_status');
            wc_update_product_lookup_tables_column('total_sales');
            wc_update_product_lookup_tables_column('onsale');

            Logger::logProtocol('update lookup');
        }

        // clear featured, sale and etc. transients
        if (function_exists('wc_delete_product_transients')) {
            Logger::logProtocol('execute - wc_delete_product_transients');

            // https://docs.woocommerce.com/wc-apidocs/function-wc_delete_product_transients.html
            wc_delete_product_transients();
        }

        # if activated Wp Super Cache
        if (function_exists('wp_cache_clear_cache')) {
            Logger::logProtocol('execute - wp_cache_clear_cache');
            wp_cache_clear_cache();
        }

        // fixed compatibility with `Rank Math SEO`
        if (class_exists('\\RankMath')) {
            flush_rewrite_rules(true);
        }

        // fixed compatibility with `WooCommerce Wholesale Prices Premium`
        if (defined('WWPP_CRON_INITIALIZE_PRODUCT_WHOLESALE_VISIBILITY_FILTER')) {
            Logger::logProtocol('register task - ' . WWPP_CRON_INITIALIZE_PRODUCT_WHOLESALE_VISIBILITY_FILTER);

            wp_schedule_single_event(time(), WWPP_CRON_INITIALIZE_PRODUCT_WHOLESALE_VISIBILITY_FILTER);
        }

        Logger::logProtocol('termsRecount1cSynchronization - end');

        Logger::endProcessingRequestLogProtocolEntry();
    }

    public function actionDisableItems()
    {
        global $wpdb;

        Logger::startProcessingRequestLogProtocolEntry(true);
        Logger::logProtocol('disableItems1cSynchronization - started');

        $all1cProducts = get_option('all1cProducts');

        /*------------------REMOVAL OF THE PRODUCTS OUT OF FULL EXCHANGE--------------------------*/
        if ($all1cProducts && count($all1cProducts)) {
            $productIds = [];
            $posts = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_id_1c'");

            foreach ($posts as $post) {
                $productIds[] = $post->post_id;
            }

            unset($posts);

            $kol = 0;
            $countRemove = 0;

            foreach ($productIds as $productID) {
                $kol++;

                if (!in_array($productID, $all1cProducts)) {
                    Product::removeProduct($productID);
                    $countRemove++;
                    $kol--;
                }
            }
        }
        /*------------------REMOVAL OF THE PRODUCTS OUT OF FULL EXCHANGE--------------------------*/

        /*------------------REMOVAL OF THE CATEGORIES OUT OF FULL EXCHANGE--------------------------*/
        $currentAll1cGroup = get_option('currentAll1cGroup');

        if ($currentAll1cGroup && count($currentAll1cGroup)) {
            $kol = 0;

            foreach (Term::getProductCatIDs() as $id => $category) {
                $kol++;

                if (\get_term($category, 'product_cat') && !in_array($id, $currentAll1cGroup)) {
                    \wp_delete_term($category, 'product_cat');

                    $kol--;
                }
            }

            delete_option('product_cat_children');
            wp_cache_flush();
        }
        /*------------------REMOVAL OF THE CATEGORIES OUT OF FULL EXCHANGE--------------------------*/

        // recalculate product cat counts
        $this->createCronTermRecount();

        update_option('all1cProducts', []);
        update_option('currentAll1cGroup', []);

        Logger::logProtocol('disableItems1cSynchronization - end');
        Logger::endProcessingRequestLogProtocolEntry();
    }

    public function recalculatePostCountInTax($tax)
    {
        Logger::logProtocol('recalculate - ' . $tax);

        // https://developer.wordpress.org/reference/functions/get_terms/
        $terms = get_terms(
            [
                'taxonomy' => $tax,
                'hide_empty' => false,
                'fields' => 'ids'
            ]
        );

        if ($terms) {
            // https://developer.wordpress.org/reference/functions/wp_update_term_count_now/
            wp_update_term_count_now(
                $terms,
                $tax
            );
        }
    }
}
