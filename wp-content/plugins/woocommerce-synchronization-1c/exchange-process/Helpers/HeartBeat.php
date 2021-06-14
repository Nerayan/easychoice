<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class HeartBeat
{
    private static $step = [];

    private static $start_time;

    private static $max_time;

    private static $memoryLimit;

    public static function next($type, $reader)
    {
        if (!isset($_SESSION['IMPORT_1C']['heartbeat'])) {
            $_SESSION['IMPORT_1C']['heartbeat'] = [];
        }

        if (!isset($_SESSION['IMPORT_1C']['heartbeat'][$type])) {
            $_SESSION['IMPORT_1C']['heartbeat'][$type] = 0;
        }

        if (!isset(self::$step[$type])) {
            self::$step[$type] = 0;
        }

        if (self::$step[$type] < $_SESSION['IMPORT_1C']['heartbeat'][$type]) {
            for ($i = self::$step[$type]; $i < $_SESSION['IMPORT_1C']['heartbeat'][$type]; $i++) {
                self::$step[$type]++;
                $reader->next();
            }

            $reader->read();
        }

        $_SESSION['IMPORT_1C']['heartbeat'][$type]++;
        self::$step[$type]++;

        if (!self::hasAvailableMemory()) {
            return false;
        }

        if (self::getTime() - self::$start_time >= self::$max_time) {
            return false;
        }

        return true;
    }

    public static function nextTerm()
    {
        if (!self::hasAvailableMemory()) {
            return false;
        }

        if (self::getTime() - self::$start_time >= self::$max_time) {
            return false;
        }

        return true;
    }

    public static function start()
    {
        self::$memoryLimit = self::getMemoryLimit();
        self::$start_time = self::getTime();
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        $timeLimit = isset($settings['time_limit'])
            ? (int) $settings['time_limit']
            : 20;

        self::$max_time = $timeLimit > 0 ? $timeLimit : 20;
    }

    private static function getTime()
    {
        list($msec, $sec) = explode(chr(32), microtime());

        return ($sec + $msec);
    }

    /**
     * Method allows you to check if there is available memory
     *
     * @return bool
     */
    private static function hasAvailableMemory()
    {
        // if the limit is empty, then we assume that memory is always available
        if (empty(self::$memoryLimit)) {
            return true;
        }

        /**
         * Check if there is at least another 10 megabytes in the available memory.         *
         * This value was determined experimentally and allows you to have a margin before overflow and error.
         *
         * 10485760 - 10 megabytes
         */
        if (memory_get_usage() + 10485760 > self::$memoryLimit) {
            return false;
        }

        return true;
    }

    /**
     * The method allows you to get the memory limit in bytes.
     *
     * If 0 is returned, it means that the limit is not set.
     *
     * @return int
     * @link https://www.php.net/manual/ini.core.php#ini.memory-limit
     */
    private static function getMemoryLimit()
    {
        $limitString = \ini_get('memory_limit');

        // limit disabled
        if ((string) $limitString === '-1') {
            return 0;
        }

        // the limit value is specified as a number, so we use it as the number of bytes
        if (is_numeric($limitString)) {
            return (int) $limitString;
        }

        $unit = strtolower(\mb_substr($limitString, -1 ));
        $bytes = (int) \mb_substr($limitString, 0, -1);

        switch ($unit) {
            case 'k':
                $bytes *= 1024; // kilobytes
                break 1;
            case 'm':
                $bytes *= 1048576; // megabytes
                break;
            case 'g':
                $bytes *= 1073741824; // gigabytes
                break;
            default:
                $bytes = 0;
                break;
        }

        return $bytes;
    }
}
