<?php
// /app/Controllers/DashboardController.php
namespace App\Controllers;

use Exception; // Adicionar esta linha

use App\Models\Log;
use App\Models\Usuario;
use App\Utils\JWT;

class DashboardController
{  
    // Método para mostrar o formulário de cadastro
    public function mostrarFormularioCadastro()
    {
        $this->view('recursos/cadastrar-usuario');
    }

/*    public function paginaInicial()
    {
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();

            switch ($usuario['nivel']) {
                case 'admin':
                    redirect('/admin');
                    break;
                case 'assinante':
                    redirect('/assinante');
                    break;
                case 'vendedor':
                    redirect('/vendedor');
                    break;
                default:
                    redirect('/cliente');
                    break;
            }
        } catch (Exception $e) {
            view('recursos/cadastrar-usuario'); // mostra o formulário
        }
    }
    */

    public function paginaInicial()
    {
        // ✅ Apenas mostra o formulário
        // A decisão de redirecionar fica com o frontend (layout.php)
        view('recursos/cadastrar-usuario');

    }

    public function logs()
    {
        $logs = \App\Models\Log::listar();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit; // Para garantir que nada mais seja executado
    }
    
    public function serverLogs()
    {
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            // Retorna apenas o conteúdo, sem layout
            view('admin/server-logs');
        } catch (Exception $e) {
            http_response_code(401);
            echo "<p>Acesso negado</p>";
            exit;
        }
    }    

    public function dbMonitor()
    {
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            view('admin/db-monitor');
        } catch (Exception $e) {
            http_response_code(401);
            echo "Acesso negado";
            exit;
        }
    }

    public function frontend()
    {
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            view('admin/frontend');
        } catch (Exception $e) {
            http_response_code(401);
            echo "Acesso negado";
            exit;
        }
    }

    public function cadastrarUsuario()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $usuarioId = \App\Models\Usuario::cadastrar($input);

            // Gera sessão com JWT + salva no banco
            $resultadoSessao = \App\Utils\JWT::createSession(
                $usuarioId,
                [
                    'nome'  => $input['nome'],
                    'email' => $input['email'],
                    'nivel' => $input['nivel'] ?? 'cliente'
                ],
                24 * 7 // 7 dias
            );

            if (!$resultadoSessao) {
                throw new Exception("Falha ao criar sessão");
            }

            // DEFINIR O COOKIE AQUI
            setcookie('authToken', $resultadoSessao['token'], [
                'expires' => time() + (JWT_EXPIRE ?? 3600 * 24 * 7),
                'path' => '/projetos/dashboard/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);            

            \App\Models\Log::registrar(
                $usuarioId,
                'Cadastro e login automático',
                "Usuário ID: $usuarioId"
            );

            json_response([
                'success' => true,
                'id' => $usuarioId,
                'token' => $resultadoSessao['token'],
                'user' => [
                    'id' => $usuarioId,
                    'nome' => $input['nome'],
                    'email' => $input['email'],
                    'nivel' => $input['nivel'] ?? 'cliente'
                ]
            ]);

        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    public function logout()
    {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            \App\Models\Sessao::desativarPorToken($token);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
        exit;
    }    

}