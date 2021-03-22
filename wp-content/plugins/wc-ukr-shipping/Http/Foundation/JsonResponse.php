<?php

namespace kirillbdev\WCUkrShipping\Http\Foundation;

use kirillbdev\WCUkrShipping\Contracts\ResponseInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

class JsonResponse implements ResponseInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * JsonResponse constructor.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function send()
    {
        wp_send_json($this->data);
    }
}