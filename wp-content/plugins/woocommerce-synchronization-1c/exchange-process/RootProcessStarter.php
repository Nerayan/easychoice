<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing\Catalog;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing\Listen;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing\Sale;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class RootProcessStarter
{
    private static $instance = false;

    private static $exchangeFileAbsolutePath = '';

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        // check session is start
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        Logger::startProcessingRequestLogProtocolEntry();

        try {
            $this->sentHeaders();
            $this->preparePaths();
            $this->checkExistsXmlExtension();
            $this->checkEnableExchange();
            $this->checkAuth();
            $this->prepareExchangeFileStructure();

            switch ($_GET['type']) {
                case 'catalog':
                    // catalog exchange - https://dev.1c-bitrix.ru/api_help/sale/algorithms/data_2_site.php
                    Catalog::process();
                    break;
                case 'listen':
                    // https://dev.1c-bitrix.ru/api_help/sale/algorithms/realtime.php
                    Listen::process();
                    break;
                case 'sale':
                    // order exchange
                    Sale::process();
                    break;
                default:
                    throw new \Exception('unknown or empty type');
            }
        } catch (\Exception $error) {
            self::failureResponse($error->getMessage());
            Logger::logProtocol('failure - ' . $error->getMessage());
        } catch (\Throwable $error) {
            self::failureResponse($error->getMessage());
            Logger::logProtocol('failure - ' . $error->getMessage(), $error);
        }

        Logger::endProcessingRequestLogProtocolEntry();

        // stop execution anyway
        exit();
    }

    public static function getCurrentExchangeFileAbsPath()
    {
        if (empty($_GET['filename'])) {
            throw new \Exception('empty or missed required parameter - `filename`');
        }

        if (!self::$exchangeFileAbsolutePath) {
            $filename = trim(str_replace('\\', '/', trim($_GET['filename'])), "/");
            $filename = Helper::getTempPath() . '/' . $filename;

            self::$exchangeFileAbsolutePath = $filename;
        }

        return self::$exchangeFileAbsolutePath;
    }

    public static function failureResponse($message)
    {
        echo "failure\n" . esc_html($message);
    }

    public static function successResponse($message = '')
    {
        echo "success\n" . $message;
        // escape ok
    }

    private function sentHeaders()
    {
        // If headers has been sent
        if (headers_sent()) {
            return;
        }

        // If is a request for orders
        if (isset($_GET['mode']) && $_GET['mode'] === 'query') {
            return;
        }

        // If is a request for info
        if (isset($_GET['mode']) && $_GET['mode'] === 'info') {
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
    }

    private function preparePaths()
    {
        Helper::existOrCreateDir(Helper::getTempPath());
        Helper::existOrCreateDir(Logger::getLogPath());
    }

    private function checkExistsXmlExtension()
    {
        if (class_exists('\\XMLReader')) {
            return;
        }

        throw new \Exception('Please install/enable `php-xml` extension for PHP');
    }

    private function checkEnableExchange()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // exchange enabled
        if (!empty($settings['enable_exchange'])) {
            return;
        }

        throw new \Exception(esc_html__('Error! Synchronization is prohibited!', 'itgalaxy-woocommerce-1c'));
    }

    private function checkAuth()
    {
        if (Helper::isUserCanWorkingWithExchange()) {
            return;
        }

        if (
            empty($_SERVER['PHP_AUTH_USER']) ||
            empty($_SERVER['PHP_AUTH_PW'])
        ) {
            $this->fixCgiAuth();
        }

        if (
            empty($_SERVER['PHP_AUTH_USER']) ||
            empty($_SERVER['PHP_AUTH_PW'])
        ) {
            throw new \Exception(
                esc_html__('Error! Empty login or password! Maybe not configured php-fpm.', 'itgalaxy-woocommerce-1c')
            );
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        $user = isset($settings['exchange_auth_username']) ? $settings['exchange_auth_username'] : 'empty';
        $password = isset($settings['exchange_auth_password']) ? $settings['exchange_auth_password'] : 'empty';

        // wrong login or password
        if (
            trim(wp_unslash($_SERVER['PHP_AUTH_USER'])) !== trim($user) ||
            trim(wp_unslash($_SERVER['PHP_AUTH_PW'])) !== trim($password)
        ) {
            throw new \Exception(esc_html__('Error! Wrong login or password!', 'itgalaxy-woocommerce-1c'));
        }
    }

    // https://www.php.net/manual/ru/features.http-auth.php#106285
    // method fills in empty user and password variables
    private function fixCgiAuth()
    {
        $environmentVariables = [
            'HTTP_AUTHORIZATION',
            'REDIRECT_HTTP_AUTHORIZATION'
        ];

        foreach ($environmentVariables as $environmentVariable) {
            if (empty($_SERVER[$environmentVariable])) {
                continue;
            }

            if (preg_match('/Basic\s+(.*)$/i', $_SERVER[$environmentVariable], $matches) === 0) {
                continue;
            }

            list($name, $password) = explode(':', base64_decode($matches[1]));
            $_SERVER['PHP_AUTH_USER'] = trim($name);
            $_SERVER['PHP_AUTH_PW'] = trim($password);
        }
    }

    private function prepareExchangeFileStructure()
    {
        if (empty($_GET['filename'])) {
            return;
        }

        /*
         * example - import_files/imagename.jpg
         * in this case, we need to create a subfolder `import_files` inside the temporary directory
         */

        $filename = trim(str_replace('\\', '/', trim($_GET['filename'])), "/");
        $filename = Helper::getTempPath() . '/' . $filename;

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0775, true);
        }
    }
}
