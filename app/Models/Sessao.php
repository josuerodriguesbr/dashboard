<?php
// app/Models/Sessao.php
namespace App\Models;

class Sessao
{
    private static function getConnection()
    {
        return \App\Config\Database::getConnection();
    }

    public static function criar($usuarioId, $token, $expiresInHours = 24)
    {
        $pdo = self::getConnection();
        
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + ($expiresInHours * 3600));
            
            $stmt = $pdo->prepare("
                INSERT INTO integra_sessoes (usuarioId, token, expiresAt, isActive)
                VALUES (?, ?, ?, 1)
            ");
            
            $stmt->execute([$usuarioId, $token, $expiresAt]);
            return $pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log("Sessao::criar falhou: " . $e->getMessage());
            return false;
        }
    }
    
    public static function buscarPorToken($token)
    {
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT s.*, u.nome as usuarioNome, u.nivel as usuarioNivel, u.email as usuarioEmail
                FROM integra_sessoes s
                JOIN integra_usuarios u ON s.usuarioId = u.id
                WHERE s.token = ? AND s.isActive = 1 AND s.expiresAt > NOW()
            ");
            
            $stmt->execute([$token]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Sessao::buscarPorToken falhou: " . $e->getMessage());
            return false;
        }
    }
    
    public static function desativar($tokenId)
    {
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare("UPDATE integra_sessoes SET isActive = 0 WHERE id = ?");
            return $stmt->execute([$tokenId]);
        } catch (\Exception $e) {
            error_log("Sessao::desativar falhou: " . $e->getMessage());
            return false;
        }
    }
    
    public static function desativarPorToken($token)
    {
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare("UPDATE integra_sessoes SET isActive = 0 WHERE token = ?");
            return $stmt->execute([$token]);
        } catch (\Exception $e) {
            error_log("Sessao::desativarPorToken falhou: " . $e->getMessage());
            return false;
        }
    }
}