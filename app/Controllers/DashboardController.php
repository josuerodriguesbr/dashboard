<?php
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
        return $this->view('server-logs');
    }    

    public function dbMonitor()
    {
        return $this->view('db-monitor');
    }

    public function frontend()
    {
        return $this->view('frontend');
    }

    protected function view($name, $data = [])
    {
        $file = ROOT . 'app/Views/' . $name . '.php';
        if (file_exists($file)) {
            // ✅ Define o tipo de conteúdo
            header('Content-Type: text/html; charset=utf-8');
            extract($data);
            include $file;
            exit; // Finaliza a execução
        }

        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "View X não encontrada: $file";
        exit;
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