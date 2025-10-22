<?php
namespace App\Models;

class Usuario
{
    public static function listar($limite = 50)
    {
        $pdo = \App\Config\Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM integra_usuarios 
                ORDER BY id DESC
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
        $pdo = \App\Config\Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM integra_usuarios WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Usuario::buscarPorId falhou: " . $e->getMessage());
            return false;
        }
    }

    public static function buscarPorEmail($email)
    {
        $pdo = \App\Config\Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM integra_usuarios WHERE email = ?");
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
        $pdo = \App\Config\Database::getConnection();
        
        $nome = trim($dados['nome'] ?? '');
        $nivel = trim($dados['nivel'] ?? '');
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

            // Hash the password before storing
            $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO integra_usuarios (nome, nivel, email, senha, cpf, telefone)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nome, $nivel, $email, $senha_hashed, $cpf, $telefone]);

            return $pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log("Usuario::cadastrar falhou: " . $e->getMessage());
            throw $e;
        }
    }

public static function atualizar($id, $dados)
{
    $pdo = \App\Config\Database::getConnection();
    
    try {
        // Primeiro verifica se o usuário existe
        if (!self::buscarPorId($id)) {
            throw new \Exception('Usuário não encontrado.');
        }
        
        $sets = [];
        $valores = [];
        
        foreach ($dados as $campo => $valor) {
            // Permite atualizar apenas campos específicos
            if (in_array($campo, ['nome', 'nivel', 'email', 'cpf', 'telefone'])) {
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
        
        if ($stmt->rowCount() === 0) {
            throw new \Exception('Nenhuma linha foi atualizada. Verifique se os dados são diferentes.');
        }
        
        return $result;
    } catch (\Exception $e) {
        error_log("Usuario::atualizar falhou: " . $e->getMessage());
        throw $e;
    }
}

    public static function deletar($id)
    {
        $pdo = \App\Config\Database::getConnection();
        
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
        $pdo = \App\Config\Database::getConnection();
        
        try {
            // Include senha field in the query
            $stmt = $pdo->prepare("SELECT id, nome, email, nivel, senha FROM integra_usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Verify password (assuming passwords are stored hashed)
                if (password_verify($senha, $usuario['senha'])) {
                    // Remove password from user data before creating session
                    unset($usuario['senha']);
                    
                    // Gera o token JWT e cria a sessão
                    $userData = [
                        'id' => $usuario['id'],
                        'nome' => $usuario['nome'],
                        'email' => $usuario['email'],
                        'nivel' => $usuario['nivel']
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
                    return ['success' => false, 'message' => 'Credenciais inválidas'];
                }
            } else {
                return ['success' => false, 'message' => 'Credenciais inválidas'];
            }
        } catch (\Exception $e) {
            error_log("Usuario::login falhou: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno'];
        }
    }

    public static function verificarToken($token)
    {
        try {
            $resultado = \App\Utils\JWT::verifySession($token);
            return ['success' => true, 'usuario' => $resultado['payload']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

}