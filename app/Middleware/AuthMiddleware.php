<?php
// app/Middleware/AuthMiddleware.php
namespace App\Middleware;

use App\Models\Usuario;
use App\Utils\UserContext;

class AuthMiddleware
{
    public static function verificar()
    {
        error_log("Iniciando verificação de autenticação");
        
        // Verificar se existe um token de autenticação
        $token = $_COOKIE['authToken'] ?? '';
        error_log("Token do cookie: " . ($token ? 'presente' : 'ausente'));
        
        if (empty($token)) {
            // Tentar obter o token do cabeçalho Authorization
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? '';
            error_log("Authorization header: " . ($authHeader ? 'presente' : 'ausente'));
            
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
                error_log("Token do header: presente");
            }
        }
        
        if (empty($token)) {
            error_log("Token de autenticação não encontrado");
            throw new \Exception('Token de autenticação não encontrado.');
        }
        
        // Verificar o token
        error_log("Verificando token");
        $resultado = Usuario::verificarToken($token);
        error_log("Resultado da verificação: " . print_r($resultado, true));
        
        if (!$resultado['success']) {
            error_log("Token inválido ou expirado: " . $resultado['message']);
            throw new \Exception('Token inválido ou expirado.');
        }
        
        // Definir o contexto do usuário
        UserContext::setUsuario($resultado['usuario']);
        if (isset($resultado['usuario']['nivel'])) {
            UserContext::setNivelAtivo($resultado['usuario']['nivel']);
        }
        
        error_log("Autenticação bem sucedida: " . print_r($resultado['usuario'], true));
        return $resultado['usuario'];
    }
    
    public static function verificarEConfigurar()
    {
        try {
            $usuario = self::verificar();
            return $usuario;
        } catch (\Exception $e) {
            error_log("Falha na autenticação: " . $e->getMessage());
            // Redirecionar para a página de login
            header('Location: /projetos/dashboard/');
            exit;
        }
    }
}