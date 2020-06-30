<?php
//Located in the root of the site

if (!defined('ABSPATH')
    && !empty($_GET['type'])
    && !empty($_SERVER['PHP_AUTH_USER'])
) {
    if (!defined('_1C_IMPORT')) {
        define('_1C_IMPORT', true);
    }

    if (file_exists(__DIR__ . '/wp-load.php')) {
        require_once __DIR__ . '/wp-load.php';
    }
}

exit();
