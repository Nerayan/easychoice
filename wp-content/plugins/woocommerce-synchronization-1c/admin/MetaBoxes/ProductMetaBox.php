<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\MetaBoxes;

class ProductMetaBox
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
        // https://developer.wordpress.org/reference/hooks/add_meta_boxes/
        add_action('add_meta_boxes', [$this, 'addId1cBox']);
    }

    public function addId1cBox()
    {
        // https://developer.wordpress.org/reference/functions/add_meta_box/
        add_meta_box(
            'id_1c',
            esc_html__('Exchange with 1C ', 'itgalaxy-woocommerce-1c'),
            [$this, 'id1cShow'],
            'product',
            'side',
            'high'
        );
    }

    public function id1cShow($post)
    {
        if (!$post || !isset($post->ID)) {
            return;
        }

        $guid = get_post_meta($post->ID, '_id_1c', true);

        echo '<strong>'
            . esc_html__('GUID', 'itgalaxy-woocommerce-1c')
            . '</strong><br>'
            . (
                $guid ? esc_html($guid) : esc_html__('no data', 'itgalaxy-woocommerce-1c')
            );
    }
}
