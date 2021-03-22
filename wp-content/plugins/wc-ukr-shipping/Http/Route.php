<?php

namespace kirillbdev\WCUkrShipping\Http;

use kirillbdev\WCUkrShipping\Contracts\ResponseInterface;
use kirillbdev\WCUkrShipping\Http\Middleware\VerifyCsrfToken;

if ( ! defined('ABSPATH')) {
    exit;
}

class Route
{
    private $action;
    private $controller;
    private $method;
    private $public = false;
    private $middleware = [
        VerifyCsrfToken::class
    ];

    public function __construct($action, $controller, $method, $options = [])
    {
        $this->action = $action;
        $this->controller = $controller;
        $this->method = $method;

        if (isset($options['public'])) {
            $this->public = true;
        }

        if ( ! empty($options['middleware'])) {
            $this->middleware = array_merge($this->middleware, $options['middleware']);
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @param array $data
     */
    public function dispatch($data)
    {
        $request = new Request($data);

        foreach ($this->middleware as $middleware) {
            $guard = new $middleware();

            if (method_exists($guard, 'handle')) {
                $guard->handle($request);
            }
        }

        $controller = new $this->controller();

        // todo: throws exception if $response not implement ResponseInterface
        /* @var ResponseInterface $response */
        $response = call_user_func([ $controller, $this->method ], $request);
        $response->send();
    }
}