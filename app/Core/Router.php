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
                
                // Verificar se o método existe
                if (method_exists($controllerInstance, $method)) {
                    // Obter informações do método para determinar se ele aceita parâmetros
                    $reflection = new \ReflectionMethod($controllerInstance, $method);
                    $parameters = $reflection->getParameters();
                    
                    // Se o método não aceitar parâmetros ou todos forem opcionais, chamar sem parâmetros
                    if (count($parameters) === 0 || $parameters[0]->isDefaultValueAvailable()) {
                        $result = $controllerInstance->$method();
                    } else {
                        // Passar parâmetros mockados para os métodos que os esperam
                        $result = $controllerInstance->$method(null, null, null);
                    }
                    
                    // Se o método retornou um resultado (como um response), tentar enviar
                    if ($result !== null && method_exists($result, 'send')) {
                        $result->send();
                    }
                } else {
                    http_response_code(404);
                    echo "Método não encontrado.";
                }
            } elseif (is_callable($action)) {
                // Se for uma função anônima
                $action();
            }
        } else {
            http_response_code(404);
            echo "Página não encontrada.";
        }
    }
}