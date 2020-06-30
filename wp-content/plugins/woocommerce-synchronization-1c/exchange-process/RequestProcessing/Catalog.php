<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

class Catalog
{
    public static function process()
    {
        switch ($_GET['mode']) {
            case 'checkauth':
                CatalogModeCheckAuth::process();
                break;
            case 'init':
                CatalogModeInit::process();
                break;
            case 'file':
                CatalogModeFile::process();
                break;
            case 'import':
                CatalogModeImport::process();
                break;
            case 'complete':
                CatalogModeComplete::process();
                break;
            case 'deactivate':
                CatalogModeDeactivate::process();
                break;
            default:
                throw new \Exception('unknown or empty mode');
        }
    }
}
