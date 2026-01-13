<?php
namespace App\Models;
use Config\Database;
use App\Utils\UserContext;
use App\Models\Carteira;
use PDO;

class Usuario
{
    public static function listar($limite = 50)
    {
        $pdo = Database::getConnection();
        try {
            // Updated query to include profile status info
            $stmt = $pdo->prepare("
                SELECT u.*, p.status as perfil_status, pa.nivel as papel_nivel
                FROM usuarios u
                LEFT JOIN perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN papeis pa ON p.id_papel = pa.id
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

    public static function listarPorParentId($parentId)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT id, nome, email, telefone, created_at FROM usuarios WHERE parent_id = ? ORDER BY created_at DESC");
            $stmt->execute([$parentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Usuario::listarPorParentId falhou: " . $e->getMessage());
            return [];
        }
    }

    public static function buscarPorId($id)
    {
        $pdo = Database::getConnection();
        try {
            // Updated query to fetch profile info
            $stmt = $pdo->prepare("
                SELECT u.*, p.status as perfil_status, p.hashConvite, p.hashAnfitriao, pa.nivel as papel_nivel
                FROM usuarios u
                LEFT JOIN perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN papeis pa ON p.id_papel = pa.id
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                if (class_exists('\App\Utils\UserContext')) {
                    \App\Utils\UserContext::setUsuario([
                        'id' => $usuario['id'],
                        'nome' => $usuario['nome'],
                        'email' => $usuario['email'],
                        'parent_id' => $usuario['parent_id'],
                        'papel_nivel' => $usuario['papel_nivel'] // Added back
                    ]);
                }
            }

            return $usuario;
        } catch (\Exception $e) {
            error_log("Usuario::buscarPorId falhou: " . $e->getMessage());
            return false;
        }
    }

    // ... (buscarPorEmail kept same)
    public static function buscarPorEmail($email)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Usuario::buscarPorEmail falhou: " . $e->getMessage());
            return false;
        }
    }

    public static function cadastrar($dados)
    {
        $pdo = Database::getConnection();

        $nome = trim($dados['nome'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = trim($dados['senha'] ?? '');
        $cpf = trim($dados['cpf'] ?? '');
        $telefone = trim($dados['telefone'] ?? '');
        $parentId = !empty($dados['parent_id']) ? $dados['parent_id'] : null;

        if (empty($nome) || empty($email) || empty($senha)) {
            throw new \Exception('Nome, e-mail e senha são obrigatórios.');
        }

        try {
            if (self::buscarPorEmail($email)) {
                throw new \Exception('E-mail já cadastrado.');
            }

            $pdo->beginTransaction();

            $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha, cpf, telefone, parent_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nome, $email, $senha_hashed, $cpf, $telefone, $parentId]);

            $usuario_id = $pdo->lastInsertId();

            // Criar Carteira
            $carteira = new Carteira($pdo);
            $carteira->createForUser($usuario_id);

            $pdo->commit();

            return $usuario_id;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollback();
            }
            throw $e;
        }
    }

    // ... (atualizar, deletar kept same)
    public static function atualizar($id, $dados)
    {
        $pdo = Database::getConnection();

        try {
            if (!self::buscarPorId($id)) {
                throw new \Exception('Usuário não encontrado.');
            }

            $sets = [];
            $valores = [];

            foreach ($dados as $campo => $valor) {
                if (in_array($campo, ['nome', 'email', 'cpf', 'telefone', 'parent_id'])) {
                    $sets[] = "$campo = ?";
                    $valores[] = $valor;
                }

                if ($campo === 'senha' && !empty($valor)) {
                    $sets[] = "senha = ?";
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

            $sql = "UPDATE usuarios SET " . implode(', ', $sets) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($valores);

        } catch (\Exception $e) {
            error_log("Usuario::atualizar falhou: " . $e->getMessage());
            throw $e;
        }
    }

    public static function deletar($id)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
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
            // Updated login query to include role info
            $stmt = $pdo->prepare("
                SELECT u.*, p.status as perfil_status, pa.nivel as papel_nivel
                FROM usuarios u
                LEFT JOIN perfis p ON u.idPerfilAtivo = p.id
                LEFT JOIN papeis pa ON p.id_papel = pa.id
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                unset($usuario['senha']);

                // Prefer profile role, fallback to parent_id logic
                $nivel = $usuario['papel_nivel'] ?? (($usuario['parent_id'] === null) ? 'admin' : 'cliente');

                if (class_exists('\App\Utils\UserContext')) {
                    \App\Utils\UserContext::setUsuario([
                        'id' => $usuario['id'],
                        'nome' => $usuario['nome'],
                        'email' => $usuario['email'],
                        'parent_id' => $usuario['parent_id'],
                        'papel_nivel' => $nivel
                    ]);
                }

                $userData = [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email'],
                    'parent_id' => $usuario['parent_id'],
                    'nivel' => $nivel,
                    'idPerfilAtivo' => $usuario['idPerfilAtivo']
                ];

                $sessionData = \App\Utils\JWT::createSession($usuario['id'], $userData);

                if ($sessionData) {
                    return [
                        'success' => true,
                        'token' => $sessionData['token'],
                        'usuario' => $userData,
                        'saldo' => self::getSaldo($usuario['id'])
                    ];
                }
            }

            return ['success' => false, 'message' => 'Credenciais inválidas'];

        } catch (\Exception $e) {
            error_log("Usuario::login falhou: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno'];
        }
    }

    // ... (verificarToken kept same but ensure context hydration)
    public static function verificarToken($token)
    {
        try {
            $resultado = \App\Utils\JWT::verifySession($token);

            if (isset($resultado['payload']['id'])) {
                $saldo = self::getSaldo($resultado['payload']['id']);
                $resultado['payload']['saldo'] = $saldo;

                // Buscar asaas_id fresco do banco
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT asaas_id FROM usuarios WHERE id = ?");
                $stmt->execute([$resultado['payload']['id']]);
                $extra = $stmt->fetch();
                if ($extra) {
                    $resultado['payload']['asaas_id'] = $extra['asaas_id'];
                }

                // Re-hidratar context
                if (class_exists('\App\Utils\UserContext')) {
                    \App\Utils\UserContext::setUsuario($resultado['payload']);
                }
            }

            return ['success' => true, 'usuario' => $resultado['payload']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ... (getSaldo kept same - uses Carteira)
    public static function getSaldo($usuarioId)
    {
        $pdo = Database::getConnection();
        try {
            $carteiraModel = new Carteira($pdo);
            $carteira = $carteiraModel->getByUserId($usuarioId);
            return $carteira ? (int) $carteira['saldo_atual'] : 0;
        } catch (\Exception $e) {
            error_log("Usuario::getSaldo falhou: " . $e->getMessage());
            return 0;
        }
    }

    // ... (adicionarTransacao kept same)
    public static function adicionarTransacao($usuarioId, $tipoId, $valor, $descricao, $recargaId = null)
    {
        return \App\Models\CreditoTransacao::criar($usuarioId, $tipoId, $valor, $descricao, $recargaId);
    }

    public static function atualizarAsaasId($usuarioId, $asaasId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE usuarios SET asaas_id = ? WHERE id = ?");
        return $stmt->execute([$asaasId, $usuarioId]);
    }

    // RESTORED METHODS FOR PROFILES

    public static function getPerfilAtivo($usuarioId)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, pa.nivel as papel_nivel
                FROM perfis p
                JOIN papeis pa ON p.id_papel = pa.id
                WHERE p.id = (SELECT idPerfilAtivo FROM usuarios WHERE id = ?)
            ");
            $stmt->execute([$usuarioId]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Usuario::getPerfilAtivo falhou: " . $e->getMessage());
            return false;
        }
    }

    public static function definirPerfilAtivo($usuarioId, $perfilId)
    {
        $pdo = Database::getConnection();
        try {
            // Verify ownership
            $stmt = $pdo->prepare("SELECT id FROM perfis WHERE id = ? AND id_usuario = ?");
            $stmt->execute([$perfilId, $usuarioId]);
            if (!$stmt->fetch()) {
                throw new \Exception("Perfil não pertence ao usuário.");
            }

            $stmt = $pdo->prepare("UPDATE usuarios SET idPerfilAtivo = ? WHERE id = ?");
            return $stmt->execute([$perfilId, $usuarioId]);
        } catch (\Exception $e) {
            error_log("Usuario::definirPerfilAtivo falhou: " . $e->getMessage());
            return false;
        }
    }

    // Added hash generation util
    public static function gerarHash()
    {
        return bin2hex(random_bytes(32));
    }
}
