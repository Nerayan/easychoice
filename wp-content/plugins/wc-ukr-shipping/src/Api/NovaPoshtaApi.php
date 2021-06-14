<?php

namespace kirillbdev\WCUkrShipping\Api;

use kirillbdev\WCUkrShipping\Contracts\ApiResponseInterface;
use kirillbdev\WCUkrShipping\Contracts\HttpClient;
use kirillbdev\WCUkrShipping\Exceptions\ApiServiceException;
use kirillbdev\WCUkrShipping\Http\Response\CollectionResponse;
use kirillbdev\WCUkrShipping\Http\Response\ErrorResponse;
use kirillbdev\WCUkrShipping\Http\Response\ExceptionResponse;

if ( ! defined('ABSPATH')) {
    exit;
}

class NovaPoshtaApi
{
    /**
     * @var string
     */
    private $apiUrl = 'https://api.novaposhta.ua/v2.0/json/';

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpClient $client
     */
    public function __construct($client)
    {
        $this->client = $client;
        $this->apiKey = get_option('wc_ukr_shipping_np_api_key', '');
    }

    /**
     * @return ApiResponseInterface
     */
    public function getAreas()
    {
        $data['modelName'] = 'Address';
        $data['calledMethod'] = 'getAreas';
        $data['apiKey'] = $this->apiKey;

        return $this->sendLoadRequest($data);
    }

    /**
     * @return ApiResponseInterface
     */
    public function getCities($page)
    {
        $data['modelName'] = 'Address';
        $data['calledMethod'] = 'getCities';
        $data['apiKey'] = $this->apiKey;
        $data['methodProperties'] = [
            'Page' => $page,
            'Limit' => 300
        ];

        return $this->sendLoadRequest($data);
    }

    /**
     * @return ApiResponseInterface
     */
    public function getWarehouses($page)
    {
        $data['modelName'] = 'AddressGeneral';
        $data['calledMethod'] = 'getWarehouses';
        $data['apiKey'] = $this->apiKey;
        $data['methodProperties'] = [
            'Page' => $page,
            'Limit' => 300
        ];

        return $this->sendLoadRequest($data);
    }

    /**
     * @param array $data
     *
     * @return ApiResponseInterface
     */
    private function sendLoadRequest($data)
    {
        try {
            $response = $this->sendRequest($data);

            if ($response['success']) {
                return new CollectionResponse($response['data']);
            }

            return new ErrorResponse($response);
        }
        catch (ApiServiceException $e) {
            return new ExceptionResponse($e);
        }
    }

    /**
     * @param array $data
     * @return mixed
     *
     * @throws ApiServiceException
     */
    private function sendRequest($data)
    {
        $result = $this->client->post(
            $this->apiUrl,
            json_encode($data),
            [ 'Content-Type' => 'application/json' ]
        );

        return json_decode($result, true);
    }
}