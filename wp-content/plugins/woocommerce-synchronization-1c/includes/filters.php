<?php
use Itgalaxy\Wc\Exchange1c\Includes\Filters\WcCartItemPriceShowSalePrice;
use Itgalaxy\Wc\Exchange1c\Includes\Filters\WcGetPriceHtmlShowPriceListDetailProductPage;

if (!defined('ABSPATH')) {
    exit();
}

// bind filters
WcCartItemPriceShowSalePrice::getInstance();
WcGetPriceHtmlShowPriceListDetailProductPage::getInstance();
