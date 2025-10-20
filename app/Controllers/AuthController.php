<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Utils\JWT;

class AuthController
{
    public function login()
    {
        // Garantir que a resposta seja JSON
        header('Content-Type: application/json; charset=utf-8');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            echo json_encode(['success' => false, 'message' => 'E-mail e senha são obrigatórios']);
            exit;
        }
        
        // Usar o método login do model Usuario que verifica senha
        $resultado = Usuario::login($email, $senha);
        
        if ($resultado['success']) {
            // Criar uma nova sessão para o usuário autenticado
            $sessionData = \App\Utils\JWT::createSession(
                $resultado['usuario']['id'],
                $resultado['usuario'],
                2 // 2 minutos
            );
            
            if ($sessionData) {
                // Definir o cookie com a nova sessão
                setcookie('authToken', $sessionData['token'], [
                    'expires' => time() + (2 * 60), // 2 minutos
                    'path' => '/projetos/dashboard/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                
                echo json_encode([
                    'success' => true,
                    'token' => $sessionData['token'],
                    'usuario' => $resultado['usuario'],
                    'redirect' => $this->getRedirectPage($resultado['usuario']['nivel'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar sessão']);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => $resultado['message']
            ]);
        }
        exit;
    }
    
    private function getRedirectPage($nivel)
    {
        $basePath = '/projetos/dashboard';

        switch ($nivel) {
            case 'admin':
                return $basePath . '/admin';
            case 'assinante':
                return $basePath . '/assinante';
            case 'vendedor':
                return $basePath . '/vendedor';
            case 'cliente':
            default:
                return $basePath . '/cliente';
        }
    }
    
    public function logout()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;
        
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            \App\Models\Sessao::desativarPorToken($token);
        }
        
        // Limpar o cookie de autenticação
        setcookie('authToken', '', time() - 3600, '/projetos/dashboard/');
        
        // Retornar resposta de sucesso
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
        exit;
    }
}