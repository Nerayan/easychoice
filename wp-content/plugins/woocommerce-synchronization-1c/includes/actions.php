<?php
use Itgalaxy\Wc\Exchange1c\Includes\Actions\DeleteAttachment;
use Itgalaxy\Wc\Exchange1c\Includes\Actions\PreDeleteTerm;
use Itgalaxy\Wc\Exchange1c\Includes\Actions\WcBeforeCalculateTotalsSetCartItemPrices;

if (!defined('ABSPATH')) {
    exit();
}

// bind actions
DeleteAttachment::getInstance();
PreDeleteTerm::getInstance();
WcBeforeCalculateTotalsSetCartItemPrices::getInstance();
