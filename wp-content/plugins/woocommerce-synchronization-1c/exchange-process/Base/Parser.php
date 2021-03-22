<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Groups;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

abstract class Parser
{
    protected $rate = 1;

    protected $postAuthor = 0;

    // true or false
    protected $onlyChanges = '';

    public function __construct()
    {
        HeartBeat::start();

        // https://developer.wordpress.org/reference/functions/wp_defer_term_counting/
        // disable allows to make the exchange much faster, since a large number of resources are saved for
        // each quantity recount, and the final recount is performed through the cron plugin task
        \wp_defer_term_counting(true);

        if (class_exists('\\WPSEO_Sitemaps_Cache')) {
            \add_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');
        }

        if (!isset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'])) {
            $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'] = [];
        }

        $settings = \get_option(Bootstrap::OPTIONS_KEY);

        $this->postAuthor = !empty($settings['exchange_post_author'])
            ? $settings['exchange_post_author']
            : '';

        if (!$this->postAuthor) {
            if ($users = get_users(['role' => 'administrator'])) {
                $this->postAuthor = array_shift($users)->ID;
            } else {
                $this->postAuthor = 1;
            }
        }

        Groups::prepare();
    }

    public function parse($filename) {}

    /**
     * @param \XMLReader $reader
     * @param string $node Node name.
     *
     * @return bool
     */
    protected function isEmptyNode(\XMLReader $reader, $node)
    {
        $resolveResult = str_replace(
            [' xmlns="' . $reader->namespaceURI . '"', ' '],
            '',
            $reader->readOuterXml()
        );

        if ($resolveResult === '<' . $node . '/>') {
            return true;
        }

        return false;
    }
}
