<?php
namespace App\Models;

use Config\Database;
use PDO;

class CreditoTransacao
{
    /**
     * Cria uma nova transação de crédito e atualiza o saldo do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @param int $tipoId ID do tipo de transação (1=Recarga, 2=Consumo, etc)
     * @param int $valorNominal Valor da transação (positivo para crédito, negativo para débito)
     * @param string $descricao Descrição da transação
     * @param int|null $recargaId ID da recarga relacionada (opcional)
     * @return int ID da transação criada
     */
    public static function criar($usuarioId, $tipoId, $valorNominal, $descricao, $recargaId = null)
    {
        $pdo = Database::getConnection();
        
        try {
            $pdo->beginTransaction();

            // 1. Atualizar Carteira
            $carteiraModel = new Carteira($pdo);
            // $valorNominal já deve vir com sinal correto (negativo para consumo)
            // Mas dependendo da regra, o model de Carteira soma o que vier.
            
            // SE a lógica anterior usava multiplicador do TIPO, precisamos manter isso?
            // O novo schema tem `cred_trans_tipos.multiplicador`.
            // Vamos buscar o multiplicador para garantir.
            
            $stmtTipo = $pdo->prepare("SELECT multiplicador FROM cred_trans_tipos WHERE id = ?");
            $stmtTipo->execute([$tipoId]);
            $tipo = $stmtTipo->fetch(PDO::FETCH_ASSOC);
            $multiplicador = $tipo ? $tipo['multiplicador'] : 1;
            
            // O valor final a impactar no saldo
            $impactoSaldo = $valorNominal * $multiplicador;
            
            // Atualiza saldo e pega o novo saldo para registrar
            $carteiraModel->updateSaldo($usuarioId, $impactoSaldo);
            $novaCarteira = $carteiraModel->getByUserId($usuarioId);
            $saldoApos = $novaCarteira['saldo_atual'];
            
            // 2. Registrar Transação
            $stmt = $pdo->prepare("
                INSERT INTO credito_transacoes 
                (usuario_id, tipo_id, valor_nominal, saldo_apos, descricao, recarga_id, createdAt) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $usuarioId,
                $tipoId,
                $valorNominal, // Registramos o valor nominal da operação
                $saldoApos,
                $descricao,
                $recargaId
            ]);
            
            $transacaoId = $pdo->lastInsertId();
            
            $pdo->commit();
            return $transacaoId;
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getHistorico($usuarioId, $limite = 50)
    {
        $pdo = Database::getConnection();
        
        $stmt = $pdo->prepare("
            SELECT t.*, tp.nome as tipo_nome, tp.multiplicador
            FROM credito_transacoes t
            JOIN cred_trans_tipos tp ON t.tipo_id = tp.id
            WHERE t.usuario_id = ?
            ORDER BY t.createdAt DESC
            LIMIT ?
        ");
        
        $stmt->execute([$usuarioId, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}