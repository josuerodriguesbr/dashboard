<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Utils\JWT;

class AuthController
{
    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $email = $input['email'] ?? '';
        
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'E-mail é obrigatório']);
            exit;
        }
        
        // Verificar se o usuário existe
        $usuario = Usuario::buscarPorEmail($email);
        
        if ($usuario) {
            // Usuário existe, criar sessão
            $userData = [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'nivel' => $usuario['nivel']
            ];
            
            $sessionData = \App\Utils\JWT::createSession($usuario['id'], $userData);
            
            if ($sessionData) {
                echo json_encode([
                    'success' => true,
                    'token' => $sessionData['token'],
                    'usuario' => $userData,
                    'redirect' => $this->getRedirectPage($usuario['nivel'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar sessão']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuário não encontrado. Por favor, cadastre-se primeiro.']);
        }
        exit;
    }
    
    private function getRedirectPage($nivel)
    {
        switch ($nivel) {
            case 'admin':
                return '/admin';
            case 'assinante':
                return '/assinante';
            case 'vendedor':
                return '/vendedor';
            case 'cliente':
            default:
                return '/cliente';
        }
    }
    
    public function logout()
    {
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;
        
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            \App\Models\Sessao::desativarPorToken($token);
        }
        
        echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
        exit;
    }
}