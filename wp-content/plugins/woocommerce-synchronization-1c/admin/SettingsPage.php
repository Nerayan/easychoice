<?php
namespace Itgalaxy\Wc\Exchange1c\Admin;

use Itgalaxy\Wc\Exchange1c\Admin\PageParts\CheckExistsExchangeEntryPointFile;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\CheckExistsWooCommerceAttributesTableColumn;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\CheckPhpExtensionNotice;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionAccountingSystemAuth;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionForDebugging;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionForExchangeOrders;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionForPrices;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionLicense;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionLogging;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionNomenclatureExchangeConfigure;
use Itgalaxy\Wc\Exchange1c\Admin\PageParts\SectionTempCatalogInfo;
use Itgalaxy\Wc\Exchange1c\Includes\AssetsHelper;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class SettingsPage
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
        // https://developer.wordpress.org/reference/hooks/admin_menu/
        add_action('admin_menu', [$this, 'addSubmenu'], 1000); // 1000 - fix priority for Admin Menu Editor

        if (isset($_GET['page']) && $_GET['page'] === Bootstrap::OPTIONS_KEY) {
            // https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
            add_action('admin_enqueue_scripts', function () {
                // https://developer.wordpress.org/reference/functions/wp_enqueue_style/
                wp_enqueue_style(
                    'itgalaxy-woocommerce-1c-page-css',
                    AssetsHelper::getPathAssetFile('/admin/css/app.css'),
                    false,
                    null
                );

                // https://developer.wordpress.org/reference/functions/wp_enqueue_script/
                wp_enqueue_script(
                    'itgalaxy-woocommerce-1c-page-js',
                    AssetsHelper::getPathAssetFile('/admin/js/app.js'),
                    false,
                    false
                );
            });
        }
    }

    public function addSubmenu()
    {
        // https://developer.wordpress.org/reference/functions/add_submenu_page/
        add_submenu_page(
            'woocommerce',
            esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
            esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
            'manage_woocommerce',
            Bootstrap::OPTIONS_KEY,
            [$this, 'page']
        );
    }

    public function page()
    {
        if (isset($_POST['option_page_synchronization_from_1c_hidden']) && $_POST['option_page_synchronization_from_1c_hidden'] == 1) {
            if (!empty($_POST['empty_price_type_key'])) {
                $allPricesTypes = [];
                $allPricesTypes[$_POST['empty_price_type_key']] = $_POST['empty_price_type_name'];
                update_option('all_prices_types', $allPricesTypes);
                $_POST[Bootstrap::OPTIONS_KEY]['price_type_1'] = $_POST['empty_price_type_key'];
            }

            if (isset($_POST[Bootstrap::OPTIONS_KEY])) {
                update_option(Bootstrap::OPTIONS_KEY, $_POST[Bootstrap::OPTIONS_KEY]);
            } else {
                update_option(Bootstrap::OPTIONS_KEY, []);
            }

            wp_redirect(
                admin_url()
                . 'admin.php?page='
                . Bootstrap::OPTIONS_KEY
                . '&updated'
            );
        }

        if (isset($_GET['updated'])) {
            ?>
            <div class="updated">
                <p>
                    <strong>
                        <?php esc_html_e('Settings have been saved.', 'itgalaxy-woocommerce-1c'); ?>
                    </strong>
                </p>
            </div>
            <?php
        }

        //check extensions end show notices
        CheckPhpExtensionNotice::render();

        // check exists exchange entry point file
        CheckExistsExchangeEntryPointFile::render();

        // maybe column was not added when activating the plugin
        CheckExistsWooCommerceAttributesTableColumn::render();
        ?>
        <div id="poststuff" class="wrap woocommerce">
            <h1><?php esc_html_e('Sync settings with 1C', 'itgalaxy-woocommerce-1c'); ?></h1>
            <?php
            echo sprintf(
                '%1$s <a href="%2$s" target="_blank">%3$s</a>. %4$s.',
                esc_html__('Plugin documentation: ', 'itgalaxy-woocommerce-1c'),
                esc_url(
                    'https://itgalaxy.company/software/wordpress-woocommerce-1c-%d0%bf%d1%80%d0%b5%d0%b4%d0%bf%d1%80'
                    . '%d0%b8%d1%8f%d1%82%d0%b8%d0%b5-%d0%be%d0%b1%d0%bc%d0%b5%d0%bd-%d0%b4%d0%b0%d0%bd%d0%bd%d1%8b%d0'
                    . '%bc%d0%b8/woocommerce-1c%d0%bf%d1%80%d0%b5%d0%b4%d0%bf%d1%80%d0%b8%d1%8f%d1%82%d0%b8%d0%b5-%d0'
                    . '%be%d0%b1%d0%bc%d0%b5%d0%bd-%d0%b4%d0%b0%d0%bd%d0%bd%d1%8b%d0%bc%d0%b8-%d0%b8%d0%bd%d1%81/'
                ),
                esc_html__('open', 'itgalaxy-woocommerce-1c'),
                esc_html__('Or open the folder `documentation` in the plugin and open index.html', 'itgalaxy-woocommerce-1c')
            );
            ?>
            <hr>
            <form method="post" action="#">
                <input type="hidden" name="option_page_synchronization_from_1c_hidden" value="1">
                <?php
                SectionTempCatalogInfo::render();
                SectionAccountingSystemAuth::render();
                SectionNomenclatureExchangeConfigure::render();
                SectionForPrices::render();
                SectionForExchangeOrders::render();
                SectionForDebugging::render();
                SectionLogging::render();
                ?>
                <hr>
                <p class="submit">
                    <input type="submit"
                        class="button button-primary"
                        value="<?php esc_attr_e('Save settings', 'itgalaxy-woocommerce-1c'); ?>"
                        name="Submit">
                </p>
            </form>
            <?php SectionLicense::render(); ?>
        </div>
        <?php
    }
}
