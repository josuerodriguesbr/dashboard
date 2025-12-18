<?php
namespace App\Models;
use Config\Database;
use App\Utils\UserContext;

class Usuario
{
    public static function listar($limite = 50)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT u.*, p.status as perfil_status, p.creditos as perfil_creditos, pa.nivel as papel_nivel
                FROM integra_usuarios u
                LEFT JOIN integra_perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN integra_papeis pa ON p.id_papel = pa.id
                ORDER BY u.id DESC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Usuario::listar falhou: " . $e->getMessage());
            return [];
        }
    }

    public static function buscarPorId($id)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT u.*, p.status as perfil_status, p.creditos as perfil_creditos, pa.nivel as papel_nivel
                FROM integra_usuarios u
                LEFT JOIN integra_perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Usuario::buscarPorId falhou: " . $e->getMessage());
            return false;
        }
    }

    public static function buscarPorEmail($email)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT u.*, p.status as perfil_status, p.creditos as perfil_creditos, pa.nivel as papel_nivel
                FROM integra_usuarios u
                LEFT JOIN integra_perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Usuario::buscarPorEmail falhou: " . $e->getMessage());
            return false;
        }
    }

    // In the cadastrar method (around line 45)
    public static function cadastrar($dados)
    {
        $pdo = Database::getConnection();
        
        $nome = trim($dados['nome'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = trim($dados['senha'] ?? ''); // This is the plain text password
        $cpf = trim($dados['cpf'] ?? '');
        $telefone = trim($dados['telefone'] ?? '');

        // Validação básica
        if (empty($nome) || empty($email) || empty($senha)) {
            throw new \Exception('Nome, e-mail e senha são obrigatórios.');
        }

        try {
            // Verifica se o e-mail já está cadastrado
            if (self::buscarPorEmail($email)) {
                throw new \Exception('E-mail já cadastrado.');
            }

            // Iniciar transação
            $pdo->beginTransaction();

            // Hash the password before storing
            $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO integra_usuarios (nome, email, senha, cpf, telefone)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nome, $email, $senha_hashed, $cpf, $telefone]);
            
            $usuario_id = $pdo->lastInsertId();
            
            // Criar perfil 'cliente' para o novo usuário
            if ($usuario_id) {
                try {
                    // Obter o ID do papel 'cliente'
                    $papel_stmt = $pdo->prepare("SELECT id FROM integra_papeis WHERE nivel = 'cliente' LIMIT 1");
                    $papel_stmt->execute();
                    $papel = $papel_stmt->fetch();
                    
                    if ($papel) {
                        // Criar perfil para o usuário
                        $perfil_stmt = $pdo->prepare("
                            INSERT INTO integra_perfis (id_papel, id_usuario, creditos, status)
                            VALUES (?, ?, 0.00, 'Ativo')
                        ");
                        $perfil_stmt->execute([$papel['id'], $usuario_id]);
                        
                        $perfil_id = $pdo->lastInsertId();
                        
                        // Atualizar o campo idPerfilAtivo do usuário
                        $update_stmt = $pdo->prepare("
                            UPDATE integra_usuarios 
                            SET idPerfilAtivo = ? 
                            WHERE id = ?
                        ");
                        $update_stmt->execute([$perfil_id, $usuario_id]);
                    }
                } catch (\Exception $e) {
                    // Ignorar erros de chave duplicada ou restrições de trigger
                    error_log("Aviso: Erro ao criar perfil automático para usuário $usuario_id: " . $e->getMessage());
                }
            }
            
            // Confirmar transação
            $pdo->commit();

            return $usuario_id;
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            $pdo->rollback();
            error_log("Usuario::cadastrar falhou: " . $e->getMessage());
            throw $e;
        }
    }

public static function atualizar($id, $dados)
{
    $pdo = Database::getConnection();
    
    try {
        // Primeiro verifica se o usuário existe
        if (!self::buscarPorId($id)) {
            throw new \Exception('Usuário não encontrado.');
        }
        
        $sets = [];
        $valores = [];
        
        foreach ($dados as $campo => $valor) {
            // Permite atualizar apenas campos específicos
            if (in_array($campo, ['nome', 'email', 'cpf', 'telefone'])) {
                $sets[] = "$campo = ?";
                $valores[] = $valor;
            }
            
            // Trata senha especialmente
            if ($campo === 'senha' && !empty($valor)) {
                $sets[] = "senha = ?";
                // Faz hash da senha se não estiver criptografada
                if (!password_get_info($valor)['algo']) {
                    $valores[] = password_hash($valor, PASSWORD_DEFAULT);
                } else {
                    $valores[] = $valor;
                }
            }
        }
        
        if (empty($sets)) {
            throw new \Exception('Nenhum dado válido para atualizar.');
        }
        
        $valores[] = $id;
        
        $sql = "UPDATE integra_usuarios SET " . implode(', ', $sets) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($valores);
        
        return true;
    } catch (\Exception $e) {
        error_log("Usuario::atualizar falhou: " . $e->getMessage());
        throw $e;
    }
}

    public static function deletar($id)
    {
        $pdo = Database::getConnection();
        
        try {
            $stmt = $pdo->prepare("DELETE FROM integra_usuarios WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log("Usuario::deletar falhou: " . $e->getMessage());
            return false;
        }
    }

    public static function login($email, $senha)
    {
        $pdo = Database::getConnection();
        
        try {
            error_log("Iniciando login para o email: " . $email);
            
            // Consulta mais abrangente para pegar todos os perfis do usuário
            $stmt = $pdo->prepare("
                SELECT u.id, u.nome, u.email, u.senha, u.idPerfilAtivo,
                       p.status as perfil_status, pa.nivel as papel_nivel, p.id as perfil_id
                FROM integra_usuarios u
                LEFT JOIN integra_perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            error_log("Resultado da consulta: " . print_r($usuario, true));
            
            if ($usuario) {
                // Verifica se o perfil está ativo
                if (isset($usuario['perfil_status']) && $usuario['perfil_status'] !== 'Ativo') {
                    error_log("Perfil bloqueado para o usuário: " . $email);
                    return ['success' => false, 'message' => 'Perfil bloqueado.'];
                }
                
                // Verificar se o usuário tem um papel definido
                if (!isset($usuario['papel_nivel']) || empty($usuario['papel_nivel'])) {
                    error_log("Usuário sem papel atribuído: " . $email);
                    
                    // Tentar encontrar qualquer perfil ativo do usuário
                    if (!isset($usuario['perfil_id']) || empty($usuario['perfil_id'])) {
                        error_log("Usuário sem perfil ativo definido: " . $email);
                        
                        // Verificar se existe algum perfil para este usuário
                        $perfisStmt = $pdo->prepare("
                            SELECT p.id, p.status, pa.nivel, pa.descricao
                            FROM integra_perfis p
                            JOIN integra_papeis pa ON p.id_papel = pa.id
                            WHERE p.id_usuario = ? AND p.status = 'Ativo'
                            LIMIT 1
                        ");
                        $perfisStmt->execute([$usuario['id']]);
                        $perfil = $perfisStmt->fetch();
                        
                        if ($perfil) {
                            error_log("Encontrado perfil alternativo para o usuário: " . print_r($perfil, true));
                            
                            // Atualizar o perfil ativo do usuário
                            $updateStmt = $pdo->prepare("UPDATE integra_usuarios SET idPerfilAtivo = ? WHERE id = ?");
                            $updateStmt->execute([$perfil['id'], $usuario['id']]);
                            
                            // Recarregar os dados do usuário
                            $stmt = $pdo->prepare("
                                SELECT u.id, u.nome, u.email, u.senha, u.idPerfilAtivo,
                                       p.status as perfil_status, pa.nivel as papel_nivel, p.id as perfil_id
                                FROM integra_usuarios u
                                LEFT JOIN integra_perfis p ON u.idPerfilAtivo = p.id
                                LEFT JOIN integra_papeis pa ON p.id_papel = pa.id
                                WHERE u.email = ?
                            ");
                            $stmt->execute([$email]);
                            $usuario = $stmt->fetch();
                            
                            error_log("Dados do usuário após atualização: " . print_r($usuario, true));
                        } else {
                            error_log("Nenhum perfil ativo encontrado para o usuário");
                        }
                    }
                }
                
                // Verificar novamente se agora temos um papel definido
                if (!isset($usuario['papel_nivel']) || empty($usuario['papel_nivel'])) {
                    return ['success' => false, 'message' => 'Usuário sem papel atribuído.'];
                }
                
                // Verify password (assuming passwords are stored hashed)
                if (password_verify($senha, $usuario['senha'])) {
                    // Remove password from user data before creating session
                    unset($usuario['senha']);
                    
                    // Determinar o nível com base no perfil ativo
                    $nivel = $usuario['papel_nivel'] ?? 'cliente';
                    
                    // Definir o nível ativo no contexto do usuário
                    UserContext::setNivelAtivo($nivel);
                    UserContext::setUsuario([
                        'id' => $usuario['id'],
                        'nome' => $usuario['nome'],
                        'email' => $usuario['email']
                    ]);
                    
                    // Verificar se perfil_id está definido antes de usá-lo
                    if (isset($usuario['perfil_id'])) {
                        UserContext::setPerfilAtivo([
                            'id' => $usuario['perfil_id'],
                            'nivel' => $nivel,
                            'status' => $usuario['perfil_status'] ?? 'Ativo'
                        ]);
                    }
                    
                    // Gera o token JWT e cria a sessão
                    $userData = [
                        'id' => $usuario['id'],
                        'nome' => $usuario['nome'],
                        'email' => $usuario['email'],
                        'nivel' => $nivel
                    ];
                    
                    $sessionData = \App\Utils\JWT::createSession($usuario['id'], $userData);
                    
                    if ($sessionData) {
                        return [
                            'success' => true,
                            'token' => $sessionData['token'],
                            'usuario' => $userData
                        ];
                    } else {
                        return ['success' => false, 'message' => 'Erro ao criar sessão'];
                    }
                } else {
                    // Incrementa tentativas de login falhas
                    self::incrementarTentativasLogin($email);
                    return ['success' => false, 'message' => 'Credenciais inválidas'];
                }
            } else {
                error_log("Nenhum usuário encontrado com o email: " . $email);
                return ['success' => false, 'message' => 'Credenciais inválidas'];
            }
        } catch (\Exception $e) {
            error_log("Usuario::login falhou: " . $e->getMessage());
            error_log("Usuario::login trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    public static function verificarToken($token)
    {
        try {
            $resultado = \App\Utils\JWT::verifySession($token);
            
            // Definir o nível ativo no contexto do usuário com base no perfil ativo
            if (isset($resultado['payload']['id'])) {
                // Buscar o perfil ativo do usuário para obter o nível
                $usuario = self::buscarPorId($resultado['payload']['id']);
                if ($usuario && isset($usuario['papel_nivel'])) {
                    UserContext::setNivelAtivo($usuario['papel_nivel']);
                } else {
                    UserContext::setNivelAtivo('cliente');
                }
                
                UserContext::setUsuario([
                    'id' => $resultado['payload']['id'],
                    'nome' => $resultado['payload']['nome'],
                    'email' => $resultado['payload']['email']
                ]);
            }
            
            return ['success' => true, 'usuario' => $resultado['payload']];
        } catch (\Exception $e) {
            error_log("Usuario::verificarToken falhou: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private static function atualizarUltimoLogin($usuarioId)
    {
        // Como não temos colunas específicas para controle de último login,
        // apenas registramos no log
        error_log("Usuário ID " . $usuarioId . " fez login com sucesso");
    }
    
    private static function incrementarTentativasLogin($email)
    {
        // Como não temos colunas específicas para controle de tentativas de login,
        // apenas registramos a tentativa no log
        error_log("Tentativa de login falha para o email: " . $email);
    }
    
    public static function buscarPorStatus($status, $limite = 50)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT u.*, p.status as perfil_status, p.creditos as perfil_creditos, pa.nivel as papel_nivel
                FROM integra_usuarios u
                LEFT JOIN integra_perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE u.status = ? ORDER BY u.id DESC LIMIT ?");
            $stmt->execute([$status, $limite]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Usuario::buscarPorStatus falhou: " . $e->getMessage());
            return [];
        }
    }
    
    public static function getPapeisDoUsuario($usuarioId)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT pa.nivel, pa.descricao, p.status as perfil_status, p.creditos, p.id as perfil_id
                FROM integra_perfis p
                JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE p.id_usuario = ?
            ");
            $stmt->execute([$usuarioId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Usuario::getPapeisDoUsuario falhou: " . $e->getMessage());
            return [];
        }
    }
    
    public static function adicionarPapelAoUsuario($usuarioId, $papelNivel)
    {
        $pdo = Database::getConnection();
        try {
            // Verificar se o usuário já tem este papel
            $checkStmt = $pdo->prepare("
                SELECT p.id
                FROM integra_perfis p
                JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE p.id_usuario = ? AND pa.nivel = ?
            ");
            $checkStmt->execute([$usuarioId, $papelNivel]);
            $perfilExistente = $checkStmt->fetch();
            
            // Se já tiver o papel, não faz nada
            if ($perfilExistente) {
                return true;
            }
            
            // Obter o ID do papel
            $papelStmt = $pdo->prepare("SELECT id FROM integra_papeis WHERE nivel = ?");
            $papelStmt->execute([$papelNivel]);
            $papel = $papelStmt->fetch();
            
            if (!$papel) {
                throw new \Exception("Papel '$papelNivel' não encontrado.");
            }
            
            // Criar novo perfil
            $insertStmt = $pdo->prepare("
                INSERT INTO integra_perfis (id_papel, id_usuario, creditos, status)
                VALUES (?, ?, 0.00, 'Ativo')
            ");
            $insertStmt->execute([$papel['id'], $usuarioId]);
            
            return true;
        } catch (\Exception $e) {
            error_log("Usuario::adicionarPapelAoUsuario falhou: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function definirPerfilAtivo($usuarioId, $perfilId)
    {
        $pdo = Database::getConnection();
        try {
            // Verificar se o perfil pertence ao usuário
            $checkStmt = $pdo->prepare("
                SELECT p.id, pa.nivel as papel_nivel
                FROM integra_perfis p
                JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE p.id = ? AND p.id_usuario = ?
            ");
            $checkStmt->execute([$perfilId, $usuarioId]);
            $perfil = $checkStmt->fetch();
            
            if (!$perfil) {
                throw new \Exception("Perfil inválido ou não pertence ao usuário.");
            }
            
            // Atualizar o perfil ativo do usuário
            $updateStmt = $pdo->prepare("
                UPDATE integra_usuarios 
                SET idPerfilAtivo = ? 
                WHERE id = ?
            ");
            $updateStmt->execute([$perfilId, $usuarioId]);
            
            // Atualizar o contexto do usuário
            UserContext::setNivelAtivo($perfil['papel_nivel']);
            UserContext::setPerfilAtivo([
                'id' => $perfil['id'],
                'nivel' => $perfil['papel_nivel'],
                'status' => 'Ativo'
            ]);
            
            return true;
        } catch (\Exception $e) {
            error_log("Usuario::definirPerfilAtivo falhou: " . $e->getMessage());
            throw $e;
        }
    }
}