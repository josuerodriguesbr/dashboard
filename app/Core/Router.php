<?php
// /app/Core/Router.php
namespace App\Core;

class Router
{
    private $routes = [];

    public function get($path, $action) {
        $this->routes['GET'][$path] = $action;
    }

    public function post($path, $action) {
        $this->routes['POST'][$path] = $action;
    }

    public function resolve($path, $method) {
        // $path já vem limpo do index.php
        $path = '/' . ltrim($path, '/');

        if (isset($this->routes[$method][$path])) {
            $action = $this->routes[$method][$path];
            if (is_array($action)) {
                [$controller, $method] = $action;
                $controllerInstance = new $controller();
                // Opcional: verificar se o método existe
                if (method_exists($controllerInstance, $method)) {
                    $controllerInstance->$method();
                } else {
                    http_response_code(404);
                    echo "Método não encontrado.";
                }
            }
        } else {
            http_response_code(404);
            echo "Página não encontrada.";
        }
    }
}