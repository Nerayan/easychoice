<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;

class GetInArchiveTemp
{
    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        if (!isset($_GET['itgxl-wc1c-temp-get-in-archive'])) {
            return;
        }

        // check exists php-zip extension
        if (!function_exists('zip_open')) {
            return;
        }

        // https://developer.wordpress.org/reference/hooks/init/
        add_action('init', [$this, 'requestProcessing']);
    }

    public function requestProcessing()
    {
        if (!Helper::isUserCanWorkingWithExchange()) {
            exit();
        }

        $file = ITGALAXY_WC_1C_PLUGIN_DIR . 'files/site' . get_current_blog_id() . '/' . uniqid() . '.zip';

        $this->createArchive(Helper::getTempPath(), $file);

        header('Content-Type: application/zip');
        header(
            'Content-Disposition: attachment; filename="'
            . 'temp_('
            . ITGALAXY_WC_1C_PLUGIN_VERSION
            . ')_'
            . date('Y-m-d_H:i:s')
            . '.zip"'
        );
        header('Content-Length: ' . filesize($file));

        readfile($file);
        unlink($file);

        exit();
    }

    private function createArchive($path, $filename)
    {
        // create empty file
        file_put_contents($filename, '');

        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $countFiles = 0;

        foreach ($files as $name => $file) {
            // get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($path) + 1);

            if (!$file->isDir()) {
                // add current file to archive
                $zip->addFile($filePath, 'temp/' . $relativePath);
                $countFiles++;
            } elseif ($relativePath !== false) {
                $zip->addEmptyDir('temp/' . $relativePath);
            }
        }

        if ($countFiles === 0) {
            $zip->addEmptyDir('temp');
        }

        // zip archive will be created only after closing object
        $zip->close();
    }
}
