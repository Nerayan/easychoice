<?php

namespace morkva\JustinShip\classes;

class JustinApiNew
{
    /*
    constructor with default params which are test credentials
    */
    public function __construct($login = "Morkva", $password = "gFpdGw&oLs", $url='https://api.justin.ua/justin_pms/hs/v2/runRequest')
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;

        if ( ! empty( get_option( 'morkvajustin_login' ) )  && ! empty( get_option( 'morkvajustin_password' ) ) ) {
            $this->url = 'https://api.justin.ua/justin_pms/hs/v2/runRequest';
            $this->login = get_option( 'morkvajustin_login' );
            $this->password = get_option( 'morkvajustin_password' );
        }
        
        $KyivTimeZone = new \DateTimeZone('Europe/Kiev');
        $current_time = wp_date( "Y-m-d", time(), $KyivTimeZone );
        $this->sign = (string)sha1( $this->password.':'. $current_time );

        $this->apikey = get_option('morkvajustin_apikey');
    }

    /*
    function to get justin areas
    */
    public function getAreas()
    {
        $jsongetoblast = array(
      "keyAccount" => $this->login,
      "sign" => $this->sign,
      "request"  => "getData",
      "type"  => "catalog",
      "name"  => "cat_Region",
      "language"  => "UA",
      "TOP"=> 40,
    );

        $remote_post = wp_remote_post(
            $this->url,
            array(
          'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
          'body'        => json_encode($jsongetoblast),
          'method'      => 'POST',
          'data_format' => 'body',
      )
        );
        return $remote_post['body'];
    }

    /*
    function to get justin cities, if filterstr is empty, returns all justin cities
    */
    public function getCity($filterstr=null)
    {
        $filterarray = array(
        "name" => "descr",
        "comparison" => "equal",
        "leftValue" => $filterstr
      );
        $jsongetcity = array(
        "keyAccount" => $this->login,
        "sign" => $this->sign,
        "request" => "getData",
        "type" => "catalog",
        "name" => "cat_Cities",
        "language" => "UA",
        "TOP" => 1000,
      );
        if (null != $filterstr) {
            $jsongetcity["filter"] = array($filterarray);
        }
        $remote_post = wp_remote_post(
            $this->url,
            array(
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode($jsongetcity),
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => 17,
        )
        );
        if ( is_object( $remote_post )) {
            $getCityErr = '<b>Сповіщення від API Justin: </b><i> ' . json_encode( $remote_post->errors ) . '</i>';
            return wc_add_notice( $getCityErr, 'error' );
        }
        return $remote_post['body'];
    }

    public function getCityStreets($cityId)
    {
        $filterarray = array(
          "name" => "objectOwner",
          "comparison" => "equal",
          "leftValue" => $cityId
        );
        $jsongetcitystreet = array(
          "keyAccount" => $this->login,
          "sign" => $this->sign,
          "request" => "getData",
          "type" => "catalog",
          "name" => "cat_cityStreets",
          "language" => "UA",
          "TOP" => 1000,
          "filter"=>array($filterarray)
        );
        $remote_post = wp_remote_post(
            $this->url,
            array(
                'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                'body'        => json_encode($jsongetcitystreet),
                'method'      => 'POST',
                'data_format' => 'body',
            )
        );
        return $remote_post['body'];
    }

    public function getWarehouses($regionId = null)
    {
        $filterarray = array(
          "name" => "region",
          "comparison" => "equal",
          "leftValue" => $regionId
        );
        $jsongetwarehouses = array(
          "keyAccount" => $this->login,
          "sign" => $this->sign,
          "request" => "getData",
          "type" => "request",
          "name" => "req_DepartmentsLang",
          "TOP" => 1000,
          "params" =>array('language' => "UA")
        );
        if (null != $regionId) {
            $jsongetwarehouses["filter"] = array($filterarray);
        }

        $remote_post = wp_remote_post(
            $this->url,
            array(
                'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                'body'        => json_encode($jsongetwarehouses),
                'method'      => 'POST',
                'data_format' => 'body',
                'timeout'     => 19,
            )
        );
        return $remote_post['body'];
    }
    /*
     createTtn
    */

    public function createTtn($apikey, $data)
    {
        $url = "https://api.justin.ua/justin_pms/hs/api/v1/documents/orders";

        if ($apikey == 'f2290c07-c028-11e9-80d2-525400fb7782') {
            $url = "http://api.justin.ua/justin_pms_test/hs/api/v1/documents/orders";
        }

        $apikey = $this->apikey;

        $remote_post = wp_remote_post(
            $url,
            array(
              'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
              'body'        => json_encode(
                  array(
                  "api_key" =>  $this->apikey,
                  "data"    =>  $data
                )
              ),
              'method'      => 'POST',
              'data_format' => 'body',
          )
        );
        return $remote_post['body'];
    }
}
