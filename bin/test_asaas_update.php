<?php
require_once __DIR__ . '/../config/autoload.php';

use App\Services\AsaasService;
use App\Models\Usuario;
use Config\Database;

// Habilitar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "--- Iniciando Diagnóstico Asaas ---\n";

try {
    $pdo = Database::getConnection();
    
    // 1. Buscar usuário Carlos
    echo "1. Buscando usuário Carlos...\n";
    $email = 'carlos@gmail.com';
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("ERRO: Usuário {$email} não encontrado no banco local.\n");
    }

    echo "   Usuário encontrado: {$usuario['nome']} (ID: {$usuario['id']})\n";
    echo "   CPF no Banco: " . ($usuario['cpf'] ?? 'VAZIO') . "\n";
    echo "   Asaas ID no Banco: " . ($usuario['asaas_id'] ?? 'VAZIO') . "\n\n";

    if (empty($usuario['asaas_id'])) {
        die("ERRO: Usuário não tem Asaas ID. Execute o fluxo normal primeiro.\n");
    }

    // 2. Instanciar Serviço
    $asaasService = new AsaasService();

    // 3. Testar Atualização com NOVO CPF Válido (Raw Numbers)
    // 3. Testar Atualização com NOVO CPF Válido (Tentativa 2)
    $cpfTeste = '35928876044';
    $cpfTesteLimpo = cleanCPF($cpfTeste);
    $dadosAtualizacao = [];
    if ($cpfTesteLimpo && isValidCPF($cpfTesteLimpo)) {
        $dadosAtualizacao['cpfCnpj'] = $cpfTesteLimpo;
    } else {
        echo "AVISO: CPF de teste inválido: {$cpfTeste}\n";
    }

    echo "2. Tentando atualizar cliente no Asaas...\n";
    echo "   ID Asaas: {$usuario['asaas_id']}\n";
    echo "   Dados enviados: " . json_encode($dadosAtualizacao) . "\n";

    /*
    $resultado = $asaasService->updateCustomer($usuario['asaas_id'], $dadosAtualizacao);
    */

    echo "2. TESTE DE CRIAÇÃO (Novo Cliente COM CPF + TIPO)...\n";
    $cpfNovo = '52998224097';
    $cpfNovoLimpo = cleanCPF($cpfNovo);
    $dadosNovo = [
        'name' => 'Teste Diagnostico Tipo',
        'email' => 'teste_diag_tipo_' . time() . '@email.com',
        'personType' => 'FISICA'
    ];
    if ($cpfNovoLimpo && isValidCPF($cpfNovoLimpo)) {
        $dadosNovo['cpfCnpj'] = $cpfNovoLimpo;
    } else {
        echo "AVISO: CPF novo inválido: {$cpfNovo}\n";
    }
    echo "   Dados enviados: " . json_encode($dadosNovo) . "\n";
    $resultado = $asaasService->createCustomer($dadosNovo);
    
    echo "\n3. Resposta do Asaas (Raw):\n";
    print_r($resultado);
    exit;

    echo "\n--- Diagnóstico Concluído ---\n";

} catch (Exception $e) {
    echo "\nERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
