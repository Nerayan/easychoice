<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

class CheckPhpExtensionNotice
{
    public static function render()
    {
        // check exists php-xmlreader extension
        if (!class_exists('\\XMLReader')) {
            echo sprintf(
                '<div class="error notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
                esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
                sprintf(
                    esc_html__(
                        'There is no extension "%1$s", without it, the exchange will not work. '
                        . 'Please install / activate the extension.',
                        'itgalaxy-woocommerce-1c'
                    ),
                    'php-xmlreader'
                )
            );
        }

        // check exists php-xml extension
        if (!function_exists('\\simplexml_load_string')) {
            echo sprintf(
                '<div class="error notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
                esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
                sprintf(
                    esc_html__(
                        'There is no extension "%1$s", without it, the exchange will not work. '
                        . 'Please install / activate the extension.',
                        'itgalaxy-woocommerce-1c'
                    ),
                    'php-xml'
                )
            );
        }

        // check exists php-zip extension
        if (!function_exists('zip_open')) {
            echo sprintf(
                '<div class="error notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
                esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
                esc_html__(
                    'There is no extension "php-zip", so the exchange in the archive will not work. '
                    . 'Please install / activate the extension.',
                    'itgalaxy-woocommerce-1c'
                )
            );
        }
    }
}
