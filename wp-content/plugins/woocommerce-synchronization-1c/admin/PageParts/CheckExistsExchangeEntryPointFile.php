<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

class CheckExistsExchangeEntryPointFile
{
    public static function render()
    {
        // check exists exchange entry point file
        if (file_exists(ABSPATH . 'import-1c.php')) {
           return;
        }

        echo sprintf(
            '<div class="error notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
            esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
            esc_html__(
                'There is no `import-1c.php` file in the root of the site, it should be copied to the root of the site '
                . 'when the plugin is activated, but for some reason this did not happen. Please copy this file from '
                . 'the plugin folder to the root of the site.',
                'itgalaxy-woocommerce-1c'
            )
        );
    }
}
