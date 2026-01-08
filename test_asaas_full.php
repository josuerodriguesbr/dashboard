<?php
// Simula o ambiente da aplicação para testar o Controller
require_once 'config/autoload.php';

// Mock de Cookies e Input
$_COOKIE['authToken'] = '...'; // Não vamos conseguir validar token real aqui facilmente sem login

// Utilizaremos diretamente o AsaasService para isolar a integração
use App\Services\AsaasService;

echo "--- Teste Integrado Asaas Service ---\n";

$service = new AsaasService();

// 1. Criar/Buscar Cliente
$email = 'mirela@gmail.com'; 
echo "1. Buscando cliente por email ($email)...\n";
$customer = $service->getCustomerByEmail($email);

if (!$customer) {
    echo "Cliente não encontrado. Criando...\n";
    $dadosCliente = [
        'name' => 'Mirela Teste',
        'email' => $email,
        'mobilePhone' => '0000000000'
    ];
    $cpfRaw = '00000000000';
    $cpf = cleanCPF($cpfRaw);
    if ($cpf && isValidCPF($cpf)) {
        $dadosCliente['cpfCnpj'] = $cpf;
    } else {
        echo "AVISO: CPF fictício não enviado (inválido): {$cpfRaw}\n";
    }
    $customer = $service->createCustomer($dadosCliente);
}

if (!isset($customer['id'])) {
    echo "ERRO CRÍTICO: Falha ao obter ID do cliente.\n";
    print_r($customer);
    exit(1);
}

echo "Cliente ID: " . $customer['id'] . "\n";

// 2. Criar Cobrança
echo "2. Criando cobrança de R$ 5,00...\n";
$paymentData = [
    'customer' => $customer['id'],
    'billingType' => 'PIX',
    'value' => 5.00,
    'dueDate' => date('Y-m-d'),
    'description' => 'Teste CLI'
];

$payment = $service->createPayment($paymentData);

if (!isset($payment['id'])) {
    echo "ERRO CRÍTICO: Falha ao criar cobrança.\n";
    print_r($payment);
    exit(1);
}

echo "Cobrança ID: " . $payment['id'] . "\n";

// 3. Obter QR Code
echo "3. Obtendo QR Code...\n";
$qrCode = $service->getPixQrCode($payment['id']);

if (!isset($qrCode['encodedImage'])) {
    echo "ERRO CRÍTICO: Falha ao obter QR Code.\n";
    print_r($qrCode);
    exit(1);
}

echo "SUCESSO! QR Code (primeiros 50 chars): " . substr($qrCode['encodedImage'], 0, 50) . "...\n";
