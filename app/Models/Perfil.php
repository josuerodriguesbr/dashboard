<?php
namespace App\Models;

use Config\Database;
use App\Models\Papel;
use App\Models\Usuario;

class Perfil
{
    /**
     * Lista todos os perfis de um usuário
     */
    public static function listarPorUsuario($usuarioId)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, pa.nivel as papel_nivel, pa.descricao as papel_descricao
                FROM integra_perfis p
                JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE p.id_usuario = ?
                ORDER BY pa.id ASC
            ");
            $stmt->execute([$usuarioId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Perfil::listarPorUsuario falhou: " . $e->getMessage());
            return [];
        }
    }

    public static function buscarPorId($id)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, pa.nivel as papel_nivel, u.nome as usuario_nome, u.email as usuario_email
                FROM integra_perfis p
                JOIN integra_papeis pa ON p.id_papel = pa.id
                JOIN integra_usuarios u ON p.id_usuario = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Perfil::buscarPorId falhou: " . $e->getMessage());
            return false;
        }
    }

    public static function criar($usuarioId, $papelId, $hashConvite = null, $hashAnfitriao = null, $status = 'Ativo')
    {
        $pdo = Database::getConnection();
        try {
            if (!$hashConvite) {
                $hashConvite = bin2hex(random_bytes(32));
            }

            $stmt = $pdo->prepare("
                INSERT INTO integra_perfis (id_usuario, id_papel, hashConvite, hashAnfitriao, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$usuarioId, $papelId, $hashConvite, $hashAnfitriao, $status]);
            
            return $pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log("Perfil::criar falhou: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica hierarquia simples (acesso aos perfis filhos na mesma árvore)
     * Neste sistema simplificado, assumimos que níveis superiores podem acessar
     * níveis inferiores se estiverem na mesma "cadeia", mas por enquanto
     * a lógica de acesso será mais restritiva ou baseada em parent_id do usuário,
     * não só do perfil. 
     * MANTENDO LÓGICA ANTIGA PARA COMPATIBILIDADE.
     */
    public static function podeAcessarPerfil($perfilSolicitanteId, $perfilAlvoId)
    {
        // Se for o mesmo perfil, OK
        if ($perfilSolicitanteId == $perfilAlvoId) {
            return true;
        }

        $solicitante = self::buscarPorId($perfilSolicitanteId);
        $alvo = self::buscarPorId($perfilAlvoId);

        if (!$solicitante || !$alvo) {
            return false;
        }

        // Admin acessa tudo
        if ($solicitante['papel_nivel'] === 'admin') {
            return true;
        }
        
        // Logica hierarquica poderia ser complexa (invites), mas aqui
        // vamos verificar se o Usuario "Pai" do dono do perfil alvo é o dono do perfil solicitante.
        // Isso conecta a tabela integra_usuarios (parent_id) aos perfis.
        
        $usuarioAlvo = Usuario::buscarPorId($alvo['id_usuario']);
        if ($usuarioAlvo && $usuarioAlvo['parent_id'] == $solicitante['id_usuario']) {
            return true;
        }

        return false;
    }

    public static function getPerfisDaArvore($perfilRaizId)
    {
        // Retorna IDs dos perfis abaixo deste na hierarquia
        // Baseado em invitation/parent_id.
        // Implementação simplificada:
        return [$perfilRaizId];
    }
}
