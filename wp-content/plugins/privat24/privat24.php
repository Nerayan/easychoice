<?php
/*
Plugin Name: WooCommerce Privat24 Gateway
Plugin URI: https://woocommerce.com/
Description: Privat24 gateway for WooCommerce
Version: 1.0
Author: Dmitry Kolyadenko
Author URI: https://webmakers.com.ua
*/

if ( ! defined( 'wm_privat24_domain' ) )
    define( 'wm_privat24_domain', 'wm-privat24' );

if( !load_plugin_textdomain( wm_privat24_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ) )
    load_plugin_textdomain( wm_privat24_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

add_action('plugins_loaded', 'Privat24WoocommerceGateway', 0 );

function Privat24WoocommerceGateway() {

add_action( 'init', 'wm_privat24_check_complete' );

if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_Privat24 extends WC_Payment_Gateway {

	var $notify_url;

    // Constructor for the gateway
	public function __construct() {
		global $woocommerce;

        $this->id           = 'privat24';
        $this->icon         = '/wp-content/plugins/privat24/logo.png';
        $this->has_fields   = false;
        $this->method_title = __( 'Приват24', wm_privat24_domain );
        $this->liveurl 		= 'https://api.privatbank.ua/p24api/ishop';
        $this->interactionurl	= add_query_arg( array('wc-api' => 'privat24_process_payment'), get_bloginfo('url') );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title 			= $this->get_option( 'title' );
		$this->description 		= $this->get_option( 'description' );
		$this->pryvatid		    = $this->get_option( 'pryvatid' );
        $this->pryvatpass		= $this->get_option( 'pryvatpass' );
		$this->redirecturl	    = $this->get_option( 'redirecturl' );

		// Actions
        //add_action( 'init', array($this, 'check_complete') );
        add_action( 'woocommerce_receipt_privat24', array($this, 'receipt_page'));
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

    }

	// Admin Panel Options
	// - Options for bits like 'title' and availability on a country-by-country basis
	public function admin_options() {

		?>
		<h3><?php _e( 'Privat24', 'woocommerce' ); ?></h3>
		<p><?php _e( 'Privat24 направляет пользователя на https://www.privat24.ua/ для указания платежной информации.', wm_privat24_domain ); ?></p>

        <?php if ( $this->is_valid_for_use() ) { ?>

			<table class="form-table">
			<?php
    			// Generate the HTML For the settings form.
    			$this->generate_settings_html();
			?>
			</table><!--/.form-table-->

        <?php } else { ?>
            <p><?php _e('Извините, этот платежный шлюз не поддерживает валюту вашего онлайн магазина.', wm_privat24_domain); ?></p>
        <?php } ?>

		<?php
	}

    public function is_valid_for_use() {

            if (!in_array(get_option('woocommerce_currency'), array('USD', 'EUR', 'UAH'))) {
                return false;
            }

            return true;
    }

    // Initialise Gateway Settings Form Fields
    function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
							'title' => __( 'Enable/Disable', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Включить Privat24', wm_privat24_domain ),
							'default' => 'yes'
						),
			'title' => array(
							'title' => __( 'Title', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
							'default' => __( 'Приват24', wm_privat24_domain ),
							'desc_tip'      => true,
						),
			'description' => array(
							'title' => __( 'Description', 'woocommerce' ),
							'type' => 'textarea',
							'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
							'default' => __( 'Оплатить через электронную платежную систему Приват24 (только для клиентов ПриватБанка)', wm_privat24_domain )
						),
			'pryvatid' => array(
							'title' => __( 'Privat24 мерчант ID', wm_privat24_domain ),
							'type' 			=> 'text',
							'description' => __( 'Пожалуйста, укажите ваш Privat24 merchant ID.', wm_privat24_domain ),
							'default' => '',
							'desc_tip'      => true,
							'placeholder'	=> ''
						),
			'pryvatpass' => array(
							'title' => __( 'Privat24 пароль мерчанта', wm_privat24_domain ),
							'type' 			=> 'text',
							'description' => __( 'Пожалуйста, укажите ваш Privat24 пароль мерчанта.', wm_privat24_domain ),
							'default' => '',
							'desc_tip'      => true,
							'placeholder'	=> ''
						),
			'redirecturl' => array(
							'title' => __( 'Url на который вернется покупатель', wm_privat24_domain ),
							'type' 			=> 'text',
							'description' => __( 'Пожалуйста, укажите URL на который нужно возвращать покупателю с сайта Privat24.', wm_privat24_domain ),
							'default' => '',
							'desc_tip'      => true,
							'placeholder'	=> ''
						)
			);

    }

		// Generate the pryvat button link
		public function generate_pryvat_form( $order_id ) {
            global $woocommerce;

			$order = new WC_Order( $order_id );

		    // Remove cart
		    $woocommerce->cart->empty_cart();

			$pryvat_adr         = $this->liveurl;
            $redirect_url       = $this->redirecturl;
            $interaction_url    = $this->interactionurl;
            $sCurrency          = get_option('woocommerce_currency');

			return '<form action="'.$pryvat_adr.'" method="post" id="pryvat_payment_form">
					<input type="hidden" name="amt" value="'.$order->order_total.'" />
					<input type="hidden" name="ccy" value="'.$sCurrency.'" />
					<input type="hidden" name="merchant" value="'.$this->pryvatid.'" />
					<input type="hidden" name="order" value="'.$order_id.'" />
					<input type="hidden" name="details" value="'.sprintf(__('Оплата за заказ № %s', wm_privat24_domain), $order_id).'" />
					<input type="hidden" name="ext_details" value="" />
					<input type="hidden" name="pay_way" value="privat24" />
					<input type="hidden" name="return_url" value="'.$redirect_url.'" />
					<input type="hidden" name="server_url" value="'.$interaction_url.'" />
					<input type="submit" class="button" id="submit_pryvat_payment_form" value="'.__('Pay for order', 'woocommerce').'" /> <a class="button cancel" href="'.esc_url($order->get_cancel_order_url()).'">'.__('Cancel order &amp; restore cart', 'woocommerce').'</a>
				</form>';

		}

		// Process the payment and return the result
		function process_payment( $order_id ) {

			$order = wc_get_order( $order_id );

		    // Remove cart
		    //$woocommerce->cart->empty_cart();

			return array(
				'result' 	=> 'success',
                'redirect'	=> $order->get_checkout_payment_url( true )
			);

		}

		// receipt_page
		function receipt_page( $order ) {

			echo '<p>' . __( 'Спасибо! Ваш заказ ожидает оплаты. Нажмите на кнопку "Оплатить заказ" для перенаправления на страницу оплаты Приват24.', wm_privat24_domain ) . '</p>';

			echo $this->generate_pryvat_form( $order );

		}

    }

		//complete order
		function wm_privat24_check_complete() {
			if ( isset($_POST['payment']) ) {

                $WC_Gateway_Privat24 = new WC_Gateway_Privat24;

			    $sMerchantId         = $WC_Gateway_Privat24->pryvatid;
			    $sMerchantPass       = $WC_Gateway_Privat24->pryvatpass;

				//create array from POST data
                $aPayment = $_POST['payment'];
				$str_arr = explode('&', $_POST['payment']);
				$i = 0;
				foreach ( $str_arr as $value ) {
					$data = explode('=', $value);
					$post_arr[$i]['key'] = $data[0];
					$post_arr[$i]['value'] = $data[1];
						if ( $post_arr[$i]['key'] == 'state' ) { $sStatus = $post_arr[$i]['value']; }
						if ( $post_arr[$i]['key'] == 'order' ) { $sOrderId = $post_arr[$i]['value']; }
					$i++;
				}

                $sSignature     = $_POST['signature'];
                $sMySignature   = sha1(md5($aPayment.$sMerchantPass));

				if ( $sStatus == 'ok' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('processing', __( 'Оплачено через Privat24', wm_privat24_domain ));
					}
				} elseif ( $sStatus == 'fail' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
						$order->update_status('failed', __( 'Неудачная оплата!', wm_privat24_domain ));
                        wc_add_notice( __( 'Неудачная оплата!', wm_privat24_domain ), 'error' );
					}
				} elseif ( $sStatus == 'test' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('completed', __( 'Тест: оплачено Privat24!', wm_privat24_domain ));
					}
                }

			}
		}
		
    // Add the Gateway to WooCommerce
    function woocommerce_add_gateway_privat24_gateway($methods) {
        $methods[] = 'WC_Gateway_Privat24';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_privat24_gateway' );

}

class privat24_process_payment {

		// check response from payment service
	    public function __construct( ) {

                $WC_Gateway_Privat24 = new WC_Gateway_Privat24;

			    $sMerchantId         = $WC_Gateway_Privat24->pryvatid;
			    $sMerchantPass       = $WC_Gateway_Privat24->pryvatpass;

				//create array from POST data
                $aPayment = $_POST['payment'];
				$str_arr = explode('&', $_POST['payment']);
				$i = 0;
				foreach ( $str_arr as $value ) {
					$data = explode('=', $value);
					$post_arr[$i]['key'] = $data[0];
					$post_arr[$i]['value'] = $data[1];
						if ( $post_arr[$i]['key'] == 'state' ) { $sStatus = $post_arr[$i]['value']; }
						if ( $post_arr[$i]['key'] == 'order' ) { $sOrderId = $post_arr[$i]['value']; }
					$i++;
				}

                $sSignature     = $_POST['signature'];
                $sMySignature   = sha1(md5($aPayment.$sMerchantPass));

				if ( $sStatus == 'ok' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('processing', __( 'Оплачено через Privat24', wm_privat24_domain ));
					}
				} elseif ( $sStatus == 'fail' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
						$order->update_status('failed', __( 'Неудачная оплата!', wm_privat24_domain ));
                        wc_add_notice( __( 'Неудачная оплата!', wm_privat24_domain ), 'error' );
					}
				} elseif ( $sStatus == 'test' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('completed', __( 'Тест: оплачено Privat24!', wm_privat24_domain ));
					}
                }
        }

}