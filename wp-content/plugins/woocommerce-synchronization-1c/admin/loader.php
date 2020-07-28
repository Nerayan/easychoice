<?php
use Itgalaxy\Wc\Exchange1c\Admin\SettingsPage;
use Itgalaxy\Wc\Exchange1c\Admin\PluginActionLinksFilter;
use Itgalaxy\Wc\Exchange1c\Admin\ProductAttributesPage1cIdInfo;

// table columns
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\TableColumnProductAttribute;
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\TableColumnProductCat;
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\TableColumnProduct;

// metaboxes
use Itgalaxy\Wc\Exchange1c\Admin\MetaBoxes\MetaBoxProduct;
use Itgalaxy\Wc\Exchange1c\Admin\MetaBoxes\MetaBoxShopOrder;

// ajax actions
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cClearLogs;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cClearTemp;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cLogsCountAndSize;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cTempCountAndSize;

// admin requests
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\GetInArchiveLogs;
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\GetInArchiveTemp;

//other
use Itgalaxy\Wc\Exchange1c\Admin\Other\VariationHeaderGuidInfo;

if (!defined('ABSPATH')) {
    exit();
}

// do not continue initialization if not admin panel
if (!is_admin()) {
    return;
}

SettingsPage::getInstance();
PluginActionLinksFilter::getInstance();
ProductAttributesPage1cIdInfo::getInstance();

// table columns
TableColumnProductAttribute::getInstance();
TableColumnProductCat::getInstance();
TableColumnProduct::getInstance();

// metaboxes
MetaBoxProduct::getInstance();
MetaBoxShopOrder::getInstance();

// bind ajax actions
ItglxWc1cClearLogs::getInstance();
ItglxWc1cClearTemp::getInstance();
ItglxWc1cLogsCountAndSize::getInstance();
ItglxWc1cTempCountAndSize::getInstance();

// bind admin request handlers
GetInArchiveLogs::getInstance();
GetInArchiveTemp::getInstance();

// bind other admin actions
VariationHeaderGuidInfo::getInstance();
