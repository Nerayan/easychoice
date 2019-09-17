<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class Reporter
{
  public function __construct()
  {
    add_action('admin_init', [ $this, 'sendFirstStats' ]);
  }

  public function sendFirstStats()
  {
    $this->generateToken();

    if ( ! $this->validateSendOpportunity() || get_option('wc_ukr_shipping_first_stat_sending')) {
      return;
    }

    try {
      $result = wp_remote_post('https://kirillbdev.pro/api/v1/wc-ukr-shipping/', [
        'headers' => ['Content-Type' => 'application/json'],
        'timeout' => 30,
        'body' => json_encode([
          'method' => 'sendAction',
          'params' => [
            'token' => get_option('wc_ukr_shipping_stats_api_token'),
            'event' => 'initial_send',
            'url' => home_url(),
            'lang' => get_option('wc_ukr_shipping_np_lang'),
            'addressShipping' => get_option('wc_ukr_shipping_address_shipping')
          ]
        ], JSON_UNESCAPED_UNICODE)
      ]);

      $response = json_decode($result['body'], true);

      if ($response['success']) {
        update_option('wc_ukr_shipping_first_stat_sending', 1);
      }
    }
    catch (\Error $e) {

    }
    catch (\Exception $e) {

    }
  }

  public function reportForUninstall()
  {
	  try {
		  wp_remote_post('https://kirillbdev.pro/api/v1/wc-ukr-shipping/', [
			  'headers' => ['Content-Type' => 'application/json'],
			  'timeout' => 30,
			  'body' => json_encode([
          'method' => 'uninstall',
          'params' => [
            'url' => home_url()
          ]
        ], JSON_UNESCAPED_UNICODE)
		  ]);
	  }
	  catch (\Error $e) {

	  }
	  catch (\Exception $e) {

	  }
  }

  private function validateSendOpportunity()
  {
    return get_option('wc_ukr_shipping_stats_api_token') &&
      get_option('wc_ukr_shipping_send_statistic') &&
      get_option('wc_ukr_shipping_np_lang') !== null &&
      get_option('wc_ukr_shipping_address_shipping') !== null;
  }

  private function generateToken()
  {
    if ( ! get_option('wc_ukr_shipping_stats_api_token') && (int)get_option('wc_ukr_shipping_send_statistic')) {
      try {
        $result = wp_remote_post('https://kirillbdev.pro/api/v1/wc-ukr-shipping/', [
          'headers' => ['Content-Type' => 'application/json'],
          'timeout' => 30,
          'body' => json_encode([
            'method' => 'generateToken',
            'params' => [
              'url' => home_url()
            ]
          ], JSON_UNESCAPED_UNICODE)
        ]);

        $response = json_decode($result['body'], true);

        if ($response['success']) {
          update_option('wc_ukr_shipping_stats_api_token', $response['data']['token']);
        }
      }
      catch (\Error $e) {

      }
      catch (\Exception $e) {

      }
    }
  }
}