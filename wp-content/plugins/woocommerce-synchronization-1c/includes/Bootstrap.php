<?php
namespace Itgalaxy\Wc\Exchange1c\Includes;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\CreateProductInDraft;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FindProductCatId;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FindProductId;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\ProductIsRemoved;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\SkipProductByXml;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;

class Bootstrap
{
    const OPTIONS_KEY = 'wc-itgalaxy-1c-exchange-settings';
    const OPTION_INFO_KEY = 'wc-itgalaxy-1c-exchange-additional-info';
    const PURCHASE_CODE_OPTIONS_KEY = 'wc-itgalaxy-1c-exchange-purchase-code';

    public static $plugin = '';

    private static $instance = false;

    protected function __construct($file)
    {
        self::$plugin = $file;

        self::pluginLifeCycleActionsRegister();

        if (get_option('ITGALAXY_WC_1C_PLUGIN_VERSION') !== ITGALAXY_WC_1C_PLUGIN_VERSION) {
            self::updateSettings();
        }

        // bind cron actions
        Cron::getInstance();

        // processing request from the accounting system
        // https://developer.wordpress.org/reference/hooks/init/
        add_action('init', function () {
            // check is exchange request
            if (!Helper::isExchangeRequest()) {
                return;
            }

            // bind filters
            CreateProductInDraft::getInstance();
            FindProductCatId::getInstance();
            FindProductId::getInstance();
            ProductIsRemoved::getInstance();
            SkipProductByXml::getInstance();

            // exchange start
            RootProcessStarter::getInstance();
        }, PHP_INT_MAX);
    }

    public static function getInstance($file)
    {
        if (!self::$instance) {
            self::$instance = new self($file);
        }

        return self::$instance;
    }

    public static function pluginLifeCycleActionsRegister()
    {
        // https://developer.wordpress.org/reference/functions/register_activation_hook/
        register_activation_hook(
            self::$plugin,
            ['Itgalaxy\Wc\Exchange1c\Includes\Bootstrap', 'pluginActivation']
        );

        // https://developer.wordpress.org/reference/functions/register_deactivation_hook/
        register_deactivation_hook(
            self::$plugin,
            ['Itgalaxy\Wc\Exchange1c\Includes\Bootstrap', 'pluginDeactivation']
        );

        // https://developer.wordpress.org/reference/functions/register_uninstall_hook/
        register_uninstall_hook(
            self::$plugin,
            ['Itgalaxy\Wc\Exchange1c\Includes\Bootstrap', 'pluginUninstall']
        );
    }

    public static function updateSettings()
    {
        $settings = get_option(self::OPTIONS_KEY);

        if (!is_array($settings)) {
            $settings = [];
        }

        $resaveOptions = [
            'old_day_synchronization_logs' => 'log_days',
            'synchronization_user' => 'exchange_auth_username',
            'synchronization_pass' => 'exchange_auth_password',
            'synchronization_post_author' => 'exchange_post_author'
        ];

        $resave = false;

        foreach ($resaveOptions as $oldOption => $newKey) {
            if (!isset($settings[$newKey])) {
                $settings[$newKey] = get_option($oldOption);

                delete_option($oldOption);

                $resave = true;
            }
        }

        if ($resave) {
            update_option(self::OPTIONS_KEY, $settings);
        }

        update_option('ITGALAXY_WC_1C_PLUGIN_VERSION', ITGALAXY_WC_1C_PLUGIN_VERSION);
    }

    public static function pluginActivation()
    {
        // https://developer.wordpress.org/reference/functions/is_plugin_active/
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            wp_die(
                esc_html__(
                    'To run the plug-in, you must first install and activate the WooCommerce plugin.',
                    'itgalaxy-woocommerce-1c'
                ),
                esc_html__(
                    'Error while activating the WooCommerce - 1C:Enterprise - Data Exchange',
                    'itgalaxy-woocommerce-1c'
                ),
                [
                    'back_link' => true
                ]
            );
            // Escape ok
        }

        self::setRoleCapabilities();
        self::addWcAttributesTableColumn();
        self::addMetaValueIndexes();
        self::copyRootEntryImportFile();

        // add option row in table for plugin settings
        if (get_option(self::OPTIONS_KEY) === false) {
            add_option(
                self::OPTIONS_KEY,
                [
                    'enable_exchange' => 1,
                    'use_file_zip' => 1,
                    'send_orders_last_success_export' => str_replace(' ', 'T', date_i18n('Y-m-d H:i')),
                    'log_days' => 5,
                    'enable_logs_protocol' => 1,
                    'enable_logs_changes' => 1
                ],
                '',
                'no'
            );
        }

        if (get_option(self::PURCHASE_CODE_OPTIONS_KEY) === false) {
            add_option(self::PURCHASE_CODE_OPTIONS_KEY, '', '', 'no');
        }

        if (get_option(self::OPTION_INFO_KEY) === false) {
            add_option(self::OPTION_INFO_KEY, [], '', 'no');
        }

        if (get_option('all_prices_types') === false) {
            add_option('all_prices_types', [], '', 'no');
        }

        if (get_option('itglx_wc1c_nomenclature_categories') === false) {
            add_option('itglx_wc1c_nomenclature_categories', [], '', 'no');
        }

        if (get_option('ITGALAXY_WC_1C_PLUGIN_VERSION') === false) {
            add_option('ITGALAXY_WC_1C_PLUGIN_VERSION', ITGALAXY_WC_1C_PLUGIN_VERSION, '', 'no');
        }
    }

    public static function pluginDeactivation()
    {
        // Nothing
    }

    public static function pluginUninstall()
    {
        // Nothing
    }

    private static function copyRootEntryImportFile()
    {
        if (!file_exists(ITGALAXY_WC_1C_PLUGIN_DIR . 'import-1c.php')) {
            return;
        }

        if (file_exists(ABSPATH . 'import-1c.php')) {
            return;
        }

        copy(ITGALAXY_WC_1C_PLUGIN_DIR . 'import-1c.php', ABSPATH . 'import-1c.php');
    }

    private static function addWcAttributesTableColumn()
    {
        global $wpdb;

        $dbName = DB_NAME;
        $columnExists = $wpdb->query(
            "SELECT * FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = '{$dbName}'
                  AND TABLE_NAME = '{$wpdb->prefix}woocommerce_attribute_taxonomies'
                  AND COLUMN_NAME = 'id_1c'"
        );

        if (!$columnExists) {
            $wpdb->query(
                "ALTER TABLE {$wpdb->prefix}woocommerce_attribute_taxonomies
                ADD id_1c varchar(200) NOT NULL"
            );
        }
    }

    private static function addMetaValueIndexes()
    {
        global $wpdb;

        // phpcs:disable
        if (
            !$wpdb->query(
                "SHOW KEYS FROM `{$wpdb->postmeta}` WHERE Key_name='meta_value-1c'"
            )
        ) {
            $wpdb->query(
                "ALTER TABLE `{$wpdb->postmeta}`" .
                " ADD INDEX `meta_value-1c` (`meta_value` (191)) COMMENT ''"
            );
        }

        if (
            !$wpdb->query(
                "SHOW KEYS FROM `{$wpdb->termmeta}` WHERE Key_name='meta_value-1c'"
            )
        ) {
            $wpdb->query(
                "ALTER TABLE `{$wpdb->termmeta}`" .
                " ADD INDEX `meta_value-1c` (`meta_value` (191)) COMMENT ''"
            );
        }
        // phpcs:enable
    }

    private static function setRoleCapabilities()
    {
        $roles = new \WP_Roles();

        foreach (self::capabilities() as $capGroup) {
            foreach ($capGroup as $cap) {
                $roles->add_cap('administrator', $cap);

                if (is_multisite()) {
                    $roles->add_cap('super_admin', $cap);
                }
            }
        }
    }

    private static function capabilities()
    {
        $capabilities = [
            'core' => [
                'manage_' . self::OPTIONS_KEY
            ]
        ];

        // https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
        flush_rewrite_rules(true);

        return $capabilities;
    }

    private function __clone()
    {
        // Nothing
    }
}
