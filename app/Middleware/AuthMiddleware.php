<?php
// app/Middleware/AuthMiddleware.php
namespace App\Middleware;

use Exception;

class AuthMiddleware
{
    public static function verificar()
    {
        // Change: Check for the token in the $_COOKIE superglobal.
        // It's no longer expected in the Authorization header.
        if (!isset($_COOKIE['authToken'])) {
            throw new Exception('Token não fornecido');
        }

        $token = $_COOKIE['authToken'];

        try {
            // ✅ Usa o JWT para validar sessão e token
            $resultado = \App\Utils\JWT::verifySession($token);
            return $resultado['payload']; // retorna os dados do usuário (userId, nivel, etc)
        } catch (Exception $e) {
            // It's a good practice to also clear the cookie if the token is invalid.
            // This prevents the user from being stuck in a loop.
            setcookie('authToken', '', time() - 3600, '/');
            throw new Exception('Autenticação falhou: ' . $e->getMessage());
        }
    }

    public static function verificarEInjetar(&$router)
    {
        try {
            return self::verificar();
        } catch (Exception $e) {
            // Em caso de falha, redireciona para a página inicial
            header('Location: /projetos/dashboard/');
            exit;
        }
    }
}