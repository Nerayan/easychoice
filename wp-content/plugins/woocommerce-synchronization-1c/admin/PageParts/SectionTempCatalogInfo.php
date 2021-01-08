<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;

class SectionTempCatalogInfo
{
    public static function render()
    {
        Section::header(esc_html__('Temporary directory for exchange with 1C', 'itgalaxy-woocommerce-1c'));

        $message = Helper::existOrCreateDir(Helper::getTempPath());
        ?>
        <p>
            <span style="<?php echo esc_attr($message['color']); ?>">
                <?php echo esc_html($message['text']); ?>
            </span>
        </p>
        <p class="description">
            <?php
            esc_html_e(
                'Files received from 1C during the exchange are loaded into this directory if it is not '
                    . 'available for write and read, sharing will be impossible.',
                'itgalaxy-woocommerce-1c'
            );
            ?>
        </p>
        <hr>
        <?php
        // check exists php-zip extension
        if (function_exists('zip_open')) {
            ?>
            <a href="<?php echo esc_url(admin_url()); ?>?itgxl-wc1c-temp-get-in-archive"
                class="btn btn-outline-info btn-sm text-decoration-none"
                target="_blank">
                <?php echo esc_html__('Download in zip archive (all)', 'itgalaxy-woocommerce-1c'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url()); ?>?itgxl-wc1c-temp-get-in-archive-only-xml"
               class="btn btn-outline-info btn-sm text-decoration-none"
               target="_blank">
                <?php echo esc_html__('Download in zip archive (only XML files)', 'itgalaxy-woocommerce-1c'); ?>
            </a>
        <?php } ?>
        <button class="btn btn-info btn-sm" type="button" data-ui-component="itglx-wc1c-ajax-clear-temp">
            <span class="text">
                <?php echo esc_html__('Clear', 'itgalaxy-woocommerce-1c'); ?>
                <span data-ui-component="itglx-wc1c-temp-count-and-size-text"></span>
            </span>
            <span class="spinner-grow spinner-grow-sm" role="status"></span>
        </button>
        <?php

        Section::footer();
    }
}
