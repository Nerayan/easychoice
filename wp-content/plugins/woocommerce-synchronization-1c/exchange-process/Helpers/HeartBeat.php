<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class HeartBeat
{
    private static $step = [];

    private static $start_time;

    private static $max_time;

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

        if (self::getTime() - self::$start_time >= self::$max_time) {
            return false;
        }

        return true;
    }

    public static function nextTerm()
    {
        if (self::getTime() - self::$start_time >= self::$max_time) {
            return false;
        }

        return true;
    }

    public static function start()
    {
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
}
