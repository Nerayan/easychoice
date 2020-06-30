<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Cron;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class CatalogModeComplete
{
    public static function process()
    {
        if (!get_option('not_clear_1c_complete')) {
            update_option('all1cProducts', []);
            update_option('currentAll1cGroup', []);

            $cron = Cron::getInstance();
            $cron->createCronTermRecount();

            // clear sitemap cache
            if (class_exists('\\WPSEO_Sitemaps_Cache')) {
                remove_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');
                \WPSEO_Sitemaps_Cache::clear();
            }
        } else {
            update_option('not_clear_1c_complete', '');
        }

        RootProcessStarter::successResponse(esc_html__('Package complete!', 'itgalaxy-woocommerce-1c'));
        Logger::logProtocol('success - Package complete!');
    }
}
