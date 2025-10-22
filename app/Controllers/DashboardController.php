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

public function mostraPerfilUsuario()
{
    $data = [
        'title' => 'Meu Perfil',
        'semLayout' => true
    ]; 
    view('recursos/usuarios/perfil-usuario', $data);
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


public function atualizaUsuario()
{
    try {
        // Verificar se o usuário está autenticado
        $usuarioLogado = \App\Middleware\AuthMiddleware::verificar();
        
        // Obter os dados do corpo da requisição
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar se os dados foram recebidos corretamente
        if (!$input) {
            json_response(['success' => false, 'message' => 'Dados inválidos'], 400);
            return;
        }
        
        // Verificar se o ID do usuário foi fornecido
        $id = $input['id'] ?? null;
        
        // Se não foi fornecido ID, usar o ID do usuário logado
        if (!$id) {
            $id = $usuarioLogado['id'];
        }
        
        // Se o usuário não é admin e está tentando atualizar outro perfil, negar acesso
        if ($usuarioLogado['id'] != $id && $usuarioLogado['nivel'] != 'admin') {
            json_response(['success' => false, 'message' => 'Acesso negado'], 403);
            return;
        }
        
        // Remover campos que não devem ser atualizados via este endpoint
        unset($input['id']); // Remover ID dos dados a serem atualizados
        
        // Atualizar o usuário usando o método do modelo
        $resultado = \App\Models\Usuario::atualizar($id, $input);
        
        if ($resultado) {
            // Se for o próprio usuário atualizando seu perfil, atualizar o token
            if ($usuarioLogado['id'] == $id) {
                // Buscar os dados atualizados do usuário
                $usuarioAtualizado = \App\Models\Usuario::buscarPorId($id);
                
                if ($usuarioAtualizado) {
                    // Remover a senha antes de criar o novo token
                    unset($usuarioAtualizado['senha']);
                    
                    // Regenerar o token JWT com os dados atualizados
                    $novosDadosUsuario = [
                        'id' => $usuarioAtualizado['id'],
                        'nome' => $usuarioAtualizado['nome'],
                        'email' => $usuarioAtualizado['email'],
                        'nivel' => $usuarioAtualizado['nivel']
                    ];
                    
                    $novaSessao = \App\Utils\JWT::createSession($id, $novosDadosUsuario);
                    
                    if ($novaSessao) {
                        // Atualizar o cookie com o novo token
                        setcookie('authToken', $novaSessao['token'], [
                            'expires' => time() + (2 * 60), // 2 minutos
                            'path' => '/projetos/dashboard/',
                            'secure' => false,
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }
                }
            }
            
            // Se a atualização for bem-sucedida, retornar sucesso
            json_response([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso'
            ]);
        } else {
            // Se a atualização falhar
            json_response([
                'success' => false,
                'message' => 'Falha ao atualizar perfil'
            ], 500);
        }
    } catch (\Exception $e) {
        // Registrar erro em log
        error_log("Erro ao atualizar usuário: " . $e->getMessage());
        
        // Retornar mensagem de erro
        json_response([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}  

public function carregaPerfil()
{
    try {
        // Verificar se o usuário está autenticado
        $usuarioLogado = \App\Middleware\AuthMiddleware::verificar();
        
        // Obter o ID do usuário (pode ser passado como parâmetro ou usar o do usuário logado)
        $id = $_GET['id'] ?? $usuarioLogado['id'];
        
        // Se o usuário não é admin e está tentando ver outro perfil, negar acesso
        if ($usuarioLogado['id'] != $id && $usuarioLogado['nivel'] != 'admin') {
            json_response(['success' => false, 'message' => 'Acesso negado'], 403);
            return;
        }
        
        // Buscar os dados do usuário
        $usuario = \App\Models\Usuario::buscarPorId($id);
        
        if ($usuario) {
            // Remover a senha antes de enviar para o frontend
            unset($usuario['senha']);
            
            json_response([
                'success' => true,
                'usuario' => $usuario
            ]);
        } else {
            json_response([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }
    } catch (\Exception $e) {
        error_log("Erro ao carregar perfil: " . $e->getMessage());
        json_response([
            'success' => false,
            'message' => 'Erro interno do servidor'
        ], 500);
    }
}

}