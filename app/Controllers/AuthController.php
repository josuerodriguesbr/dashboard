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
        
        error_log("Iniciando processo de login");
        
        // Verificar se JWT_SECRET está definido
        if (!defined('JWT_SECRET')) {
            error_log("JWT_SECRET não está definido");
            echo json_encode(['success' => false, 'message' => 'Configuração incompleta: JWT_SECRET não definido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("Dados recebidos: " . print_r($input, true));
        
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            echo json_encode(['success' => false, 'message' => 'E-mail e senha são obrigatórios']);
            exit;
        }
        
        // Usar o método login do model Usuario que verifica senha
        error_log("Chamando Usuario::login");
        $resultado = Usuario::login($email, $senha);
        error_log("Resultado do login: " . print_r($resultado, true));
        
        if ($resultado['success']) {
            error_log("Login bem sucedido, criando sessão");
            
            // Verificar se o usuário tem um nível definido
            if (!isset($resultado['usuario']['nivel']) || empty($resultado['usuario']['nivel'])) {
                error_log("Usuário sem nível definido: " . print_r($resultado['usuario'], true));
                echo json_encode(['success' => false, 'message' => 'Usuário sem nível de acesso definido']);
                exit;
            }
            
            // Criar uma nova sessão para o usuário autenticado
            $sessionData = \App\Utils\JWT::createSession(
                $resultado['usuario']['id'],
                $resultado['usuario'],
                2 // 2 minutos
            );
            
            error_log("Dados da sessão: " . print_r($sessionData, true));
            
            if ($sessionData) {
                // Definir o cookie com a nova sessão
                setcookie('authToken', $sessionData['token'], [
                    'expires' => time() + JWT_EXPIRE, // 2 minutos 
                    'path' => '/projetos/dashboard/',
                    'secure' => false,
                    // 'httponly' => true, // Removido para permitir leitura via JS
                    'samesite' => 'Lax'
                ]);

                $redirect = getRotaPorUserNivel($resultado['usuario']['nivel']);
                
                echo json_encode([
                    'success' => true,
                    'token' => $sessionData['token'],
                    'usuario' => $resultado['usuario'],
                    'redirect' => $redirect
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
    
    public function logout()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $headers = apache_request_headers();
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;
            
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
                \App\Models\Sessao::desativarPorToken($token);
            } else {
                // Tentar obter o token do cookie
                if (isset($_COOKIE['authToken'])) {
                    $token = $_COOKIE['authToken'];
                    \App\Models\Sessao::desativarPorToken($token);
                }
            }
        } catch (\Exception $e) {
            // Log do erro mas continua o processo de logout
            error_log("Erro ao desativar sessão: " . $e->getMessage());
        }
        
        // Limpar o cookie de autenticação
        setcookie('authToken', '', time() - 3600, '/projetos/dashboard/');
        
        // Retornar resposta de sucesso
        echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
        exit;
    }
}