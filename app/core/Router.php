<?php

class Router
{
    private $routes = [];

    public function get($url, $action)
    {
        $this->routes['GET'][$url] = $action;
    }

    public function post($url, $action)
    {
        $this->routes['POST'][$url] = $action;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = '/leadflow';

        if (strpos($url, $basePath) === 0) {
            $url = substr($url, strlen($basePath));
        }

        $url = '/' . trim($url, '/');

        if ($url === '/') {
            $url = '/';
        }

        if (isset($this->routes[$method][$url])) {
            [$controller, $action] = $this->routes[$method][$url];

            require_once __DIR__ . '/../controllers/' . $controller . '.php';

            $instance = new $controller();

            $instance->$action();
            return;
        }

        http_response_code(404);
        echo "404 - Page Not Found";
    }
}
