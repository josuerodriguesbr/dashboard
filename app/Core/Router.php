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
        $path = '/' . ltrim($path, '/');

        // 1. Tentativa de match exato (performance)
        if (isset($this->routes[$method][$path])) {
            $this->dispatch($this->routes[$method][$path]);
            return;
        }

        // 2. Tentativa de match com parâmetros (Regex)
        foreach ($this->routes[$method] as $route => $action) {
            // Converte {param} para regex (ex: {id} -> ([^/]+))
            if (strpos($route, '{') !== false) {
                // Escapa a rota para regex, mas mantendo os grupos {param}
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
                $pattern = "#^" . $pattern . "$#";

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // Remove o match completo, mantém só os grupos capturados
                    $this->dispatch($action, $matches);
                    return;
                }
            }
        }

        http_response_code(404);
        http_response_code(404);
        echo "Página não encontrada. Rota: " . htmlspecialchars($path) . " Metodo: " . $method;
    }

    private function dispatch($action, $params = [])
    {
        if (is_array($action)) {
            [$controller, $method] = $action;
            $controllerInstance = new $controller();
            
            if (method_exists($controllerInstance, $method)) {
                // Passa os parâmetros capturados para o método
                call_user_func_array([$controllerInstance, $method], $params);
            } else {
                http_response_code(404);
                echo "Método não encontrado.";
            }
        } elseif (is_callable($action)) {
            call_user_func_array($action, $params);
        }
    }
}