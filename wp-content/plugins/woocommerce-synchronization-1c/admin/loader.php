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
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ClearLogsAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ClearTempAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\LastRequestResponseAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\LogsCountAndSizeAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\TempCountAndSizeAjaxAction;

// admin requests
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\GetInArchiveLogs;
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\GetInArchiveTemp;

//other
use Itgalaxy\Wc\Exchange1c\Admin\Other\AdminNoticeIfHasTrashedProductWithGuid;
use Itgalaxy\Wc\Exchange1c\Admin\Other\AdminNoticeIfNotVerified;
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
ClearLogsAjaxAction::getInstance();
ClearTempAjaxAction::getInstance();
LastRequestResponseAjaxAction::getInstance();
LogsCountAndSizeAjaxAction::getInstance();
TempCountAndSizeAjaxAction::getInstance();

// bind admin request handlers
GetInArchiveLogs::getInstance();
GetInArchiveTemp::getInstance();

// bind other admin actions
AdminNoticeIfHasTrashedProductWithGuid::getInstance();
AdminNoticeIfNotVerified::getInstance();
VariationHeaderGuidInfo::getInstance();
