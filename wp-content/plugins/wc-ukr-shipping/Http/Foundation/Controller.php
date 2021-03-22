<?php

namespace kirillbdev\WCUkrShipping\Http\Foundation;

use kirillbdev\WCUkrShipping\Contracts\ResponseInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

abstract class Controller
{
    /**
     * @param array $data
     *
     * @return ResponseInterface
     */
    public function json($data)
    {
        return new JsonResponse($data);
    }
}