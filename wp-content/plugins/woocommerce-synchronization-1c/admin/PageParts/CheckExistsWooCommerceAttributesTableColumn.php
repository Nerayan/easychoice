<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

class CheckExistsWooCommerceAttributesTableColumn
{
    public static function render()
    {
        global $wpdb;

        $dbName = DB_NAME;
        $columnExists = $wpdb->query(
            "SELECT * FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = '{$dbName}'
                  AND TABLE_NAME = '{$wpdb->prefix}woocommerce_attribute_taxonomies'
                  AND COLUMN_NAME = 'id_1c'"
        );

        // check exists column
        if (!$columnExists) {
            echo sprintf(
                '<div class="error notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
                esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
                esc_html__(
                    'For some reason, there is no column `id_1c` in table `woocommerce_attribute_taxonomies` that '
                    . 'should have been added when the plugin was activated. If there are properties in the unload, '
                    . 'this will cause a processing error. You can add it yourself: name - id_1c, type - varchar, '
                    . 'length 200.',
                    'itgalaxy-woocommerce-1c'
                )
            );
        }
    }
}
