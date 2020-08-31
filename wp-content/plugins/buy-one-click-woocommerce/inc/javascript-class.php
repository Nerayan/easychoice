<?php

if (!defined('ABSPATH')) {
    exit;
}

use Coderun\BuyOneClick\Help;

/**
 * Класс для работы с JavaScript функциями отправляемыми через скрипты
 */
class BuyJavaScript {

    /**
     * Конструктор класса
     */
    public function __construct() {
        $this->addaction();
    }

    /**
     * Адды
     */
    public function addaction() {
        add_action('wp_ajax_buybuttonform', array($this, 'ajaxBuyButtonForm')); //buybuttonform Посылается из js файла
        add_action('wp_ajax_nopriv_buybuttonform', array($this, 'ajaxBuyButtonForm')); //buybuttonform Посылается из js файла
        add_action('wp_ajax_removeorder', array($this, 'ajaxRemoveOrderId'));
        add_action('wp_ajax_nopriv_removeorder', array($this, 'ajaxRemoveOrderId'));
        add_action('wp_ajax_updatestatus', array($this, 'ajaxStatusOrderId'));
        add_action('wp_ajax_nopriv_updatestatus', array($this, 'ajaxStatusOrderId'));
        add_action('wp_ajax_removeorderall', array($this, 'ajaxRemoveOrderAll'));
        add_action('wp_ajax_nopriv_removeorderall', array($this, 'ajaxRemoveOrderAll'));
        add_action('wp_ajax_getViewForm', array($this, 'ajaxgetViewForm')); //Запрос формы
        add_action('wp_ajax_nopriv_getViewForm', array($this, 'ajaxgetViewForm')); //Запрос формы
        add_action('wp_ajax_getViewFormCustom', array($this, 'ajaxgetViewFormCustom')); //Запрос Кастомной формы
        add_action('wp_ajax_nopriv_getViewFormCustom', array($this, 'ajaxgetViewFormCustom')); //Запрос Кастомной формы
        add_action('wp_ajax_add_to_cart', array($this, 'add_to_cart')); //Добавление в корзину и отправка в оформление
        add_action('wp_ajax_nopriv_add_to_cart', array($this, 'add_to_cart')); //Добавление в корзину и отправка в оформление
        //
    }

    /**
     * Проверка обязательных полей
     */
    protected static function checkRequireField($form) {

        $options = Help::getInstance()->get_options();

        $params = $options['buyoptions'];

        if (empty($params)) {
            return true;
        }

        if (!empty($params['email_verifi']) && empty($form['user_email'])) {
            throw new Exception(__('Email field is required', 'coderun-oneclickwoo'), 200);
        }

        if (!empty($params['fio_verifi']) && empty($form['user_name'])) {
            throw new Exception(__('Name field is required', 'coderun-oneclickwoo'), 200);
        }

        if (!empty($params['fon_verifi']) && empty($form['user_phone'])) {
            throw new Exception(__('Telephone field is required', 'coderun-oneclickwoo'), 200);
        }

        if (!empty($params['dopik_verifi']) && empty($form['user_cooment'])) {
            throw new Exception(__('Message field is required', 'coderun-oneclickwoo'), 200);
        }

        if (!empty($params['conset_personal_data_enabled']) && empty(boolval($form['check_conset_personal_data']))) {
            throw new Exception(__('You need to give consent', 'coderun-oneclickwoo'), 200);
        }
    }

    /**
     * Выбросит исключение, если форма отправляетс чаще чем N секунд
     * @param int $product_id ид товара
     * @throws Exception
     */
    protected static function checkLimitSendForm($product_id) {

        $options = Help::getInstance()->get_options();

        $params = $options['buyoptions'];

        //Лимит отправки формы
        $limit_time = intval($params['time_limit_send_form']);

        $limit_message = __('You have already sent an order!', 'coderun-oneclickwoo');

        if (empty($limit_time)) {
            $limit_time = 10;
        }
        if (!empty($params['time_limit_message'])) {
            $limit_message = $params['time_limit_message'];
        }

        $key = 'ORDER_LAST_DATE_' . $product_id;

        if (empty($_SESSION['BUY_ONE_CLICK_WOOCOMMERCE'][$key])) {//Установка
            $_SESSION['BUY_ONE_CLICK_WOOCOMMERCE'][$key] = time();
        } else {
            if (($_SESSION['BUY_ONE_CLICK_WOOCOMMERCE'][$key] + $limit_time) > time()) {
                throw new Exception($limit_message, 200);
            } else {
                $_SESSION['BUY_ONE_CLICK_WOOCOMMERCE'][$key] = time();
            }
        }
        //
    }

    /**
     * Функция выполняется после нажатия на кнопку в форме заказа
     */
    public static function ajaxBuyButtonForm() {



        $arResult = array();

        $base_form_data = $_POST['form']; //Весь запрос

        if (empty($base_form_data)) {
            wp_send_json_error(array('message' => __('request error', 'coderun-oneclickwoo')));
        }

        $help = Help::getInstance();

        $options = $help->get_options();

        $product_id = intval($help->get_value_field($base_form_data, 'idtovar'));

        $product_link = get_the_permalink($product_id);

        //

        /**
         * Поля формы и параметры метода
         */
        $field = array(
            'user_name' => $help->get_value_field($base_form_data, 'txtname'),
            'user_phone' => $help->get_value_field($base_form_data, 'txtphone'),
            'user_email' => sanitize_email($help->get_value_field($base_form_data, 'txtemail')),
            'user_cooment' => $help->get_value_field($base_form_data, 'message'),
            'product_id' => $product_id,
            'product_name' => $help->get_value_field($base_form_data, 'nametovar'),
            'product_price' => $help->get_value_field($base_form_data, 'pricetovar'),
            'product_link_admin' => '<a href="' . $product_link . '" target="_blank"><span class="glyphicon glyphicon-share"></span></a>',
            'product_link' => '<a href="' . $product_link . '" target="_blank">' . __('Look', 'coderun-oneclickwoo') . '</a>',
            'company_name' => $help->get_value_field($options['buynotification'], 'namemag'),
            'order_admin_comment' => $help->get_value_field($options['buynotification'], 'dopiczakaz'),
            'check_conset_personal_data' => $help->get_value_field($base_form_data, 'conset_personal_data'),
            'forms_field' => $help->get_value_field($base_form_data, 'form'),
            'time' => current_time('mysql'),
            'custom' => $help->get_value_field($base_form_data, 'custom'),
        );

        if (BuyCore::$variation) {
            if ($variation = BuyVariationClass::getVariableProductInfo($field['forms_field'])) {

                $strVariation = '<br>' . $variation;

                $field['product_name'] .= '<br>' . $variation;
            }
            if (($variation_id = BuyVariationClass::get_variation_id($field['forms_field'])) > 0) {
                $product_id = $variation_id;
            }
        }



        try {

            if ($product_id < 1) {
                wp_send_json_error(array('message' => __('An error has occurred! Order not formed', 'coderun-oneclickwoo')));
            }

            self::checkRequireField($field);

            self::checkLimitSendForm($product_id);
        } catch (Exception $ex) {
            wp_send_json_error(array('message' => $ex->getMessage()));
        }



        $smslog = ''; //Лог смс
        //В таблицу Woo
        if (isset($options['buyoptions']['add_tableorder_woo']) and $field['custom'] == 0) {

            $woo_order = \Coderun\BuyOneClick\Order::getInstance();

            $woo_order->set_order(array(
                'first_name' => $field['user_name'],
                'last_name' => '',
                'company' => '',
                'email' => $field['user_email'],
                'phone' => $field['user_phone'],
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => '',
                'order_status' => 'processing', //Статус заказа который будет установлен
                'message_notes_order' => __('Quick order form', 'coderun-oneclickwoo'), //Сообщение в заказе
                'qty' => 1,
                'product_id' => $product_id, //ИД товара Woo
            ));
        }
        //---таблица Woo

        if (isset($options['buyoptions']['success_action'])) { // опции "действия после нажатия кнопки в форме"
            if (!empty($options['buyoptions']['success_action_close'])) {
                $success_time = $options['buyoptions']['success_action_close']; // 2 Закрытие формы через мсек
            }
            if (!empty($options['buyoptions']['success_action_message'])) {
                $success_message = $options['buyoptions']['success_action_message']; // 3 Сообщение после нажатия кнопки в форме
            }
            if (!empty($options['buyoptions']['success_action_redirect'])) {
                $success_redirect = $options['buyoptions']['success_action_redirect']; // 4  Редирект на страницу после нажатия на кнопку в форме
            }
            switch ($options['buyoptions']['success_action']) {
                case 1: $success_action = 'no'; //Ни чего не делать, пользователь сам закроет форму
                    $field['num'] = 1;
                    break;
                case 2: $success_action = $success_time;
                    $field['num'] = 2;
                    break;
                case 3: $success_action = $success_message;
                    $field['num'] = 3;
                    break;
                case 4: $success_action = $success_redirect;
                    $field['num'] = 4;
                    break;
                default: $success_action = 'no';
                    $field['num'] = 2; //Ни чего не делать, пользователь сам закроет форму
            }
        } //конец IF действий после нажатия кнопки в форме
        $message = array(
            'time' => $field['time'],
            'url' => $field['product_link'],
            'price' => $field['product_price'],
            'nametov' => $field['product_name'],
            'namemag' => $field['company_name'],
            'dopinfo' => $field['order_admin_comment'],
            'fon' => $field['user_phone'],
            'fio' => $field['user_name'],
            'form' => $field['forms_field'],
            'dop_pole' => $field['user_cooment'],
            'email' => $field['user_email'],
        );
        if (!empty($field['user_email']) and ! empty(BuyCore::$buynotification['infozakaz_chek'])) {
            BuyFunction::BuyEmailNotification($field['user_email'], $field['company_name'], $message);
        }
        if (!empty(BuyCore::$buynotification['emailbbc'])) {
            BuyFunction::BuyEmailNotification(BuyCore::$buynotification['emailbbc'], $field['company_name'], $message);
        }
        //Отправка СМС клиенту
        if (!empty($options['buysmscoptions']['enable_smsc'])) {
            $smsmessage = array(
                'fon' => $field['user_phone'],
                'fio' => $field['user_name'],
                'txtemail' => $field['user_email'],
                'dopinfo' => $field['order_admin_comment'],
                'price' => $field['product_price'],
                'nametov' => $field['product_name']
            );
            $sms = new BuySMSC();
            $smslog = $sms->send_sms(trim($smsmessage['fon']), BuyFunction::composeSms($options['buysmscoptions']['smshablon'], $smsmessage));
            ///Переписать функцию sms? помнить про static
        }
        //Отправка СМС продавцу
        if (!empty(BuyCore::$buysmscoptions['enable_smsc_saller'])) {
            $smsmessage = array(
                'fon' => $field['user_phone'],
                'fio' => $field['user_name'],
                'txtemail' => $field['user_email'],
                'dopinfo' => $field['order_admin_comment'],
                'price' => $field['product_price'],
                'nametov' => $field['product_name']
            );
            $sms2 = new BuySMSC();
            $smslog = $sms2->send_sms(trim($options['buysmscoptions']['phone_saller']), BuyFunction::composeSms($options['buysmscoptions']['smshablon_saller'], $smsmessage));
        }
        //Журналирование

        $jornal_row = array('time' => $field['time'], 'idtovar' => $product_id, 'txtname' => $field['user_name'], 'txtphone' => $field['user_phone'],
            'txtemail' => $field['user_email'], 'nametovar' => $field['product_name'], 'pricetovar' => $field['product_price'], 'message' => $field['user_cooment'], 'status' => 1, 'linktovar' => $field['product_link_admin'], 'smslog' => $smslog
        );

        array_push($options['buyzakaz'], $jornal_row);

        update_option('buyzakaz', $options['buyzakaz']);

        //Конец журналирования
        ob_end_clean();
        $arResult['message'] = __('The order has been sent', 'coderun-oneclickwoo');
        $arResult['result'] = $options['buyoptions']['success'];
        $arResult['num'] = $field['num'];
        $arResult['action'] = $success_action;

        BuyHookPlugin::buyClickNewrder($arResult, $jornal_row);

        echo wp_send_json_success($arResult);
    }

    /**
     * Функция удаляет элемент заказа из таблицы
     * Данные отправляются из файла admin_order.js
     */
    public function ajaxRemoveOrderId() {
        $id = $_POST['text'];
        $infotovar_old = get_option('buyzakaz');
        unset($infotovar_old[$id]);
        $infotovar_new = $infotovar_old;
        update_option('buyzakaz', $infotovar_new);
        die();
    }

    /**
     * Функция удаляет всю таблицу заказов
     * Данные отправляются из файла admin_order.js
     */
    public function ajaxRemoveOrderAll() {
        $nonce = $_POST['nonce']; // Массив URL и NONCE
        ob_end_clean();
        if (wp_verify_nonce($nonce['nonce'], 'superKey')) {
            update_option('buyzakaz', array());
            wp_die('ok');
        } else {
            wp_die(__('Are you a hacker?', 'coderun-oneclickwoo'));
        }
    }

    /**
     * Функция Изменения статуса заказа
     * Данные отправляются из файла admin_order.js
     */
    public function ajaxStatusOrderId() {
        $text = $_POST['text'];
        $id = $text['id'];
        $infotovar_old = get_option('buyzakaz');
        $infotovar_old[$id]['status'] = $text['status'];
        $infotovar_new = $infotovar_old;
        update_option('buyzakaz', $infotovar_new);

        die();
    }

    public static function add_to_cart() {
        $productid = intval($_POST['productid']);
        $variation_id = intval($_POST['variation_selected']);
        $variation_attr = '';
        $variations = array();
        $quantity = 1;
        if (isset($_POST['variation_attr'])) {
            $variation_attr = $_POST['variation_attr'];
            $arSelectVariation = explode('&', $variation_attr);

            foreach ($arSelectVariation as $values) {
                $params = explode('=', $values);
                if (stripos($params[0], 'attribute_pa') !== false) {
                    $variation_slug = str_replace('attribute_pa_', '', $params[0]);
                    $variation_value = $params[1];
                    $variations[$variation_slug] = $variation_value;
                }
                if (stripos($params[0], 'quantity') !== false) {
                    $quantity = $params[1];
                }
            }
        }

        if (!function_exists('WC')) {
            echo get_home_url();
            die();
        }


        WC()->cart->add_to_cart($productid, $quantity, $variation_id, $variations);

        $url = get_permalink(get_option('woocommerce_checkout_page_id'));

        echo $url;

        die();
    }

    /**
     * Возвращает форму для быстрого заказа
     * Используется для фронта
     */
    public static function ajaxgetViewForm() {

        $product_id = intval($_POST['productid']);

        $variation_id = intval($_POST['variation_selected']);

        if ($variation_id > 0) {
            $product_id = $variation_id;
        }

        $cartinfo = BuyFunction::get_product_param($product_id);

        $cartinfo['custom'] = 0;

        echo BuyFunction::viewBuyForm($cartinfo);
        
        die();
    }

    public static function ajaxgetViewFormCustom() {
        $url = $_POST['urlpost'];
        $productid = $_POST['productid'];
        $name = $_POST['name'];
        $count = $_POST['count'];
        $price = $_POST['price'];
        $arProduct = array(
            'article' => $productid,
            'name' => $name,
            'imageurl' => '',
            'amount' => $price,
            'quantity' => $count,
            'custom' => 1,
        );
        //ob_end_clean();
        echo BuyFunction::viewBuyForm($arProduct);
        die();
    }

}
