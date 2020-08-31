<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Базовый класс плагина
 * Создаёт настройки, отображает опции в админки
 * Вызывает хуки ВордПресс
 */
class BuyCore {

    /**
     * Полное название плагина
     */
    const NAME_PLUGIN = 'Buy one click WooCommerce';

    /**
     * Имя папки с плагином без слэшей
     */
    const PATCH_PLUGIN = 'buy-one-click-woocommerce';

    /**
     * Название пункта подменю
     */
    const NAME_SUB_MENU = 'BuyOneClick';

    /**
     * URL страницы подменю
     */
    const URL_SUB_MENU = 'buyone';

    /**
     * Путь до страницы опций плагина HTML
     */
    const OPTIONS_NAME_PAGE = 'page/option1.php';

    /**
     * Имя индексного файла
     */
    const INDEX_NAME_FILE = 'buycli-index.php';

    /**
     * Версия ядра
     */
    const VERSION = '1.8.8';

    /**
     * Настройки плагина
     * @uses [enable_button] - Включатель кнопки
     * @uses [enable_button_shortcod] Показ кнопки шорткода
     * @uses [namebutton] - Название кнопки "купить"
     * @uses [positionbutton]- Расположение кнопк "купить"
     * @uses [infotovar_chek] - Показывать или нет информацию о товаре в окне
     * @uses [fio_chek] - Запрос на ФИО
     * @uses [fon_chek] - Запрос телефона
     * @uses [email_chek] - Запрос email
     * @uses [dopik_chek] - Показывать поле дополнительной информации
     * @uses [fio_descript] - Описание поля ФИО
     * @uses [fon_descript] - Описание поля телефон
     * @uses [email_descript] - Описание поля email
     * @uses [dopik_descript] - Описание поля email
     * @uses [butform_descript] - Название кнопки в форме отправки данных о покупателе
     * @uses [infotovar_chek] - Показывать или нет информацию о товаре в окне
     * @uses [success] - Сообщение об успешном совершение заказа в форме
     * @uses [fio_verifi] - Обязательно поле ФИО
     * @uses [fon_verifi] - Обязательно поле ФИО
     * @uses [fon_format] - Формат телефона
     * @uses [email_verifi] - Обязательно поле ФИО
     * @uses [dopik_verifi] - Обязательно поле ФИО
     * @uses [success_action] - radio действия после закрытия
     * @uses [success_action_close] - Время в мс до закрытия формы заказа
     * @uses [success_action_message] - Сообщение после заказа
     * @uses [success_action_redirect] - URL редиректа после заказа
     * @uses [regex_fon] Регулярное выражение проверки телефона
     * @uses [add_tableorder_woo] Включает добавление заказов в таблицу Woo
     * 
     */
    static $buyoptions;

    /**
     * Настройки уведомлений плагина
     * @uses [namemag] - Название магазина
     * @uses [emailfrom] - Email для ответов
     * @uses [emailbbc] - Email для копий
     * @uses [infozakaz_chek] - Отправка клиенту сообщения о заказе
     * @uses [dopiczakaz_chek] - Отправка клиенту доп сообщения
     * @uses [dopiczakaz] - Дополнительная информация
     */
    static $buynotification;

    /**
     *
     * Журнал заказов. Вложенные массивы имеют следующие данные
     * @uses [time] - Время создания заказа
     * @uses [idtovar] - ID товара или записи Wordpress
     * @uses [txtname] - ФИО клиента
     * @uses [txtphone] - Номер телефона клиента
     * @uses [txtemail] - Email клиента
     * @uses [nametovar] - Название товара
     * @uses [pricetovar] - Цена товара
     * @uses [message] - Сообщение от клиента
     * @uses [linktovar] - ссылка на товар вместе с тегами и подписью
     * @uses [smslog] - Лог СМС
     */
    static $buyzakaz;

    /**
     * Опции СМСЦЕНТРА
     * @uses [login] - логин пользователя
     * @uses [password] - Пароль или MD5-хеш пароля в нижнем регистре  
     * @uses [methodpost] - Использовать метод POST
     * @uses [https] - использовать HTTPS протокол
     * @uses [charset] -кодировка сообщения: utf-8, koi8-r или windows-1251 (по умолчанию)
     * @uses [debug] - флаг отладки
     * @uses [smtpfrom] - e-mail адрес отправителя
     * @uses [smshablon] - SMS шаблон
     * @uses [enable_smsc] - Вкючение СМС отправки на сайте
     */
    static $buysmscoptions;

    /**
     * Работа с вариативными товарами
     * @var type 
     */
    public static $variation = FALSE;

    /**
     * Конструктор класса
     */
    public function __construct() {
        if (class_exists('BuyVariationClass')) {
            self::$variation = TRUE;
        }

        $help = \Coderun\BuyOneClick\Help::getInstance();

        $options = $help->get_options();

        self::$buyoptions = $options['buyoptions']; //Загрука опций из базы
        self::$buyzakaz = $options['buyzakaz']; //Загрука опций из базы
        self::$buynotification = $options['buynotification']; //Загрука опций из базы
        self::$buysmscoptions = $options['buysmscoptions']; //Получаем настройки смсцентра из опций
        $this->addAction();
        $this->addOptions();
    }

    /**
     * Подключение функций через add_action Wordpress
     */
    public function addAction() {
        $buyoptions = self::$buyoptions;
        if (!empty($buyoptions['enable_button']) and $buyoptions['enable_button'] == 'on') {
            $position = $buyoptions['positionbutton']; //Позиция кнопки
            if (self::$variation) {
                $strPosition = BuyVariationClass::getPositionButton();
                if ($strPosition !== FALSE) {
                    $position = $strPosition;
                }
            }
            add_action($position, array($this, 'styleAddFrontPage')); //Стили фронта
            add_action($position, array($this, 'scriptAddFrontPage')); //Скрипты фронта
            add_action($position, array('BuyFunction', 'viewBuyButton')); //Кнопка заказать
            //add_action($position, array('BuyFunction', 'viewBuyForm')); //Форма заказа с 25.08.2016 ajax
            //add_action($position, array('BuyFunction', 'viewBuyMessage')); //Дополнительное сообщение с 8.09.2016 в форме
            //Положение в категории товаров
            if (!empty($buyoptions['enable_button_category']) and $buyoptions['enable_button_category'] == 'on') {
                $position_category = $buyoptions['positionbutton_category']; //Позиция кнопки
                add_action($position_category, array('BuyFunction', 'viewBuyButton')); //Кнопка заказать
                add_action($position_category, array($this, 'styleAddFrontPage')); //Стили фронта
                add_action($position_category, array($this, 'scriptAddFrontPage')); //Скрипты фронта
                //add_action($position_category, array('BuyFunction', 'viewBuyMessage')); //Дополнительное сообщение с 8.09.2016 в форме
            }
        }
        add_action('admin_menu', array($this, 'adminOptions'));
        //add_action('woocommerce_receipt_buyclik', array('BuyFunction', 'viewBuyForm')); // Подтверждение заказа


        add_filter('plugin_action_links', array($this, 'pluginLinkSetting'), 10, 2); //Настройка на странице плагинов
        //

        add_action('wp_head', array($this, 'jsVariableHead'));
    }

    /**
     * Создаёт переменные в шапке, одна из них это обработчик ajax
     */
    public function jsVariableHead() {

        $buyoptions = self::$buyoptions;

        $variables = array('ajaxurl' => admin_url('admin-ajax.php'));
        if (self::$variation) {
            $variables['variation'] = 1;
        } else {
            $variables['variation'] = 0;
        }

        //Формат телефона 

        if (isset($buyoptions['fon_format_input']) && strlen($buyoptions['fon_format_input']) > 3) {
            $buyoptions['fon_format_input'] = str_replace(['\'', '"'], [], $buyoptions['fon_format_input']);
            $variables['tel_mask'] = $buyoptions['fon_format_input'];
        }

        //Режим работы плагина

        if (isset($buyoptions['plugin_work_mode'])) {
            $variables['work_mode'] = intval($buyoptions['plugin_work_mode']);
        } else {
            $variables['work_mode'] = 0;
        }


        $str = '';
        $str .= "<script type=\"text/javascript\">\n";
        $str .= " /* <![CDATA[ */\n";
        $str .= "var buyone_ajax = " . json_encode($variables) . "; \n";
        $str .= " /* ]]> */\n";
        // $str .=
        $str .= "</script>\n";
        echo $str;
    }

    /**
     * Операции выполняемые при деактивации плагина
     */
    public function deactivationPlugin() {
        delete_option('buyoptions');
        delete_option('buyzakaz');
        delete_option('buynotification');
        delete_option('buysmscoptions');
        remove_shortcode('viewBuyButton');
    }

    /**
     * Добавление опций в базу Wordpress
     */
    public function addOptions() {
        add_option('buyoptions', array()); //массив настроек плагина
        add_option('buyzakaz', array()); //массив Заказов через форму
        add_option('buynotification', array()); //Массив настроек уведомлений
        add_option('buysmscoptions', array()); //Настройки smsc
    }

    /**
     * Меню или суб меню плагина
     */
    public function adminOptions() {
        //Подключается если есть менюя от Woocommerce
        $page_option = add_submenu_page('woocommerce', self::NAME_SUB_MENU, self::NAME_SUB_MENU, 'manage_woocommerce', self::URL_SUB_MENU, array($this, 'showSettingPage'));
        add_action('admin_print_styles-' . $page_option, array($this, 'styleAddpage')); //загружаем стили только для страницы плагина
        add_action('admin_print_scripts-' . $page_option, array($this, 'scriptAddpage')); //Скрипты
    }

    /**
     * Стили для страницы плагина
     */
    public function styleAddpage() {
        wp_register_style('buybootstrapcss1', plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'bootstrap/css/bootstrap.css');
        wp_enqueue_style('buybootstrapcss1');
        wp_register_style('buyadmincss2', plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'css/admin.css');
        wp_enqueue_style('buyadmincss2');
    }

    /**
     * Скрипты для страницы плагина
     */
    public function scriptAddpage() {
        //wp_enqueue_script('buyonclickjs', plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'js/form.js', ['jquery'], self::VERSION);
        wp_enqueue_script('buybootstrapjs1', plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'bootstrap/js/bootstrap.js', ['jquery'], self::VERSION);
        wp_enqueue_script('buyorder', plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'js/admin_order.js', ['jquery'], self::VERSION);


        wp_localize_script('buyorder', 'buyadminnonce', array(//Установка проверочного кода
            'url' => admin_url(plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'js/admin_order.js'),
            'nonce' => wp_create_nonce('superKey')
        ));
    }

    /**
     * Стили для фронтэнда
     */
    public function styleAddFrontPage() {
        $numForm = null;
        $buyoptions = get_option('buyoptions');
        if (isset($buyoptions['form_style_color'])) {
            $numForm = $buyoptions['form_style_color'];
        }
        if (empty($numForm)) {
            $numForm = 1;
        }

        $wp_uploads_dir = wp_get_upload_dir();

        if (file_exists($wp_uploads_dir['basedir'] . '/' . self::PATCH_PLUGIN . '/css/form_' . $numForm . '.css')) {
            wp_register_style('buyonclickcss2', $wp_uploads_dir['baseurl'] . '/' . self::PATCH_PLUGIN . '/css/form_' . $numForm . '.css');
        } elseif (file_exists(get_stylesheet_directory() . '/' . self::PATCH_PLUGIN . '/css/form_' . $numForm . '.css')) {
            wp_register_style('buyonclickcss2', get_stylesheet_directory_uri() . '/' . self::PATCH_PLUGIN . '/css/form_' . $numForm . '.css');
        } else {
            wp_register_style('buyonclickcss2', plugins_url() . '/' . self::PATCH_PLUGIN . '/templates/css/form_' . $numForm . '.css');
        }

        if (file_exists($wp_uploads_dir['basedir'] . '/' . self::PATCH_PLUGIN . '/css/formmessage.css')) {
            wp_register_style('buyonclickfrontcss3', $wp_uploads_dir['baseurl'] . '/' . self::PATCH_PLUGIN . '/css/formmessage.css');
        } elseif (file_exists(get_stylesheet_directory() . '/' . self::PATCH_PLUGIN . '/css/formmessage.css')) {
            wp_register_style('buyonclickfrontcss3', get_stylesheet_directory_uri() . '/' . self::PATCH_PLUGIN . '/css/formmessage.css');
        } else {
            wp_register_style('buyonclickfrontcss3', plugins_url() . '/' . self::PATCH_PLUGIN . '/templates/css/formmessage.css');
        }

        wp_enqueue_style('buyonclickfrontcss3');
        wp_enqueue_style('buyonclickcss2');

        wp_register_style('loading', plugins_url() . '/' . self::PATCH_PLUGIN . '/css//loading-btn/loading.css');
        wp_register_style('loading-btn', plugins_url() . '/' . self::PATCH_PLUGIN . '/css/loading-btn/loading-btn.css');

        wp_enqueue_style('loading');
        wp_enqueue_style('loading-btn');
    }

    /**
     * Скрипты для фронтэнда
     */
    public function scriptAddFrontPage() {
        wp_enqueue_script('buyonclickfrontjs', plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'js/form.js', ['jquery', 'buymaskedinput'], self::VERSION);
        wp_enqueue_script('buymaskedinput', plugins_url() . '/' . self::PATCH_PLUGIN . '/' . 'js/jquery.maskedinput.min.js', ['jquery'], self::VERSION);
    }

    /**
     * Страница плагина
     */
    public function showSettingPage() {
        include_once WP_PLUGIN_DIR . '/' . self::PATCH_PLUGIN . '/' . self::OPTIONS_NAME_PAGE;
    }

    /**
     * Активная вкладка в админпанели плагина
     * @return string css Класс для активной вкладки
     */
    static public function adminActiveTab($tab_name = null, $tab = null) {

        if (isset($_GET['tab']) && !$tab)
            $tab = $_GET['tab'];
        else
            $tab = 'general';

        $output = '';
        if (isset($tab_name) && $tab_name) {
            if ($tab_name == $tab)
                $output = ' nav-tab-active';
        }
        echo $output;
    }

    /**
     * Подключает нужную страницу исходя из вкладки на страницы настроек плагина
     * @result include_once tab{номер вкладки}-option1.php
     */
    static public function tabViwer() {
        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
            switch ($tab) {
                case 'notification':
                    include_once WP_PLUGIN_DIR . '/' . self::PATCH_PLUGIN . '/page/tab2-option1.php';
                    break;
                case 'orders':
                    include_once WP_PLUGIN_DIR . '/' . self::PATCH_PLUGIN . '/page/tab3-option1.php';
                    break;
                case 'help':
                    include_once WP_PLUGIN_DIR . '/' . self::PATCH_PLUGIN . '/page/tab4-option1.php';
                    break;
                default :
                    include_once WP_PLUGIN_DIR . '/' . self::PATCH_PLUGIN . '/page/tab1-option1.php';
            }
        } else {
            include_once WP_PLUGIN_DIR . '/' . self::PATCH_PLUGIN . '/page/tab1-option1.php';
        }
    }

    /**
     * Добавляет пункт настроек на странице активированных плагинов
     */
    public function pluginLinkSetting($links, $file) {
        $this_plugin = self::PATCH_PLUGIN . '/' . self::INDEX_NAME_FILE;
        if ($file == $this_plugin) {
            $settings_link1 = '<a href="admin.php?page=' . self::URL_SUB_MENU . '">' . __("Settings", "default") . '</a>';
            array_unshift($links, $settings_link1);
        }
        return $links;
    }

    public static function get_template_path() {
        return self::PATCH_PLUGIN;
    }

}
