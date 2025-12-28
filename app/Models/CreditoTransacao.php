<?php
namespace App\Models;

use Config\Database;
use App\Models\Perfil;

class CreditoTransacao
{
    /**
     * Realiza uma transferência de créditos entre perfis
     */
    public static function transferirCreditos($origemPerfilId, $destinoPerfilId, $valor, $descricao = '', $referenciaExterna = null)
    {
        $pdo = Database::getConnection();
        
        try {
            // Validar os parâmetros
            if ($valor <= 0) {
                throw new \Exception('Valor da transferência deve ser maior que zero');
            }
            
            // Verificar se os perfis existem
            $perfilOrigem = Perfil::getPerfilPorId($origemPerfilId);
            $perfilDestino = Perfil::getPerfilPorId($destinoPerfilId);
            
            if (!$perfilOrigem) {
                throw new \Exception('Perfil de origem não encontrado');
            }
            
            if (!$perfilDestino) {
                throw new \Exception('Perfil de destino não encontrado');
            }
            
            // Verificar se os perfis pertencem à mesma árvore hierárquica
            if (!Perfil::pertencemMesmaArvore($origemPerfilId, $destinoPerfilId)) {
                throw new \Exception('Transferência não permitida - perfis não pertencem à mesma árvore hierárquica');
            }
            
            // Verificar se o perfil de origem tem saldo suficiente
            $saldoOrigem = self::getSaldoPerfil($origemPerfilId);
            if ($saldoOrigem < $valor) {
                throw new \Exception('Saldo insuficiente para a transferência');
            }
            
            // Iniciar transação
            $pdo->beginTransaction();
            
            // Atualizar saldos
            self::atualizarSaldo($origemPerfilId, $saldoOrigem - $valor);
            
            $saldoDestino = self::getSaldoPerfil($destinoPerfilId);
            self::atualizarSaldo($destinoPerfilId, $saldoDestino + $valor);
            
            // Registrar a transação
            $stmt = $pdo->prepare("
                INSERT INTO integra_transacoes 
                (tipo, origem_perfil_id, destino_perfil_id, valor, descricao, referencia_externa, status, createdAt) 
                VALUES 
                ('transferencia', ?, ?, ?, ?, ?, 'confirmado', NOW())
            ");
            
            $stmt->execute([
                $origemPerfilId, 
                $destinoPerfilId, 
                $valor, 
                $descricao, 
                $referenciaExterna
            ]);
            
            $transacaoId = $pdo->lastInsertId();
            
            // Confirmar transação
            $pdo->commit();
            
            return [
                'success' => true,
                'transacao_id' => $transacaoId,
                'mensagem' => 'Transferência realizada com sucesso'
            ];
            
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            if ($pdo->inTransaction()) {
                $pdo->rollback();
            }
            
            error_log("CreditoTransacao::transferirCreditos falhou: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém o saldo de um perfil
     */
    public static function getSaldoPerfil($perfilId)
    {
        $pdo = Database::getConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT creditos 
                FROM integra_perfis 
                WHERE id = ?
            ");
            $stmt->execute([$perfilId]);
            $result = $stmt->fetch();
            
            return $result ? (float)$result['creditos'] : 0.00;
        } catch (\Exception $e) {
            error_log("CreditoTransacao::getSaldoPerfil falhou: " . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Atualiza o saldo de um perfil
     */
    public static function atualizarSaldo($perfilId, $novoSaldo)
    {
        $pdo = Database::getConnection();
        
        try {
            // Atualizar diretamente a coluna creditos na tabela integra_perfis
            $stmt = $pdo->prepare("
                UPDATE integra_perfis 
                SET creditos = ?, updatedAt = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$novoSaldo, $perfilId]);
            
            // Verificar se a atualização afetou alguma linha
            if ($stmt->rowCount() === 0) {
                throw new \Exception("Perfil ID {$perfilId} não encontrado");
            }
        } catch (\Exception $e) {
            error_log("CreditoTransacao::atualizarSaldo falhou: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adiciona créditos a um perfil (entrada)
     */
    public static function adicionarCreditos($perfilId, $valor, $descricao = '', $referenciaExterna = null, $tipo = 'entrada')
    {
        $pdo = Database::getConnection();
        
        try {
            if ($valor <= 0) {
                throw new \Exception('Valor deve ser maior que zero');
            }
            
            $pdo->beginTransaction();
            
            $saldoAtual = self::getSaldoPerfil($perfilId);
            $novoSaldo = $saldoAtual + $valor;
            
            self::atualizarSaldo($perfilId, $novoSaldo);
            
            $stmt = $pdo->prepare("
                INSERT INTO integra_transacoes 
                (tipo, origem_perfil_id, destino_perfil_id, valor, descricao, referencia_externa, status, createdAt) 
                VALUES 
                (?, NULL, ?, ?, ?, ?, 'confirmado', NOW())
            ");
            
            $stmt->execute([
                $tipo, 
                $perfilId, 
                $valor, 
                $descricao, 
                $referenciaExterna
            ]);
            
            $transacaoId = $pdo->lastInsertId();
            $pdo->commit();
            
            return [
                'success' => true,
                'transacao_id' => $transacaoId,
                'mensagem' => 'Créditos adicionados com sucesso'
            ];
            
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollback();
            }
            
            error_log("CreditoTransacao::adicionarCreditos falhou: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove créditos de um perfil (saída)
     */
    public static function removerCreditos($perfilId, $valor, $descricao = '', $referenciaExterna = null, $tipo = 'saida')
    {
        $pdo = Database::getConnection();
        
        try {
            if ($valor <= 0) {
                throw new \Exception('Valor deve ser maior que zero');
            }
            
            $saldoAtual = self::getSaldoPerfil($perfilId);
            if ($saldoAtual < $valor) {
                throw new \Exception('Saldo insuficiente');
            }
            
            $pdo->beginTransaction();
            
            $novoSaldo = $saldoAtual - $valor;
            self::atualizarSaldo($perfilId, $novoSaldo);
            
            $stmt = $pdo->prepare("
                INSERT INTO integra_transacoes 
                (tipo, origem_perfil_id, destino_perfil_id, valor, descricao, referencia_externa, status, createdAt) 
                VALUES 
                (?, ?, NULL, ?, ?, ?, 'confirmado', NOW())
            ");
            
            $stmt->execute([
                $tipo, 
                $perfilId, 
                $valor, 
                $descricao, 
                $referenciaExterna
            ]);
            
            $transacaoId = $pdo->lastInsertId();
            $pdo->commit();
            
            return [
                'success' => true,
                'transacao_id' => $transacaoId,
                'mensagem' => 'Créditos removidos com sucesso'
            ];
            
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollback();
            }
            
            error_log("CreditoTransacao::removerCreditos falhou: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém o histórico de transações de um perfil
     */
    public static function getHistoricoTransacoes($perfilId, $limite = 50)
    {
        $pdo = Database::getConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT t.*, 
                       po.nome as nome_origem, po.email as email_origem,
                       pd.nome as nome_destino, pd.email as email_destino
                FROM integra_transacoes t
                LEFT JOIN integra_perfis p_origem ON t.origem_perfil_id = p_origem.id
                LEFT JOIN integra_usuarios po ON p_origem.id_usuario = po.id
                LEFT JOIN integra_perfis p_destino ON t.destino_perfil_id = p_destino.id
                LEFT JOIN integra_usuarios pd ON p_destino.id_usuario = pd.id
                WHERE t.origem_perfil_id = ? OR t.destino_perfil_id = ?
                ORDER BY t.createdAt DESC
                LIMIT ?
            ");
            $stmt->execute([$perfilId, $perfilId, $limite]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("CreditoTransacao::getHistoricoTransacoes falhou: " . $e->getMessage());
            return [];
        }
    }
}