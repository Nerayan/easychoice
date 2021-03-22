<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link        http://morkva.co.ua/
 * @since       1.0.0
 *
 * @package     morkvajustin-plugin
 * @subpackage  morkvajustin-plugin/includes
 */
/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    morkvajustin-plugin
 * @subpackage morkvajustin-plugin/includes
 * @author     MORKVA <hello@morkva.co.ua>
 */

require("class-morkvajustin-plugin-callbacks.php");

class MJS_Plugin_Loader
{

    /**
     * The array of pages for plugin menu
     *
     * @since 1.0.0
     * @access protected
     * @var array $pages 	Pages for plugin menu
     */
    protected $pages;

    /**
     * The array of subpages for plugin menu
     *
     * @since 1.0.0
     * @access protected
     * @var array $subpages 	Subpages for plugin menu
     */
    protected $subpages;

    /**
     * Array of settings groups fields for plugin
     *
     * @since 1.0.0
     * @access protected
     * @var array $settings
     */
    protected $settings;

    /**
     * Array of sections for settings fields for plugin
     *
     * @since 1.0.0
     * @access protected
     * @var array $sections
     */
    protected $sections;

    /**
     * Array of fields for settings fields for plugin
     *
     * @since 1.0.0
     * @access protected
     * @var array $fields
     */
    protected $fields;

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;
    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Object of callbacks class
     *
     * @since 	1.0.0
     * @access  protected
     * @var 	string $callbacks 		Class of callbacks
     */
    protected $callbacks;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        global $wp_settings_sections;
        $this->actions = array();
        $this->filters = array();
        $this->pages = array();
        $this->subpages = array();
        $this->settings = array();
        $this->sections = array();
        $this->fields = array();

        $this->callbacks = new MJS_Plugin_Callbacks();

        $this->add_settings_fields();
        $this->register_fields_sections();
        $this->register_settings_fields();

        $this->register_menu_pages();
        $this->register_menu_subpages();

        add_action('admin_menu', array(
            $this,
            'register_plugin_menu'
        ));
        add_action('add_meta_boxes', array(
            $this,
            'mv_add_meta_boxes'
        ));
        add_action('admin_init', array(
            $this,
            'register_plugin_settings'
        ));
        add_filter('manage_edit-shop_order_columns', array(
            $this,
            'woo_custom_column'
        ));
        add_action('manage_shop_order_posts_custom_column', array(
            $this,
            'woo_column_get_data'
        ));
        add_action('add_meta_boxes', array(
            $this,
            'add_invoice_meta_box'
        ));

        add_filter('wp_mail_from_name', array(
            $this,
            'my_mail_from_name'
        ));
    }
    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress action that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the action is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         The priority at which the function should be fired.
     * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {
        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );
        return $hooks;
    }
    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array(
                $hook['component'],
                $hook['callback']
            ), $hook['priority'], $hook['accepted_args']);
        }
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array(
                $hook['component'],
                $hook['callback']
            ), $hook['priority'], $hook['accepted_args']);
        }
    }

    /**
     * Registering plugin pages to menu
     *
     * @since 	1.0.0
     */
    public function register_menu_pages()
    {
        $this->pages = array(
            array(
                'page_title' => __('Justin', 'textdomain') ,
                'menu_title' => 'Justin',
                'capability' => 'manage_woocommerce',
                'menu_slug' => 'morkvajustin_plugin',
                'callback' => array(
                    $this,
                    'add_settings_page'
                ) ,
                'icon_url' => plugins_url("justin.png", __FILE__) ,
                'position' => 60
            )
        );

        return $this;
    }

    /**
     *	Add Plugin Settings page
     *
     *	@since 	1.0.0
     */
    public function add_settings_page()
    {
        require_once(JUSTIN_PLUGFOLDER . 'admin/partials/morkvajustin-plugin-settings.php');
    }

    /**
     * Registering subpages for menu of plugin
     *
     * @since 	1.0.0
     */
    public function register_menu_subpages()
    {
        $title = "Налаштування";

        if (get_option('invoice_short')) {
            $this->subpages = array(
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Налаштування',
                    'menu_title' => 'Налаштування',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_plugin',
                    'callback' => array(
                        $this,
                        'add_settings_page'
                    )
                ) ,
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Створити Накладну',
                    'menu_title' => 'Створити Накладну',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_invoice',
                    'callback' => array(
                        $this,
                        'add_invoice_page'
                    )
                ) ,
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Мої накладні',
                    'menu_title' => 'Мої накладні',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_invoices',
                    'callback' => array(
                        $this,
                        'invoices_page'
                    )
                ) ,
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Про плагін',
                    'menu_title' => 'Про плагін',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_about',
                    'callback' => array(
                        $this,
                        'about_page'
                    )
                )

                ,
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Шорткоди',
                    'menu_title' => 'Шорткоди',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_short',
                    'callback' => array(
                        $this,
                        'add_settings_page'
                    )
                ) ,
            );
        } else {
            $this->subpages = array(
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Налаштування',
                    'menu_title' => 'Налаштування',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_plugin',
                    'callback' => array(
                        $this,
                        'add_settings_page'
                    )
                ) ,
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Створити Накладну',
                    'menu_title' => 'Створити Накладну',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_invoice',
                    'callback' => array(
                        $this,
                        'add_invoice_page'
                    )
                ) ,
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Мої накладні',
                    'menu_title' => 'Мої накладні',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_invoices',
                    'callback' => array(
                        $this,
                        'invoices_page'
                    )
                ) ,
                array(
                    'parent_slug' => 'morkvajustin_plugin',
                    'page_title' => 'Про плагін',
                    'menu_title' => 'Про плагін',
                    'capability' => 'manage_woocommerce',
                    'menu_slug' => 'morkvajustin_about',
                    'callback' => array(
                        $this,
                        'about_page'
                    )
                )

                ,
            );
        }

        return $this;
    }

    /**
     * Adding subpage of plugin
     *
     * @since 1.0.0
     */
    public function add_invoice_page()
    {
        require_once(JUSTIN_PLUGFOLDER . '/admin/partials/morkvajustin-plugin-form.php');
    }

    /**
     * Add invoices subpage of plugin
     *
     * @since 1.0.0
     */
    public function invoices_page()
    {
        $path = JUSTIN_PLUGFOLDER . '/admin/partials/morkvajustin-plugin-invoices-page.php';
        if (file_exists($path)) {
            require_once($path);
        } else {
            $path = JUSTIN_PLUGFOLDER . '/admin/partials/morkvajustin-plugin-invoices-page-demo.php';
            require_once($path);
        }
    }

    /**
     * Add about page of plugin
     *
     * @since 1.0.0
     */
    public function about_page()
    {
        $path = JUSTIN_PLUGFOLDER . '/admin/partials/morkvajustin-plugin-about-page.php';
        require_once($path);
    }

    /**
     * Register plugin menu
     *
     * @since 	1.0.0
     */
    public function register_plugin_menu()
    {
        foreach ($this->pages as $page) {
            add_menu_page($page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position']);
        }

        foreach ($this->subpages as $subpage) {
            add_submenu_page($subpage['parent_slug'], $subpage['page_title'], $subpage['menu_title'], $subpage['capability'], $subpage['menu_slug'], $subpage['callback']);
        }
    }

    /**
     * Add setting fields for plugin
     *
     * @since 	1.0.0
     */
    public function add_settings_fields()
    {
        $args = array(

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_area_name'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_area'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_city_name'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_city'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_warehouse_name'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_warehouse'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_nova_poshta_sender_building'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_nova_poshta_sender_flat'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_address_name'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'woocommerce_morkvajustin_shipping_method_address'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'morkvajustin_apikey'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'zone_example'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'morkvajustin_calculate'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'show_calc'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'autoinvoice'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'city'
            ) ,
            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'justin_names'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'justin_phone'
            ) ,
            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'justin_default_price'
            ) ,
            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'flat'
            ) ,
            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'warehouse'
            ) ,
            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'justin_invoice_description'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'invoice_short'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'invoice_dpay'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'justin_invoice_payer'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'invoice_cpay'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'invoice_cron'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'np_invoice_auto_ttn'
            ) ,

            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'morkvajustin_email_template'
            ) ,
            array(
                'option_group' => 'morkvajustin_options_group',
                'option_name' => 'morkvajustin_card'
            ) ,

        );

        $this->settings = $args;

        return $this;
    }

    /**
     *	Register all sections for settings fields
     *
     *	@since 	 1.0.0
     */
    public function register_fields_sections()
    {
        $args = array(
            array(
                'id' => 'morkvajustin_admin_index',
                'title' => 'Налаштування',
                'callback' => function () {
                    echo "";
                }
                ,
                'page' => 'morkvajustin_plugin'
            )
        );

        $this->sections = $args;

        return $this;
    }

    /**
     * Register settings callbacks fields
     *
     * @since 	1.0.0
     */
    public function register_settings_fields()
    {
        $args = array(
            //start base settings
            array(
                'id' => 'morkvajustin_apikey',
                'title' => 'API ключ',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustingetapi'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'morkvajustin_apikey',
                    'class' => 'basesettings allsettings show'
                )
            ) ,
            array(
                'id' => 'morkvajustin_card',
                'title' => 'Номер картки для зарахування наложеного платежу',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinEmailSubject'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'morkvajustin_card',
                    'class' => 'allsettings additional morkvajustin_card'
                )
            ) ,

            array(
                'id' => 'morkvajustin_calculate',
                'title' => 'Розраховувати вартість доставки при оформленні',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustin_address_shpping_notuse'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'justin_names',
                    'class' => 'basesettings allsettings show'
                )
            ) ,

            array(
                'id' => 'justin_default_price',
                'title' => 'Фіксована ціна доставки',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustin_justin_default_price'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'justin_default_price',
                    'class' => 'basesettings allsettings show'
                )
            ) ,

            array(
                'id' => 'justin_names',
                'title' => 'Назва (П.І.Б. повністю) Відправника',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinNames'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'justin_names',
                    'class' => 'basesettings allsettings show'
                )
            ) ,
            array(
                'id' => 'justin_phone',
                'title' => 'Номер телефону',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinPhone'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'justin_phone',
                    'class' => 'basesettings allsettings show'
                )
            ) ,
            array(
                'id' => 'regiond',
                'title' => 'Відправка з:',
                'callback' => array(
                    $this->callbacks,
                    'emptyfunccalbask'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'region',
                    'class' => 'h3as basesettings allsettings show'
                )
            ) ,

            array(
                'id' => 'city',
                'title' => 'Місто',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinSelectCity'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'city',
                    'class' => 'basesettings allsettings show'
                )
            ) ,
            array(
                'id' => 'warehouse',
                'title' => 'З віділення',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinWarehouseAddress'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'warehouse',
                    'class' => 'basesettings allsettings show'
                )
            ) ,


            array(
                'id' => 'justin_invoice_description',
                'title' => 'Опис відправлення (за замовчуванням)',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinInvoiceDescription'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'justin_invoice_description',
                    'class' => 'allsettings additional'
                )
            ) ,

            array(
                'id' => 'justin_invoice_payer',
                'title' => 'Хто платить за доставку за замовчуванням?',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinInvoicepayer'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'justin_invoice_payer',
                    'class' => 'allsettings additional'
                )
            ) ,

            // start auto settings
            array(
                'id' => 'invoice_dpay',
                'title' => 'Автоматизація залежно від суми замовлення',
                'callback' => array(
                    $this->callbacks,
                    'morkvajustinInvoicedpay'
                ) ,
                'page' => 'morkvajustin_plugin',
                'section' => 'morkvajustin_admin_index',
                'args' => array(
                    'label_for' => 'invoice_dpay',
                    'class' => 'autosettings allsettings'
                )
            ) ,

        );

        $this->fields = $args;

        return $this;
    }

    /**
     *	Registering all settings fields for plugin
     *
     *	@since 	 1.0.0
     */
    public function register_plugin_settings()
    {
        foreach ($this->settings as $setting) {
            register_setting($setting["option_group"], $setting["option_name"], (isset($setting["callback"]) ? $setting["callback"] : ''));
        }

        foreach ($this->sections as $section) {
            add_settings_section($section["id"], $section["title"], (isset($section["callback"]) ? $section["callback"] : ''), $section["page"]);
        }

        foreach ($this->fields as $field) {
            add_settings_field($field["id"], $field["title"], (isset($field["callback"]) ? $field["callback"] : ''), $field["page"], $field["section"], (isset($field["args"]) ? $field["args"] : ''));
        }
    }

    /**
     * Add meta box to WooCommerce order's page
     *
     * @since 1.0.0
     */
    public function add_plugin_meta_box()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_GET["post"])) {
            $order_id = intval($_GET["post"]);

            $order_data0 = wc_get_order($order_id);
            $order_data = $order_data0->get_data();

            $methodid = '';
            foreach ($order_data0->get_items('shipping') as $item_id => $shipping_item_obj) {
                $shipping_item_data = $shipping_item_obj->get_data();
                $methodid = $shipping_item_data['method_id'];
            }

            if (strpos($methodid, 'justin_shipping') !== false) {
                echo '<style>#justin_newttn{display:block;}</style>';
            } else {
                echo '<style>#justin_newttn{display:none;}</style>';
            }

            if (isset($order_id)) {
                $order_data = wc_get_order($order_id);
                $order = $order_data->get_data();
                $_SESSION['order_data'] = $order;
                $_SESSION['order_id'] = $order_id;
            }
            echo '<img src="' . JUSTIN_PLUGURL . '/includes/justin.png"
		 style="height: 25px;width: 25px; margin-right: 20px; margin-top: 2px;">';
            echo "<a class='button button-primary send' href='admin.php?page=morkvajustin_invoice'>Створити накладну</a>";
        } else {
            echo '<style>#justin_newttn{display:none;}</style>';
        }
    }

    /**
     * Generating meta box
     *
     * @since 1.0.0
     */
    public function mv_add_meta_boxes()
    {
        add_meta_box('justin_newttn', __('Відправлення Justin', 'woocommerce'), array(
            $this,
            'add_plugin_meta_box'
        ), 'shop_order', 'side', 'core');
    }

    /**
     * Creating custom column at woocommerce order page
     *
     * @since 1.1.0
     */
    public function woo_custom_column($columns)
    {
        $columns['created_invoice'] = 'Накладна';
        $columns['invoice_number'] = 'Номер накладної';
        return $columns;
    }

    /**
     * Getting data of order column at order page
     *
     * @since 1.1.0
     */
    public function woo_column_get_data($column)
    {
        global $post;
        $data = get_post_meta($post->ID);

        $order_id = $post->ID;
        $selected_order = wc_get_order($post->ID);
        $order = $selected_order->get_data();
        $meta_ttn = get_post_meta($order_id, 'novaposhta_ttn', true);

        if ($column == 'created_invoice') { //will be deprecated
            global $wpdb;

            $order_id = $post->ID;
            $results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}novaposhta_ttn_invoices WHERE order_id = '$order_id'", ARRAY_A);
            $img = "";
            if (!empty($results) || !empty($meta_ttn)) {
                $img = "justin.png";
            } else {
                $img = 'justin.png'; //grey
            }
            echo '<img src="' . JUSTIN_PLUGURL . '/includes/' . $img . '" />';
        }

        if ($column == 'invoice_number') {
            global $wpdb;

            $order_id = $post->ID;
            $number_result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}novaposhta_ttn_invoices WHERE order_id = '$order_id'", ARRAY_A);

            if (!empty($results)) {
                echo '<a taget="_blank" href="https://novaposhta.ua/tracking/?cargo_number=' . $number_result["order_invoice"] . '">' . $number_result["order_invoice"] . '+</a>';
            } else {
                if (isset($meta_ttn)) {
                    echo '<a taget="_blank" href="https://novaposhta.ua/tracking/?cargo_number=' . $meta_ttn . '">' . $meta_ttn . '</a>';
                } else {
                    echo "";
                }
            }
        }
    }

    /**
     * Add meta box with invoice information
     *
     * @since 1.1.0
     */
    public function add_invoice_meta_box()
    {
        if (isset($_GET["post"])) {
            add_meta_box('justin_invoice_other_fields', __('Justin Накладна', 'woocommerce'), array(
                $this,
                'invoice_meta_box_info'
            ), 'shop_order', 'side', 'core');
        }
    }

    /**
     * Add info of invoice meta box
     *
     * @since 1.1.0
     */
    public function invoice_meta_box_info()
    {
        //function to echo ttn box in order
    }

    /**
     * From name email
     *
     * @since 1.1.3
     */
    public function my_mail_from_name($name)
    {
        //$bloginfo = get_bloginfo();
        //$title = $bloginfo->name;
        return get_option('blogname');
    }
}
