<?php
namespace App\Models;

class Log
{
    public static function listar($limite = 50) {
        $pdo = \App\Config\Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT l.*, u.nome AS nomeUsuario 
                FROM integra_logs l
                LEFT JOIN integra_usuarios u ON u.id = l.usuarioId
                ORDER BY l.createdAt DESC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Log::listar falhou: " . $e->getMessage());
            return [];
        }
    }

    public static function registrar($usuarioId, $acao, $detalhes = '') {
        $pdo = \App\Config\Database::getConnection();
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP desconhecido';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Sem agente';

            $stmt = $pdo->prepare("
                INSERT INTO integra_logs (usuarioId, acao, detalhes, ip, userAgent)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$usuarioId, $acao, $detalhes, $ip, $userAgent]);
        } catch (\Exception $e) {
            error_log("Log::registrar falhou: " . $e->getMessage());
            return false;
        }
    }
}