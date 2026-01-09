<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Carteira;
use App\Utils\JWT;

class AuthController
{
    public function login()
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            echo json_encode(['success' => false, 'message' => 'E-mail e senha são obrigatórios']);
            exit;
        }
        
        $resultado = Usuario::login($email, $senha);
        
        if ($resultado['success']) {
            $sessionData = JWT::createSession(
                $resultado['usuario']['id'],
                $resultado['usuario'],
                24 
            );
            
            if ($sessionData) {
                setcookie('authToken', $sessionData['token'], [
                    'expires' => time() + JWT_EXPIRE,
                    'path' => '/projetos/dashboard/',
                    'secure' => false,
                    'samesite' => 'Lax'
                ]);

                // Redirecionamento baseado no nível
                $redirect = ($resultado['usuario']['nivel'] === 'admin') ? '/projetos/dashboard/admin/dashboard' : '/projetos/dashboard/home';
                
                echo json_encode([
                    'success' => true,
                    'token' => $sessionData['token'],
                    'redirect' => $redirect,
                    'user' => $resultado['usuario']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar sessão']);
            }
        } else {
            echo json_encode($resultado);
        }
        exit;
    }

    public function logout()
    {
        if (ob_get_level()) ob_clean();
        // Limpar cookie em múltiplos caminhos para garantir
        $paths = ['/', '/projetos/dashboard', '/projetos/dashboard/'];
        foreach ($paths as $path) {
            setcookie('authToken', '', [
                'expires' => time() - 3600,
                'path' => $path,
                'secure' => false,
                'samesite' => 'Lax'
            ]);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
        exit;
    }
    
    // Cadastro simples (mantendo compatibilidade com rotas existentes)
    public function cadastrar() 
    {
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $id = Usuario::cadastrar($input);
            
            // Create default profile (Cliente) if none exists
            // This is new logic to ensure every user has a profile
            $papelCliente = \App\Models\Papel::buscarPorNivel('cliente');
            if ($papelCliente) {
                $perfilId = \App\Models\Perfil::criar($id, $papelCliente['id']);
                Usuario::definirPerfilAtivo($id, $perfilId);
            }

            echo json_encode(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Cadastro via convite (Restaurado para usar Perfis)
    public function cadastroViaConvite()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // O hash do convite vem na URL ou body
        $hashConvite = $_GET['hash'] ?? $input['hash'] ?? null;
        
        try {
            $pdo = \Config\Database::getConnection();
            
            // 1. Validar convite (Busca Perfil Anfitrião)
            $perfilAnfitriao = null;
            if ($hashConvite) {
                $stmt = $pdo->prepare("SELECT * FROM perfis WHERE hashConvite = ?");
                $stmt->execute([$hashConvite]);
                $perfilAnfitriao = $stmt->fetch();
                
                if (!$perfilAnfitriao) {
                    throw new \Exception('Convite inválido ou expirado.');
                }
            }

            // 2. Criar Usuário (Cria Wallet aut. pelo Model)
            // Se tiver perfil anfitrião, definimos o parent_id como o usuario dono do perfil
            if ($perfilAnfitriao) {
                $input['parent_id'] = $perfilAnfitriao['id_usuario'];
            }
            
            $usuarioId = Usuario::cadastrar($input);
            
            // 3. Criar Perfil para o novo usuário
            // Usar o tipo fornecido ou padrão 'cliente'
            $tipoSolicitado = $input['tipo'] ?? 'cliente';
            $papel = \App\Models\Papel::buscarPorNivel($tipoSolicitado);
            
            if (!$papel) {
                 // Fallback se o tipo for inválido
                 $papel = \App\Models\Papel::buscarPorNivel('cliente');
            }
            
            if ($papel) {
                // hashAnfitriao é o hash que foi usado para convidar
                $perfilId = \App\Models\Perfil::criar($usuarioId, $papel['id'], null, $hashConvite);
                Usuario::definirPerfilAtivo($usuarioId, $perfilId);
            }
            
            // 4. Login automático
            $resultado = Usuario::login($input['email'], $input['senha']);
            
            if ($resultado['success']) {
                setcookie('authToken', $resultado['token'], [
                    'expires' => time() + JWT_EXPIRE,
                    'path' => '/projetos/dashboard/',
                    'secure' => false,
                    'samesite' => 'Lax'
                ]);
                
                $redirect = '/projetos/dashboard/home';
                
                echo json_encode([
                    'success' => true,
                    'token' => $resultado['token'],
                    'redirect' => $redirect,
                    'user' => $resultado['usuario']
                ]);
            } else {
                echo json_encode(['success' => true, 'message' => 'Cadastro realizado, faça login.']);
            }

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function mostrarCadastroViaConvite()
    {
         $hash = $_GET['hash'] ?? '';
         $nomeAnfitriao = 'Sistema';
         $tipoConvite = $_GET['tipo'] ?? 'Novo Usuário';

         if ($hash) {
             try {
                $pdo = \Config\Database::getConnection();
                // Buscar perfil do anfitrião pelo hash
                $stmt = $pdo->prepare("
                    SELECT u.nome 
                    FROM perfis p
                    JOIN usuarios u ON p.id_usuario = u.id
                    WHERE p.hashConvite = ?
                ");
                $stmt->execute([$hash]);
                $resultado = $stmt->fetch();
                
                if ($resultado) {
                    $nomeAnfitriao = $resultado['nome'];
                }
             } catch (\Exception $e) {
                 // Silenciar erro na view, manter padrão
             }
         }

         view('recursos/usuarios/cadastro-convite', [
             'hash' => $hash, 
             'nomeAnfitriao' => $nomeAnfitriao,
             'tipoConvite' => $tipoConvite,
             'semLayout' => true
         ]);
    }
    
    public function gerarLinkConvite()
    {
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            $perfil = Usuario::getPerfilAtivo($usuario['id']);
            
            if ($perfil && !empty($perfil['hashConvite'])) {
                $link = BASE_URL . '/cadastro-via-convite?hash=' . $perfil['hashConvite'];
                json_response(['success' => true, 'link' => $link, 'hash' => $perfil['hashConvite']]);
            } else {
                json_response(['success' => false, 'message' => 'Perfil não possui hash de convite.']);
            }
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 401);
        }
    }
}