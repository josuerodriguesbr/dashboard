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
        // Remover o cookie de autenticação
        setcookie('authToken', '', [
            'expires' => time() - 3600,
            'path' => '/projetos/dashboard/',
            'secure' => false,
            'samesite' => 'Lax'
        ]);

        echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
        exit;
    }
    
    // Método para lidar com cadastro via convite
    public function cadastroViaConvite()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Pegar o hash do convite da URL ou do input
        $hashAnfitriao = $_GET['hash'] ?? $input['hashAnfitriao'] ?? null;
        $tipoPerfil = $_GET['tipo'] ?? $input['tipoPerfil'] ?? 'cliente'; // Padrão é 'cliente'
        
        if (!$hashAnfitriao) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Hash do convite não fornecido']);
            exit;
        }
        
        // Verificar se o hash do anfitrião é válido
        $perfilAnfitriao = Usuario::buscarPerfilPorHashConvite($hashAnfitriao);
        if (!$perfilAnfitriao) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Hash de convite inválido']);
            exit;
        }
        
        try {
            // Iniciar transação
            $pdo = \Config\Database::getConnection();
            $pdo->beginTransaction();

            // Fazer o cadastro normal do usuário SEM criar perfil padrão
            $usuarioId = $this->cadastrarUsuarioSemPerfilPadrao($input, $hashAnfitriao);
            
            if ($usuarioId) {
                // Agora criar o perfil específico com base no tipo informado
                $this->criarPerfilEspecifico($usuarioId, $tipoPerfil, $hashAnfitriao);
                
                // Definir esse perfil como ativo
                $perfil = $this->obterPerfilPorPapel($usuarioId, $tipoPerfil);
                if ($perfil) {
                    $this->definirPerfilAtivoManual($usuarioId, $perfil['perfil_id']);
                }
                
                // Após o cadastro, fazer login automático
                $email = $input['email'];
                $senha = $input['senha'];
                
                $resultado = Usuario::login($email, $senha);
                
                if (isset($resultado['success']) && $resultado['success'] && isset($resultado['token'])) {
                    // Definir o cookie com a nova sessão
                    setcookie('authToken', $resultado['token'], [
                        'expires' => time() + (defined('JWT_EXPIRE') ? JWT_EXPIRE : 120), // 2 minutos por padrão
                        'path' => '/projetos/dashboard/',
                        'secure' => false,
                        'samesite' => 'Lax'
                    ]);
                }
                
                // Confirmar transação
                $pdo->commit();
                
                // Obter o nível do usuário através do perfil ativo
                $usuario = Usuario::buscarPorId($usuarioId);
                $nivel = isset($usuario['papel_nivel']) ? $usuario['papel_nivel'] : 'cliente';
                $redirectUrl = getRotaPorUserNivel($nivel);

                \App\Models\Log::registrar(
                    $usuarioId,
                    'Cadastro via convite',
                    "Usuário ID: $usuarioId, Convidado por perfil ID: {$perfilAnfitriao['id']}, Tipo de perfil: $tipoPerfil"
                );

                json_response([
                    'success' => true,
                    'id' => $usuarioId,
                    'token' => isset($resultado['token']) ? $resultado['token'] : null,
                    'user' => isset($resultado['usuario']) ? $resultado['usuario'] : null,
                    'redirect' => $redirectUrl
                ]);
            } else {
                $pdo->rollback();
                throw new \Exception("Erro ao cadastrar usuário via convite");
            }
        } catch (\Exception $e) {
            error_log("Erro no cadastro via convite: " . $e->getMessage());
            json_response(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    // Método auxiliar para cadastrar usuário sem criar perfil padrão
    private function cadastrarUsuarioSemPerfilPadrao($dados, $hashAnfitriao = null)
    {
        $pdo = \Config\Database::getConnection();
        
        $nome = trim($dados['nome'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = trim($dados['senha'] ?? '');
        $cpf = trim($dados['cpf'] ?? '');
        $telefone = trim($dados['telefone'] ?? '');

        // Validação básica
        if (empty($nome) || empty($email) || empty($senha)) {
            throw new \Exception('Nome, e-mail e senha são obrigatórios.');
        }

        // Verifica se o e-mail já está cadastrado
        if (Usuario::buscarPorEmail($email)) {
            throw new \Exception('E-mail já cadastrado.');
        }

        // Hash the password before storing
        $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO integra_usuarios (nome, email, senha, cpf, telefone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nome, $email, $senha_hashed, $cpf, $telefone]);
        
        return $pdo->lastInsertId();
    }
    
    // Método auxiliar para criar o perfil específico
    private function criarPerfilEspecifico($usuarioId, $papelNivel, $hashAnfitriao)
    {
        $pdo = \Config\Database::getConnection();
        
        // Obter o ID do papel
        $papelStmt = $pdo->prepare("SELECT id FROM integra_papeis WHERE nivel = ?");
        $papelStmt->execute([$papelNivel]);
        $papel = $papelStmt->fetch();
        
        if (!$papel) {
            throw new \Exception("Papel '$papelNivel' não encontrado.");
        }
        
        // Gerar hash de convite para o novo perfil
        $hashConvite = Usuario::gerarHash();
        
        // Criar perfil para o usuário com hashAnfitriao e hashConvite
        $perfilStmt = $pdo->prepare("
            INSERT INTO integra_perfis (id_papel, id_usuario, creditos, hashConvite, hashAnfitriao, status)
            VALUES (?, ?, 0.00, ?, ?, 'Ativo')
        ");
        $perfilStmt->execute([$papel['id'], $usuarioId, $hashConvite, $hashAnfitriao]);
        
        return $pdo->lastInsertId();
    }
    
    // Método auxiliar para definir o perfil ativo
    private function definirPerfilAtivoManual($usuarioId, $perfilId)
    {
        $pdo = \Config\Database::getConnection();
        
        // Atualizar o perfil ativo do usuário
        $updateStmt = $pdo->prepare("
            UPDATE integra_usuarios 
            SET idPerfilAtivo = ? 
            WHERE id = ?
        ");
        $updateStmt->execute([$perfilId, $usuarioId]);
        
        // Atualizar o contexto do usuário
        \App\Utils\UserContext::setNivelAtivo($this->getNivelPerfil($perfilId));
    }
    
    // Método auxiliar para obter o nível do perfil
    private function getNivelPerfil($perfilId)
    {
        $pdo = \Config\Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.nivel
            FROM integra_perfis ip
            JOIN integra_papeis p ON ip.id_papel = p.id
            WHERE ip.id = ?
        ");
        $stmt->execute([$perfilId]);
        $result = $stmt->fetch();
        
        return $result ? $result['nivel'] : 'cliente';
    }
    
    // Método para obter o perfil por papel
    private function obterPerfilPorPapel($usuarioId, $papelNivel)
    {
        $perfis = Usuario::getPapeisDoUsuario($usuarioId);
        foreach ($perfis as $perfil) {
            if ($perfil['nivel'] === $papelNivel) {
                return $perfil;
            }
        }
        return null;
    }
    
    // Método para gerar link de convite
    public function gerarLinkConvite()
    {
        try {
            // Verificar autenticação
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Buscar o perfil ativo do usuário
            $perfilAtivo = Usuario::buscarPorId($usuario['id']);
            
            if ($perfilAtivo && isset($perfilAtivo['hashConvite'])) {
                $link = $_SERVER['HTTP_HOST'] . '/projetos/dashboard/cadastro-via-convite?hash=' . $perfilAtivo['hashConvite'];
                
                json_response([
                    'success' => true,
                    'link' => $link,
                    'hash' => $perfilAtivo['hashConvite']
                ]);
            } else {
                json_response(['success' => false, 'message' => 'Perfil não encontrado'], 404);
            }
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    // Método para mostrar o formulário de cadastro via convite
    public function mostrarCadastroViaConvite()
    {
        $hash = $_GET['hash'] ?? null;
        $tipo = $_GET['tipo'] ?? 'cliente'; // Padrão é cliente
        
        if (!$hash) {
            // Se não tiver hash, redireciona para a página principal
            header('Location: /projetos/dashboard/');
            exit;
        }
        
        // Verificar se o hash é válido
        $perfilAnfitriao = Usuario::buscarPerfilPorHashConvite($hash);
        if (!$perfilAnfitriao) {
            // Se o hash for inválido, redireciona para a página principal
            header('Location: /projetos/dashboard/');
            exit;
        }
        
        // Mostrar o formulário de cadastro com informações do anfitrião
        $data = [
            'title' => 'Cadastro via Convite',
            'anfitriao' => $perfilAnfitriao['nome'],
            'hash' => $hash,
            'tipo' => $tipo,
            'ignorarAutenticacao' => true // Permite acesso sem autenticação
        ];
        
        view('recursos/usuarios/cadastro-usuario-via-convite', $data);
    }
}