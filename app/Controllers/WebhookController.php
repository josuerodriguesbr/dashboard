<?php
namespace App\Controllers;

use App\Models\Log;

class WebhookController
{
    public function handleAsaas()
    {
        // 1. Capturar input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Log para debug
        Log::registrar(null, 'Webhook Recebido', "Evento: " . ($data['event'] ?? 'n/a'));
        error_log("[Webhook] Payload: " . $input);

        // 2. Verificar Evento
        $evento = $data['event'] ?? '';
        if ($evento !== 'PAYMENT_RECEIVED') {
            // Se não for pagamento recebido, só ignoramos (200 OK para o Asaas não ficar tentando de novo)
            http_response_code(200);
            echo json_encode(['status' => 'ignored', 'reason' => 'Not a payment received event']);
            return;
        }

        $payment = $data['payment'] ?? null;
        if (!$payment || !isset($payment['customer'])) {
            http_response_code(400); // Bad Request se payload for inválido
            echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
            return;
        }

        $asaasId = $payment['customer'];
        $valor = $payment['value'] ?? 0;
        $pagamentoIdExterno = $payment['id']; // ID do pagamento no Asaas

        try {
            $pdo = \Config\Database::getConnection();
            
            // 3. Identificar Usuário pelo asaas_id
            // Precisamos buscar quem é o dono desse ID
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE asaas_id = ?");
            $stmt->execute([$asaasId]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                error_log("[Webhook] Usuário não encontrado para asaas_id: $asaasId");
                // Retornamos 200 porque erro de negócio não deve gerar retry do webhook
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
                return;
            }

            $usuarioId = $usuario['id'];

            // 4. Idempotência: Verificar se já processamos essa transação
            // Vamos checar se existe alguma recarga com esse ID de transação externo? 
            // Como ainda não temos campo 'external_id' na credito_transacoes, vamos checar pelo log ou criar tabela de pagamentos processados.
            // Para simplificar, vamos verificar se já existe transação recente com mesmo valor e descrição, OU idealmente adicionar coluna.
            // MELHOR: Vamos usar a tabela `pagamentos` se ela estiver sendo usada, ou verificar logs.
            // Dado o schema atual, vamos assumir que se não checarmos, o usuário ganha crédito duplo.
            // Vamos checar na tabela de transações se tem alguma com a descrição contendo o ID do pagamento.
            
            $descricaoBusca = "%$pagamentoIdExterno%";
            $stmtCheck = $pdo->prepare("SELECT id FROM credito_transacoes WHERE descricao LIKE ? AND usuario_id = ?");
            $stmtCheck->execute(["Recarga via Pix (Asaas: $pagamentoIdExterno)", $usuarioId]);
            if ($stmtCheck->fetch()) {
                error_log("[Webhook] Pagamento $pagamentoIdExterno já processado para usuário $usuarioId");
                echo json_encode(['status' => 'success', 'message' => 'Already processed']);
                return;
            }

            // 5. Adicionar Créditos
            // Tipo 1 = Compra (Multiplicador 1)
            // OBS: O método adicionarTransacao/Criar JÁ atualiza o saldo da carteira automaticamente.
            \App\Models\Usuario::adicionarTransacao(
                $usuarioId,
                1,
                $valor, // Valor positivo
                "Recarga via Pix (Asaas: $pagamentoIdExterno)"
            );
            
            // Log::registrar was here, keeping it clean below


            Log::registrar($usuarioId, 'Recarga Webhook', "Créditos adicionados: $valor (Ref: $pagamentoIdExterno)");

            echo json_encode(['status' => 'success', 'new_balance' => \App\Models\Usuario::getSaldo($usuarioId)]);

        } catch (\Exception $e) {
            error_log("[Webhook] Erro ao processar: " . $e->getMessage());
            http_response_code(500); // Erro do servidor, Asaas pode tentar de novo
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}