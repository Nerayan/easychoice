<?php
use Itgalaxy\Wc\Exchange1c\Admin\SettingsPage;
use Itgalaxy\Wc\Exchange1c\Admin\PluginActionLinksFilter;
use Itgalaxy\Wc\Exchange1c\Admin\ProductAttributesPage1cIdInfo;

// table columns
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\ProductAttributeTableColumn;
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\ProductCatTableColumn;
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\ProductTableColumn;

// metaboxes
use Itgalaxy\Wc\Exchange1c\Admin\MetaBoxes\ProductMetaBox;
use Itgalaxy\Wc\Exchange1c\Admin\MetaBoxes\ShopOrderMetaBox;

// ajax actions
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cClearLogs;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cClearTemp;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cLogsCountAndSize;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ItglxWc1cTempCountAndSize;

// admin requests
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\LogsGetInArchive;
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\TempGetInArchive;

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
ProductAttributeTableColumn::getInstance();
ProductCatTableColumn::getInstance();
ProductTableColumn::getInstance();

// metaboxes
ProductMetaBox::getInstance();
ShopOrderMetaBox::getInstance();

// bind ajax actions
ItglxWc1cClearLogs::getInstance();
ItglxWc1cClearTemp::getInstance();
ItglxWc1cLogsCountAndSize::getInstance();
ItglxWc1cTempCountAndSize::getInstance();

// bind admin request handlers
LogsGetInArchive::getInstance();
TempGetInArchive::getInstance();
