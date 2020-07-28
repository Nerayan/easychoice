<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\Other;

class VariationHeaderGuidInfo
{
    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('woocommerce_variation_header', [$this, 'id1cShow']);
    }

    public function id1cShow($variation)
    {
        if (!$variation || !isset($variation->ID)) {
            return;
        }

        $guid = get_post_meta($variation->ID, '_id_1c', true);

        echo '<br><small><strong>'
            . esc_html__('GUID', 'itgalaxy-woocommerce-1c')
            . '</strong>: '
            . ($guid ? esc_html($guid) : esc_html__('no data', 'itgalaxy-woocommerce-1c'))
            . '</small>';
    }
}
