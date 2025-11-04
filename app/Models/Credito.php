<?php
namespace App\Model;

use App\Config\Database;

class Credito
{
    private static $table_saldo = 'creditos_saldo';
    private static $table_transacoes = 'creditos_transacoes';

    // Adiciona crédito a um usuário (ex: após pagamento)
    public static function adicionarCredito(int $usuarioId, float $valor, string $descricao = '', ?string $refExterna = null): bool
    {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            
            // Registra a transação
            $stmt = $db->prepare("INSERT INTO " . self::$table_transacoes . " (tipo, origem_id, destino_id, valor, descricao, referencia_externa) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['entrada', null, $usuarioId, $valor, $descricao, $refExterna]);

            // Atualiza ou insere saldo
            $stmt = $db->prepare("INSERT INTO " . self::$table_saldo . " (usuario_id, saldo) VALUES (?, ?) ON DUPLICATE KEY UPDATE saldo = saldo + VALUES(saldo)");
            $stmt->execute([$usuarioId, $valor]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Erro ao adicionar crédito: " . $e->getMessage());
            return false;
        }
    }

    // Transfere crédito de um usuário para outro (ex: assinante → vendedor)
    public static function transferirCredito(int $de, int $para, float $valor, string $descricao = ''): bool
    {
        if ($valor <= 0) return false;

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // Verifica saldo do remetente
            $saldo = self::getSaldo($de);
            if ($saldo < $valor) {
                $db->rollBack();
                return false;
            }

            // Registra transação
            $stmt = $db->prepare("INSERT INTO " . self::$table_transacoes . " (tipo, origem_id, destino_id, valor, descricao) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['transferencia', $de, $para, $valor, $descricao]);

            // Atualiza saldos
            $stmt = $db->prepare("UPDATE " . self::$table_saldo . " SET saldo = saldo - ? WHERE usuario_id = ?");
            $stmt->execute([$valor, $de]);

            $stmt = $db->prepare("INSERT INTO " . self::$table_saldo . " (usuario_id, saldo) VALUES (?, ?) ON DUPLICATE KEY UPDATE saldo = saldo + VALUES(saldo)");
            $stmt->execute([$para, $valor]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Erro na transferência de crédito: " . $e->getMessage());
            return false;
        }
    }

    // Expira crédito (assinante → plataforma)
    public static function expirarCredito(int $usuarioId, float $valor, string $descricao = ''): bool
    {
        if ($valor <= 0) return false;

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            $saldo = self::getSaldo($usuarioId);
            if ($saldo < $valor) {
                $db->rollBack();
                return false;
            }

            $stmt = $db->prepare("INSERT INTO " . self::$table_transacoes . " (tipo, origem_id, destino_id, valor, descricao) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['expiracao', $usuarioId, null, $valor, $descricao]);

            $stmt = $db->prepare("UPDATE " . self::$table_saldo . " SET saldo = saldo - ? WHERE usuario_id = ?");
            $stmt->execute([$valor, $usuarioId]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Erro ao expirar crédito: " . $e->getMessage());
            return false;
        }
    }

    // Obtém saldo atual (0 se não existir)
    public static function getSaldo(int $usuarioId): float
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT saldo FROM " . self::$table_saldo . " WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch();
        return $row ? (float)$row['saldo'] : 0.0;
    }

    // Extrato (últimas N transações)
    public static function getExtrato(int $usuarioId, int $limit = 50): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT * FROM " . self::$table_transacoes . "
            WHERE origem_id = ? OR destino_id = ?
            ORDER BY createdAt DESC
            LIMIT ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$usuarioId, $usuarioId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}