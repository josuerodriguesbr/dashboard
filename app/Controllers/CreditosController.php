<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Recurso;
use App\Models\ItemAdquirido;
use App\Models\CreditoTransacao;
use App\Utils\UserContext;

class CreditosController
{
    /**
     * Retorna o saldo em JSON para widgets via AJAX
     */
    public static function getSaldo()
    {
        try {
            // Verificar autenticação
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => 'Não autenticado: ' . $e->getMessage()], 401);
            return;
        }

        $usuario = UserContext::getUsuario();
        if (!$usuario) {
             json_response(['success' => false, 'message' => 'Não autenticado'], 401);
             return;
        }

        // Evitar cache do navegador
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        $saldo = Usuario::getSaldo($usuario['id']);
        json_response(['success' => true, 'saldo' => $saldo]);
    }

    /**
     * Dashboard de Recursos/Créditos do Usuário
     */
    public static function index()
    {
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            redirect('/'); // Redirecionar para a raiz (login)
            return;
        }

        $usuario = UserContext::getUsuario();
        if (!$usuario) {
            redirect('/');
            return;
        }

        $saldo = Usuario::getSaldo($usuario['id']);
        
        // Itens adquiridos (antigos Combos Ativos)
        $itemAdquiridoModel = new ItemAdquirido(\Config\Database::getConnection());
        $itensAdquiridos = $itemAdquiridoModel->getByUsuario($usuario['id']);
        
        $ultimasTransacoes = CreditoTransacao::getHistorico($usuario['id'], 5);
        
        // Recursos disponíveis para compra (antigos Combos Disponíveis)
        $recursoModel = new Recurso(\Config\Database::getConnection());
        $recursosDisponiveis = $recursoModel->getAll();

        view('creditos/index', [
            'saldo' => $saldo,
            'combosAtivos' => $itensAdquiridos,
            'ultimasTransacoes' => $ultimasTransacoes,
            'combosDisponiveis' => $recursosDisponiveis,
            'active_tab' => 'creditos'
        ]);
    }

    /**
     * Processa a compra de um Recurso
     */
    public static function comprarRecurso($recursoId)
    {
        $usuario = UserContext::getUsuario();
        if (!$usuario) {
            json_response(['success' => false, 'message' => 'Não autenticado'], 401);
            return;
        }

        try {
            $recursoId = (int)$recursoId;
            $recursoModel = new Recurso(\Config\Database::getConnection());
            $recurso = $recursoModel->getById($recursoId);

            if (!$recurso) {
                json_response(['success' => false, 'message' => 'Recurso inválido'], 400);
                return;
            }

            $custoCreditos = (int)$recurso['preco_creditos'];
            $saldoAtual = Usuario::getSaldo($usuario['id']);

            if ($saldoAtual < $custoCreditos) {
                json_response(['success' => false, 'message' => 'Saldo insuficiente em créditos.'], 400);
                return;
            }

            // Realizar transação de débito (Tipo 2 = Consumo, por exemplo)
            // Assumindo que tipo 2 é consumo. Precisamos garantir que existe na tabela de tipos.
            // Se não, criar.
            
            // O valor é negativo para o saldo, mas positivo no registro se o multiplicador for -1.
            // Vamos assumir multiplicador -1 para Consumo.
            
            Usuario::adicionarTransacao(
                $usuario['id'],
                2, // ID do tipo Consumo
                -$custoCreditos, // Passando negativo para garantir subtração se o model Usuario/Carteira somar direto
                "Compra de Recurso: " . $recurso['nome']
            );
            
            // Adicionar Item Adquirido
            $itemAdquiridoModel = new ItemAdquirido(\Config\Database::getConnection());
            $itemAdquiridoModel->create(
                $usuario['id'], 
                $recurso['id'], 
                ($recurso['tipo_cobranca'] == 'unidade') ? 1 : 0 // Quantidade inicial, lógica a definir
                // Se for tempo, data_expiracao seria calculada aqui.
            );

            $novoSaldo = Usuario::getSaldo($usuario['id']);
            json_response(['success' => true, 'message' => 'Recurso adquirido com sucesso!', 'novo_saldo' => $novoSaldo]);

        } catch (\Exception $e) {
            error_log("Erro ao comprar recurso: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Erro interno ao processar compra.'], 500);
        }
    }

    public static function historico()
    {
        $usuario = UserContext::getUsuario();
        if (!$usuario) {
            redirect('/login');
            return;
        }

        $transacoes = CreditoTransacao::getHistorico($usuario['id'], 100);
        
        view('creditos/historico', [
            'transacoes' => $transacoes,
            'active_tab' => 'creditos'
        ]);
    }
    /**
     * Cria uma cobrança Pix para recarga de créditos
     */
    public static function criarCobranca()
    {
        // 0. Prevenir poluição do JSON com erros PHP (HTML)
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', 0); // Desabilita erros na tela temporamente
        ob_start(); // Inicia buffer para capturar qualquer saída indesejada

        // Debug Log (que vai para o arquivo de log, não para a tela)
        error_log("[Asaas] Iniciando criarCobranca...");

        try {
            // 1. Autenticação
            try {
                \App\Middleware\AuthMiddleware::verificar();
            } catch (\Exception $e) {
                // Limpa buffer e retorna 401
                ob_clean();
                json_response(['success' => false, 'message' => 'Não autenticado'], 401);
                return;
            }

            $usuario = UserContext::getUsuario();
            
            // Recarregar dados do banco para garantir que temos CPF e Telefone (que não estão no token JWT)
            $usuarioFull = Usuario::buscarPorId($usuario['id']);
            if ($usuarioFull) {
                // Merge para manter propriedades do contexto se houver conflito, mas dar preferência ao DB para dados cadastrais
                $usuario = array_merge($usuario, $usuarioFull);
            }

            error_log("[Asaas] Usuário autenticado: " . ($usuario['id'] ?? 'N/A'));

            // Input
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $valor = $input['valor'] ?? 0;

            error_log("[Asaas] Valor solicitado: $valor");

            if ($valor < 5) {
                ob_clean(); // Garante limpeza
                json_response(['success' => false, 'message' => 'Valor mínimo de R$ 5,00'], 400);
                return;
            }

            $asaasService = new \App\Services\AsaasService();
            
            // 2. Verificar/Criar Cliente no Asaas
            $asaasId = $usuario['asaas_id'] ?? null;
            error_log("[Asaas] Asaas ID atual: " . ($asaasId ?? 'Nenhum'));
            
            if (!$asaasId) {
                // Tentar buscar por email
                try {
                    error_log("[Asaas] Buscando cliente por email: " . $usuario['email']);
                    $clienteExistente = $asaasService->getCustomerByEmail($usuario['email']);
                } catch (\Exception $e) {
                    error_log("[Asaas] Erro ao buscar usuario: " . $e->getMessage());
                    $clienteExistente = null;
                }
                
                if ($clienteExistente) {
                    $asaasId = $clienteExistente['id'];
                    error_log("[Asaas] Cliente encontrado: " . $asaasId);
                } else {
                    error_log("[Asaas] Criando novo cliente...");
                    // Criar novo cliente
                    $dadosCliente = [
                        'name' => $usuario['nome'],
                        'email' => $usuario['email'],
                        'mobilePhone' => $usuario['telefone'] ?? ''
                    ];

                    // Limpa e valida o CPF antes de enviar — enviar somente a sequência de dígitos
                    $cpfLimpoCriacao = self::cleanCPF($usuario['cpf'] ?? '');
                    if ($cpfLimpoCriacao && self::isValidCPF($cpfLimpoCriacao)) {
                        $dadosCliente['cpfCnpj'] = $cpfLimpoCriacao;
                    } else {
                        error_log("[Asaas] CPF não será enviado na criação (inválido ou ausente): '" . ($usuario['cpf'] ?? '') . "'");
                    }
                    
                    $novoCliente = $asaasService->createCustomer($dadosCliente);
                    
                    if (isset($novoCliente['id'])) {
                        $asaasId = $novoCliente['id'];
                        error_log("[Asaas] Novo cliente criado: " . $asaasId);
                    } else {
                        // Se falhar, captura o erro detalhado
                        $erroMsg = json_encode($novoCliente);
                        
                        // Verificar erros comuns (ex: CPF inválido)
                        $msgUsuario = 'Erro ao criar cadastro no sistema de pagamento.';
                        if (isset($novoCliente['errors'])) {
                             $firstError = $novoCliente['errors'][0]['description'] ?? 'Dados inválidos';
                             $msgUsuario .= " " . $firstError;
                        }

                        error_log("[Asaas] Erro ao criar cliente: " . $erroMsg);
                        throw new \Exception($msgUsuario);
                    }
                }
                
                // Salvar asaas_id no banco
                Usuario::atualizarAsaasId($usuario['id'], $asaasId);
            }

            // FORÇAR ATUALIZAÇÃO DO CADASTRO (CPF)
            // Se o cliente já existia (pegou do banco ou buscou por email), pode estar sem CPF no Asaas.
            // Vamos forçar a atualização agora.
            if ($asaasId) {
                try {
                    error_log("[Asaas] Forçando atualização do cadastro (CPF)...");
                    $cpfLimpo = self::cleanCPF($usuario['cpf'] ?? '');
                    
                    // Validação adicional do CPF
                    if (!self::isValidCPF($cpfLimpo)) {
                        throw new \Exception("CPF inválido: " . $cpfLimpo);
                    }
                    
                    $dadosAtualizacao = [
                         'cpfCnpj' => $cpfLimpo,
                         'mobilePhone' => $usuario['telefone'] ?? ''
                    ];
                    
                    $updateResult = $asaasService->updateCustomer($asaasId, $dadosAtualizacao);
                    error_log("[Asaas] Resultado update: " . json_encode($updateResult));

                    // VERIFICAÇÃO DE ERRO NO UPDATE
                    if (isset($updateResult['errors'])) {
                         $firstError = $updateResult['errors'][0]['description'] ?? 'Erro desconhecido ao atualizar';
                         // Se der erro ao atualizar CPF, VAMOS PARAR TUDO e mostrar pro usuário
                         // Senão, o Pix vai falhar lá na frente dizendo "falta cpf"
                         throw new \Exception("Asaas Rejeitou Dados: " . $firstError);
                    }
                    
                } catch (\Throwable $ex) {
                    error_log("[Asaas] FALHA AO ATUALIZAR CLIENTE: " . $ex->getMessage());
                    // AGORA VAMOS REPASSAR O ERRO PARA TELA (Pedido do usuário)
                    throw new \Exception("Erro Update Asaas: " . $ex->getMessage());
                }
            }

            // 3. Criar Cobrança
            error_log("[Asaas] Gerando cobrança Pix...");
            $dadosCobranca = [
                'customer' => $asaasId,
                'billingType' => 'PIX',
                'value' => $valor,
                'dueDate' => date('Y-m-d'),
                'description' => 'Recarga de Créditos - Dashboard'
            ];

            $cobranca = $asaasService->createPayment($dadosCobranca);

            if (isset($cobranca['id'])) {
                error_log("[Asaas] Cobrança criada: " . $cobranca['id']);
                
                // Obter QR Code
                $qrCodeData = $asaasService->getPixQrCode($cobranca['id']);
                
                // Limpa qualquer output que tenha ocorrido incidentalmente
                while (ob_get_level()) { ob_end_clean(); }
                ini_set('display_errors', $displayErrors); // Restaura config original

                json_response([
                    'success' => true,
                    'paymentId' => $cobranca['id'],
                    'pixQrCode' => $qrCodeData['encodedImage'],
                    'pixCopyPaste' => $qrCodeData['payload'],
                    'valor' => $valor
                ]);
            } else {
                $erroMsg = json_encode($cobranca);
                error_log("[Asaas] Erro resposta cobrança: " . $erroMsg);
                
                // Tenta extrair a mensagem de erro específica do Asaas
                $msgErroAsaas = 'Erro deconhecido no gateway de pagamento.';
                
                if (is_array($cobranca)) {
                    if (isset($cobranca['errors']) && is_array($cobranca['errors'])) {
                         $erros = [];
                         foreach($cobranca['errors'] as $err) {
                             $erros[] = $err['description'] ?? 'Erro sem descrição';
                         }
                         $msgErroAsaas = implode('; ', $erros);
                    } else if (isset($cobranca['error'])) {
                        $msgErroAsaas = $cobranca['error'];
                    } else {
                        // Caso seja um array mas sem chaves de erro conhecidas, mostramos tudo
                        $msgErroAsaas = json_encode($cobranca);
                    }
                } else if (is_null($cobranca)) {
                    $msgErroAsaas = 'Resposta nula/vazia do Asaas (json_decode falhou?)';
                } else {
                    $msgErroAsaas = (string)$cobranca;
                }

                throw new \Exception('Debug Asaas: ' . $msgErroAsaas);
            }

        } catch (\Throwable $e) {
            error_log("[Asaas] EXCEÇÃO CRÍTICA: " . $e->getMessage());
            error_log("[Asaas] Trace: " . $e->getTraceAsString());
            
            // Limpa o buffer de saída COMPLETAMENTE para garantir que só o JSON saia
            while (ob_get_level()) { ob_end_clean(); }
            
            ini_set('display_errors', $displayErrors); // Restaura config original
            
            json_response([
                'success' => false, 
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove caracteres não numéricos do CPF e valida se tem 11 dígitos
     */
    private static function cleanCPF($cpf)
    {
        // Verifica se o CPF é nulo ou vazio
        if (!$cpf) {
            return null;
        }

        // Remove todos os caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se tem exatamente 11 dígitos após limpeza
        if (strlen($cpf) !== 11) {
            error_log("[Asaas] CPF com número incorreto de dígitos: '$cpf'");
            return null;
        }

        return $cpf;
    }
    
    /**
     * Valida se o CPF é válido (formato correto e dígitos verificadores)
     */
    private static function isValidCPF($cpf)
    {
        // Se o CPF for nulo ou vazio após limpeza, considera inválido
        if (!$cpf) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        // Calcula e verifica os dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}