<?php
namespace Itgalaxy\PluginCommon;

class AnalyticsHelper
{
    /**
     * @var string[]
     */
    private static $defaultUtmList = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content'
    ];

    /**
     * The method allows you to get all values at once.
     *
     * @return array Array with the following keys: `utm`, `gaClientId`, `roistatVisit` and `yandexClientId`.
     */
    public static function getAll()
    {
        return [
            'utm' => self::getUtmListFromUrl(),
            'gaClientId' => self::getGetGaClientIdFromCookie(),
            'roistatVisit' => self::getCookieRoistatVisit(),
            'yandexClientId' => self::getCookieYandexClientId()
        ];
    }

    /**
     * The method allows you to get a set of values of `utm` tags from the GET parameters that were received
     * in the request.
     *
     * @return array Array contains keys such as: {@see $defaultUtmList}. If there are no or no tags in the link
     *              as a parameter, then an empty array is returned.
     * @link https://en.wikipedia.org/wiki/UTM_parameters
     */
    public static function getUtmListFromUrl()
    {
        if (empty($_GET) || !is_array($_GET)) {
            return [];
        }

        $strPosFunction = 'mb_strpos';

        if (!function_exists('mb_strpos')) {
            $strPosFunction = 'strpos';
        }

        $utmParams = [];

        foreach ($_GET as $key => $value) {
            if ($strPosFunction($key, 'utm_') === 0) {
                $utmParams[$key] = \wp_unslash($value);
            }

            if ($strPosFunction($key, 'pm_') === 0) {
                $utmParams[$key] = \wp_unslash($value);
            }
        }

        if (empty($utmParams)) {
            return [];
        }

        /**
         * In the link, not always all the parameters are filled, so it is necessary to set the missing ones so that
         * the returned set meets the expectations.
         */
        foreach (self::$defaultUtmList as $defaultUtm) {
            if (!isset($utmParams[$defaultUtm])) {
                $utmParams[$defaultUtm] = '';
            }
        }

        return $utmParams;
    }

    /**
     * The method allows you to get the user id from cookie `_ga`
     *
     * @return string If no data is available, an empty string will be returned.
     *
     * @link https://stackoverflow.com/questions/16102436/what-are-the-values-in-ga-cookie
     */
    public static function getGetGaClientIdFromCookie()
    {
        if (empty($_COOKIE['_ga'])) {
            return '';
        }

        $clientId = explode('.', \wp_unslash($_COOKIE['_ga']));

        if (!isset($clientId[2]) || !isset($clientId[3])) {
            return '';
        }

        return $clientId[2] . '.' . $clientId[3];
    }

    /**
     * The method allows you to get value from cookie `roistat_visit`
     *
     * @return string If no data is available, an empty string will be returned.
     */
    public static function getCookieRoistatVisit()
    {
        return isset($_COOKIE['roistat_visit'])
            ? \wp_unslash($_COOKIE['roistat_visit'])
            : '';
    }

    /**
     * The method allows you to get value from cookie `_ym_uid`
     *
     * @return string If no data is available, an empty string will be returned.
     * @link https://yandex.com/support/metrica/general/cookie-usage.html
     */
    public static function getCookieYandexClientId()
    {
        return isset($_COOKIE['_ym_uid'])
            ? \wp_unslash($_COOKIE['_ym_uid'])
            : '';
    }
}
