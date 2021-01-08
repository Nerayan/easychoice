<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;

/**
 * Handling a request to download a temporary directory in the archive
 */
class GetInArchiveTemp
{
    private static $instance = false;

    /**
     * Add all files to the archive or only XML.
     *
     * @var bool
     */
    private $onlyXML = false;

    /**
     * Returns an instance of a class or creates a new instance if it doesn't exist.
     *
     * @return GetInArchiveTemp
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create new instance.
     *
     * @link https://developer.wordpress.org/reference/functions/add_action/
     * @link https://developer.wordpress.org/reference/hooks/init/
     * @return void
     */
    private function __construct()
    {
        if (
            !isset($_GET['itgxl-wc1c-temp-get-in-archive']) &&
            !isset($_GET['itgxl-wc1c-temp-get-in-archive-only-xml'])
        ) {
            return;
        }

        // check exists php-zip extension
        if (!function_exists('zip_open')) {
            return;
        }

        if (isset($_GET['itgxl-wc1c-temp-get-in-archive-only-xml'])) {
            $this->onlyXML = true;
        }

        add_action('init', [$this, 'requestProcessing']);
    }

    /**
     * Action callback.
     *
     * @return void
     */
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
            . ($this->onlyXML ? 'only_xml_' : '')
            . date('Y-m-d_H:i:s')
            . '.zip"'
        );
        header('Content-Length: ' . filesize($file));

        readfile($file);
        unlink($file);

        exit();
    }

    /**
     * Forming an archive from the contents of the specified directory.
     *
     * @param string $path The absolute path of the directory to be archived
     * @param string $filename Archive file name
     * @link https://www.php.net/manual/class.ziparchive.php
     * @link https://www.php.net/manual/class.recursiveiteratoriterator.php
     * @return void
     */
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
            // if the request to get only XML, then we ignore other files
            if (
                $this->onlyXML &&
                !$file->isDir() &&
                $file->getExtension() !== 'xml'
            ) {
                continue;
            }
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
