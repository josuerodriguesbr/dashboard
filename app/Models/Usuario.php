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

    public static function cadastrar($dados)
    {
        $pdo = \App\Config\Database::getConnection();
        
        $nome = trim($dados['nome'] ?? '');
        $nivel = trim($dados['nivel'] ?? '');
        $email = trim($dados['email'] ?? '');
        $cpf = trim($dados['cpf'] ?? '');
        $telefone = trim($dados['telefone'] ?? '');

        // Validação básica
        if (empty($nome) || empty($email)) {
            throw new \Exception('Nome e e-mail são obrigatórios.');
        }

        try {
            // Verifica se o e-mail já está cadastrado
            if (self::buscarPorEmail($email)) {
                throw new \Exception('E-mail já cadastrado.');
            }

            $stmt = $pdo->prepare("
                INSERT INTO integra_usuarios (nome, nivel, email, cpf, telefone)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nome, $nivel, $email, $cpf, $telefone]);

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
            $sets = [];
            $valores = [];
            
            foreach ($dados as $campo => $valor) {
                // Permite atualizar apenas campos específicos
                if (in_array($campo, ['nome', 'nivel', 'email', 'cpf', 'telefone'])) {
                    $sets[] = "$campo = ?";
                    $valores[] = $valor;
                }
            }
            
            if (empty($sets)) {
                throw new \Exception('Nenhum dado válido para atualizar.');
            }
            
            $valores[] = $id;
            
            $sql = "UPDATE integra_usuarios SET " . implode(', ', $sets) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($valores);
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

    // Atualização no app/Models/Usuario.php
    public static function login($email, $senha)
    {
        $pdo = \App\Config\Database::getConnection();
        
        try {
            $stmt = $pdo->prepare("SELECT id, nome, email, nivel FROM integra_usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Aqui você pode adicionar verificação de senha se tiver uma coluna de senha
                // Por enquanto, vamos apenas verificar se o usuário existe
                
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