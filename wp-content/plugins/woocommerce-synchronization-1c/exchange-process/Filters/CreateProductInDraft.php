<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class CreateProductInDraft
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
        add_filter('itglx_wc1c_insert_post_new_product_params', [$this, 'postParams']);
    }

    public function postParams($params)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (empty($settings['product_create_new_in_status_draft'])) {
            return $params;
        }

        $params['post_status'] = 'draft';

        return $params;
    }
}
