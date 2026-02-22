<?php

class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function options($path, $callback) {
        $this->routes['OPTIONS'][$path] = $callback;
    }

    public function resolve() {
        // Handle CORS globally for all requests
        // Handle CORS globally for all requests
        // CORS handled by .htaccess
        // header("Access-Control-Allow-Origin: *");
        // header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        // header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'OPTIONS') {
            exit(0);
        }

        $path = urldecode($_SERVER['REQUEST_URI']);
        // Remove query string
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        // Basic routing logic to match path relative to script
        // for subdirectories
        $script_name = dirname($_SERVER['SCRIPT_NAME']);
        
        // Ensure script_name is also decoded just in case/consistent separator
        // Windows might use backslashes in some envs, but SCRIPT_NAME is usually web path.
        // Let's normalize to ensure we match correctly.
        
        if ($script_name !== '/' && strpos($path, $script_name) === 0) {
            $path = substr($path, strlen($script_name));
        }
        
        if ($path == '') $path = '/';

        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Not Found"]);
            return;
        }

        // Support array callback [Controller::class, 'method']
        if (is_array($callback)) {
            $controller = new $callback[0]();
            $method = $callback[1];
            return call_user_func([$controller, $method]);
        }

        return call_user_func($callback);
    }
}
