<?php
namespace Itgalaxy\Wc\Exchange1c\Includes;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    public static $format = '[%datetime% | %ip% | %user% | %method% | %query%] %channel%.%level_name%: '
        . "%message% %context% %extra%\n";

    public static $log;

    public static function getLogPath()
    {
        return ITGALAXY_WC_1C_PLUGIN_DIR . 'files/site' . get_current_blog_id() . '/logs';
    }

    public static function logProtocol($message, $data = [])
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (
            !empty($settings['enable_logs_protocol']) &&
            is_writable(self::getLogPath())
        ) {
            if (empty($_SESSION['logSynchronizeProcessFile'])) {
                // prepare and set log file path
                self::setLogFilePathToSession(
                    self::generateLogFilePath()
                );
            }

            self::log($_SESSION['logSynchronizeProcessFile'], $message, $data);
        }
    }

    public static function startProcessingRequestLogProtocolEntry()
    {
        self::logProtocol('START PROCESSING REQUEST');
    }

    public static function endProcessingRequestLogProtocolEntry()
    {
        self::logProtocol('END PROCESSING REQUEST');
    }

    public static function logChanges($message, $data = [])
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (empty($settings['enable_logs_changes'])) {
            return;
        }

        if (!is_writable(self::getLogPath())) {
            return;
        }

        if (empty($_SESSION['logSynchronizeProcessFile'])) {
            // prepare and set log file path
            self::setLogFilePathToSession(
                self::generateLogFilePath()
            );
        }

        self::log($_SESSION['logSynchronizeProcessFile'], $message, $data);
    }

    public static function log($file, $message, $data = [], $type = 'info')
    {
        try {
            if (empty(self::$log)) {
                self::$log = new MonologLogger('wc1c');

                $handler = new StreamHandler($file, MonologLogger::INFO);
                $handler->setFormatter(new LineFormatter(self::$format));

                self::$log->pushHandler($handler);

                self::$log->pushProcessor(function ($entry) {
                    return self::addClientData($entry);
                });
            }

            self::$log->$type($message, (array) $data);
        } catch (\Exception $exception) {
            if (Helper::isUserCanWorkingWithExchange()) {
                wp_die(
                    sprintf(
                        esc_html__(
                            'Error code (%s): %s.',
                            'itgalaxy-woocommerce-1c'
                        ),
                        $exception->getCode(),
                        $exception->getMessage()
                    ),
                    esc_html__(
                        'An error occurred while writing the log file.',
                        'itgalaxy-woocommerce-1c'
                    ),
                    [
                        'back_link' => true
                    ]
                );
                // escape ok
            }
        }
    }

    public static function clearOldLogs()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $logsPath = self::getLogPath() . '/';

        $oldDaySynchronizationLogs = isset($settings['log_days'])
            ? (int) $settings['log_days']
            : 0;

        if ($oldDaySynchronizationLogs <= 1) {
            $oldDaySynchronizationLogs = 5;
        }

        // time in seconds - default 5 days
        $expireTime = $oldDaySynchronizationLogs * 24 * 60 * 60;

        if (is_dir($logsPath)) {
            $dirHandler = opendir($logsPath);

            if ($dirHandler) {
                while (($file = readdir($dirHandler)) !== false) {
                    $timeSec = time();
                    $filePath = $logsPath . $file;
                    $timeFile = filemtime($filePath);

                    $time = $timeSec - $timeFile;

                    if (is_file($filePath) && $time > $expireTime) {
                        unlink($filePath);
                    }
                }

                closedir($dirHandler);
            }
        }
    }

    private static function generateLogFilePath()
    {
        $type = !empty($_GET['type']) ? $_GET['type'] : 'empty';

        if (defined('DOING_CRON') && DOING_CRON) {
            $type = 'cron';
        }

        return self::getLogPath()
            . '/'
            . esc_attr($type)
            . '_'
            . date_i18n('Y.m.d_H')
            . '.log1c';
    }

    private static function setLogFilePathToSession($logFile)
    {
        $_SESSION['logSynchronizeProcessFile'] = $logFile;
    }

    private static function addClientData($record)
    {
        $record['ip'] = $_SERVER['REMOTE_ADDR'];
        $record['method'] = $_SERVER['REQUEST_METHOD'];
        $record['query'] = $_SERVER['QUERY_STRING'];
        $record['user'] = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'non user';

        return $record;
    }
}
