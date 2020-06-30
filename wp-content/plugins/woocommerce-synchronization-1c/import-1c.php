<?php
//Located in the root of the site

if (defined('ABSPATH') || empty($_GET['type'])) {
    exit();
}

if (!defined('_1C_IMPORT')) {
    define('_1C_IMPORT', true);
}

if (file_exists(__DIR__ . '/wp-load.php')) {
    include_once __DIR__ . '/wp-load.php';
}

exit();
