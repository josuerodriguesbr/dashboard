<?php
// /app/Controllers/DashboardController.php
namespace App\Controllers;

use Exception; // Adicionar esta linha

use App\Models\Log;
use App\Models\Usuario;
use App\Utils\JWT;

class DashboardController
{  

    public function mostraPerfilUsuario()
    {
        $data = [
            'title' => 'Meu Perfil'
            // Removido semLayout => true para usar o layout normal
        ]; 
        view('recursos/usuarios/perfil-usuario', $data);
    }  

    public function paginaInicial()
    {
        // Verificar se o usuário já está autenticado
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            $this->redirecionaUsuarioPorNivel($usuario['nivel']);
            exit();
        } catch (Exception $e) {
            // Usuário não autenticado, mostrar página de login
            $data = [
                'title' => 'Login',
                'semLayout' => true
            ];    

            view('recursos/usuarios/login', $data);
        }
    }

    private function redirecionaUsuarioPorNivel($nivel)
    {
        $redirectUrl = getRotaPorUserNivel($nivel);
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

    public function atualizaUsuario()
    {
        // Garantir que a resposta seja JSON
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Verificar se o usuário está autenticado
            $usuarioLogado = \App\Middleware\AuthMiddleware::verificar();
            
            // Obter os dados do corpo da requisição
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validar se os dados foram recebidos corretamente
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
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
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            // Remover campos que não devem ser atualizados via este endpoint
            unset($input['id']); // Remover ID dos dados a serem atualizados
            
            // Atualizar o usuário usando o método do modelo
            $resultado = \App\Models\Usuario::atualizar($id, $input);
            
            if ($resultado) {
                $token = null;
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
                            'nivel' => $usuarioAtualizado['papel_nivel'] ?? 'cliente'
                        ];
                        
                        $novaSessao = \App\Utils\JWT::createSession($id, $novosDadosUsuario);
                        
                        if ($novaSessao) {
                            $token = $novaSessao['token'];
                            // Atualizar o cookie com o novo token
                            setcookie('authToken', $novaSessao['token'], [
                                'expires' => time() + (8 * 60), // 8 minutos
                                'path' => '/projetos/dashboard/',
                                'secure' => false,
                                // 'httponly' => true, // Removido para permitir leitura via JS
                                'samesite' => 'Lax'
                            ]);
                        }
                    }
                }
                
                // Se a atualização for bem-sucedida, retornar sucesso
                echo json_encode([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso',
                    'token' => $token
                ]);
            } else {
                // Se a atualização falhar
                echo json_encode([
                    'success' => false,
                    'message' => 'Falha ao atualizar perfil'
                ]);
            }
        } catch (\Exception $e) {
            // Registrar erro em log
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            
            // Retornar mensagem de erro
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function carregaPerfil()
    {
        // Garantir que a resposta seja JSON
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Verificar se o usuário está autenticado
            $usuarioLogado = \App\Middleware\AuthMiddleware::verificar();
            
            // Obter o ID do usuário (pode ser passado como parâmetro ou usar o do usuário logado)
            $id = $_GET['id'] ?? $usuarioLogado['id'];
            
            // Se o usuário não é admin e está tentando ver outro perfil, negar acesso
            if ($usuarioLogado['id'] != $id && $usuarioLogado['nivel'] != 'admin') {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            // Buscar os dados do usuário
            $usuario = \App\Models\Usuario::buscarPorId($id);
            
            if ($usuario) {
                // Remover a senha antes de enviar para o frontend
                unset($usuario['senha']);
                
                echo json_encode([
                    'success' => true,
                    'usuario' => $usuario
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ]);
            }
        } catch (\Exception $e) {
            error_log("Erro ao carregar perfil: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ]);
        }
        exit;
    }

}