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
    public function mostraCadastroUsuario()
    {
        $data = [
            'title' => 'Cadastro de usuário',
            'semLayout' => true
        ]; 
        view('recursos/usuarios/cadastro-usuario.php', $data);
    }

public function paginaInicial()
{
    // Verificar se o usuário já está autenticado
    try {
        $usuario = \App\Middleware\AuthMiddleware::verificar();
        // Se estiver autenticado, redirecionar diretamente para o dashboard
        $this->redirecionarParaDashboard($usuario['nivel']);
        return;
    } catch (Exception $e) {
        // Usuário não autenticado, mostrar página de login
        $data = [
            'title' => 'Login',
            'semLayout' => true
        ];    

        view('recursos/usuarios/login', $data);
    }
}

private function redirecionarParaDashboard($nivel)
{
    $basePath = '/projetos/dashboard';
    $rotas = [
        'admin' => $basePath . '/admin',
        'assinante' => $basePath . '/assinante',
        'vendedor' => $basePath . '/vendedor',
        'cliente' => $basePath . '/cliente'
    ];
    
    $redirectUrl = $rotas[$nivel] ?? $rotas['cliente'];
    header("Location: $redirectUrl");
    exit;
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
        $data = [
            'title' => 'Logs do Servidor',
            'semLayout' => true
        ];        
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();

            // Retorna apenas o conteúdo, sem layout
            view('admin/server-logs', $data);

        } catch (Exception $e) {
            http_response_code(401);
            echo "<p>Acesso negado</p>";
            exit;
        }
    }    

    public function dbMonitor()
    {
        $data = [
            'title' => 'Dado do DB',
            'semLayout' => true
        ];        
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            view('admin/db-monitor', $data);
        } catch (Exception $e) {
            http_response_code(401);
            echo "Acesso negado";
            exit;
        }
    }

    public function frontend()
    {
        $data = [
            'title' => 'Playground de Integração',
            'semLayout' => true
        ];         
        try {

            $usuario = \App\Middleware\AuthMiddleware::verificar();
            view('admin/frontend', $data);

        } catch (Exception $e) {
            http_response_code(401);
            echo "Acesso negado";
            exit;
        }
    }

    public function cadastroUsuario()
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
                //'expires' => time() + (JWT_EXPIRE ?? 3600 * 24 * 7),
                'expires' => time() + (2 * 60), // 2 minutos
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

}