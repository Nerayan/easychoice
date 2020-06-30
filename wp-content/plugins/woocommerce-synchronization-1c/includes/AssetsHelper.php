<?php
namespace Itgalaxy\Wc\Exchange1c\Includes;

class AssetsHelper
{
    public static function getPathAssetFile($assetFile)
    {
        $manifestFile = ITGALAXY_WC_1C_PLUGIN_DIR . '/resources/compiled/mix-manifest.json';

        if (!file_exists($manifestFile)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestFile), true);

        if (!is_array($manifest) || !isset($manifest[$assetFile])) {
            return '';
        }

        return ITGALAXY_WC_1C_PLUGIN_URL . 'resources/compiled' . $manifest[$assetFile];
    }
}
