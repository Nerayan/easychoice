<?php
namespace Itgalaxy\Wc\Exchange1c\Includes;

class Helper
{
    public static function isUserCanWorkingWithExchange()
    {
        if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
            return false;
        }

        return true;
    }

    public static function isExchangeRequest()
    {
        if (
            (!defined('_1C_IMPORT') || _1C_IMPORT !== true) && // 1c exchange directly running
            (!self::isUserCanWorkingWithExchange() || !isset($_GET['manual-1c-import'])) // admin exchange running
        ) {
            return false;
        }

        return true;
    }

    public static function getTempPath()
    {
        return ITGALAXY_WC_1C_PLUGIN_DIR . 'files/site' . get_current_blog_id() . '/temp';
    }

    public static function getFileSizeLimit()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        $fileSizeLimit = isset($settings['file_limit'])
            ? (int) $settings['file_limit']
            : 1000000;

        return $fileSizeLimit;
    }

    public static function isUseZip()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (
            function_exists('zip_open') &&
            !empty($settings['use_file_zip'])
        ) {
            return true;
        }

        // log if enabled but no extension
        if (
            !function_exists('zip_open') &&
            !empty($settings['use_file_zip'])
        ) {
            Logger::logProtocol('Exchange in the archive is enabled but no extension php-xml');
        }

        return false;
    }

    public static function removeDir($dir)
    {
        $objects = glob($dir . '/*');

        if ($objects) {
            foreach ($objects as $object) {
                is_dir($object) ? self::removeDir($object) : unlink($object);
            }
        }

        rmdir($dir);
    }

    public static function scanDir($dir)
    {
        $ignored = ['.', '..', '.svn', '.htaccess'];
        $files = [];

        if (file_exists($dir)) {
            foreach (scandir($dir) as $file) {
                if (in_array($file, $ignored) || is_dir($dir . '/' . $file)) {
                    continue;
                }

                $files[$file] = filemtime($dir . '/' . $file);
            }

            arsort($files);
            $files = array_keys($files);
        }

        return count($files) > 0 ? $files : false;
    }

    public static function extractArchive($filename)
    {
        $zip = new \ZipArchive;

        $result = $zip->open($filename);

        if ($result !== true) {
            Logger::logProtocol(esc_html__('Failed open archive ' . $filename . ', code - ' . $result));

            return;
        }

        $result = $zip->extractTo(dirname($filename));

        if ($result !== true) {
            Logger::logProtocol(esc_html__('Failed extract archive ' . $filename . ', to - ' . dirname($filename)));

            return;
        }

        $result = $zip->close();

        if ($result !== true) {
            Logger::logProtocol(esc_html__('Failed close archive ' . $filename));

            return;
        }

        unlink($filename);
    }

    public static function existOrCreateDir($dirName)
    {
        $color = 'color: green;';
        $status = true;

        if (file_exists($dirName) && !is_writable($dirName)) {
            $color = 'color: red;';
            $message = $dirName
                . ' |  '
                . esc_html__(
                    'Exists, but is not available for writing. Change permissions.',
                    'itgalaxy-woocommerce-1c'
                );
            $status = false;
        } else {
            if (!file_exists($dirName)) {
                if (mkdir($dirName, 0755, true)) {
                    $message = $dirName
                        . ' | '
                        . esc_html__('Created and available for writing.', 'itgalaxy-woocommerce-1c');
                } else {
                    $color = 'color: red;';
                    $message = $dirName
                        . ' | '
                        . esc_html__('Not exist and unavailable for writing..', 'itgalaxy-woocommerce-1c');
                    $status = false;
                }
            } else {
                $message = $dirName
                    . ' | '
                    . esc_html__('Exists and available for writing.', 'itgalaxy-woocommerce-1c');
            }
        }

        return [
            'color' => $color,
            'text' => $message,
            'status' => $status
        ];
    }
}
