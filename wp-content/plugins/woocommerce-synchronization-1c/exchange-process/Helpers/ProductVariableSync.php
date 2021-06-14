<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ProductVariableSync
{
    public static function process()
    {
        if (isset($_SESSION['IMPORT_1C']['variableProductsSync'])) {
            return true;
        }

        if (!isset($_SESSION['IMPORT_1C']['hasVariation'])) {
            return true;
        }

        Logger::logProtocol('variable product sync - start');

        if (!isset($_SESSION['IMPORT_1C']['numberOfSyncProducts'])) {
            $_SESSION['IMPORT_1C']['numberOfSyncProducts'] = 0;
        }

        $numberOfSyncProducts = 0;

        foreach ($_SESSION['IMPORT_1C']['hasVariation'] as $productID => $_) {
            if (!HeartBeat::nextTerm()) {
                Logger::logProtocol('variable product sync - progress');

                return false;
            }

            $numberOfSyncProducts++;

            if ($numberOfSyncProducts <= $_SESSION['IMPORT_1C']['numberOfSyncProducts']) {
                continue;
            }

            if (!get_post_meta($productID, '_is_set_variable', true)) {
                $_SESSION['IMPORT_1C']['numberOfSyncProducts'] = $numberOfSyncProducts;
                continue;
            }

            \WC_Product_Variable::sync($productID);

            Logger::logChanges('(product) sync variable product - ' . $productID);

            $_SESSION['IMPORT_1C']['numberOfSyncProducts'] = $numberOfSyncProducts;
        }

        Logger::logProtocol('variable product sync - end');

        $_SESSION['IMPORT_1C']['variableProductsSync'] = true;

        return true;
    }
}
