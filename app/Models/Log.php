<?php
namespace App\Models;
use Config\Database;

class Log
{
    public static function listar($limite = 50) {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT l.*, u.nome AS nomeUsuario 
                FROM logs l
                LEFT JOIN usuarios u ON u.id = l.usuarioId
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
        $pdo = Database::getConnection();
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP desconhecido';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Sem agente';

            $stmt = $pdo->prepare("
                INSERT INTO logs (usuarioId, acao, detalhes, ip, userAgent)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$usuarioId, $acao, $detalhes, $ip, $userAgent]);
        } catch (\Exception $e) {
            error_log("Log::registrar falhou: " . $e->getMessage());
            return false;
        }
    }
}