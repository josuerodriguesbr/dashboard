<?php
require_once __DIR__ . '/../config/autoload.php';

use App\Services\AsaasService;
use Config\Asaas;

// Habilitar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "--- Iniciando Teste de Criação de Cliente (Simulando Guzzle) ---\n";
echo "Endpoint: " . Asaas::BASE_URL . "/customers\n";
echo "API Key (Inicio): " . substr(Asaas::API_KEY, 0, 10) . "...\n\n";

try {
    $asaasService = new AsaasService();

    // Dados fornecidos pelo usuário no exemplo Guzzle
    $dadosExemplo = [
        "name" => "Administrador",
        "phone" => "85988259163"
    ];

    // Sanitizar/validar CPF antes de enviar
    $cpfRaw = "40847527387";
    $cpf = cleanCPF($cpfRaw);
    if ($cpf && isValidCPF($cpf)) {
        $dadosExemplo['cpfCnpj'] = $cpf;
    } else {
        echo "AVISO: CPF inválido ou ausente; não será enviado. Raw='{$cpfRaw}'\n";
    }

    echo "Enviando dados:\n";
    print_r($dadosExemplo);
    echo "\n...\n";

    $resultado = $asaasService->createCustomer($dadosExemplo);

    echo "\n--- Resposta do Asaas ---\n";
    print_r($resultado);

    if (isset($resultado['id'])) {
        echo "\nSUCESSO! Cliente criado com ID: " . $resultado['id'] . "\n";
    } else {
        echo "\nFALHA. Verifique os erros acima.\n";
    }

} catch (Exception $e) {
    echo "\nERRO CRÍTICO: " . $e->getMessage() . "\n";
}
