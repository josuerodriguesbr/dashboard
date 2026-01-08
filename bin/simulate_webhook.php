<?php
// bin/simulate_webhook.php

// URL do webhook local
$url = 'http://localhost/projetos/dashboard/webhook/asaas';

// Configurações do teste
$asaasId = 'cus_000007406314'; // ID do usuário Josué (obtido no inspect anterior)
$valor = 50.00;
$paymentId = 'pay_' . bin2hex(random_bytes(8)); // ID aleatório para ser único

echo "Simulando Webhook Asaas...\n";
echo "Cliente: $asaasId\n";
echo "Valor: R$ $valor\n";
echo "Payment ID: $paymentId\n";
echo "URL: $url\n\n";

// Payload (Corpo da requisição)
$data = [
    "event" => "PAYMENT_RECEIVED",
    "payment" => [
        "id" => $paymentId,
        "customer" => $asaasId,
        "value" => $valor,
        "netValue" => $valor,
        "billingType" => "PIX",
        "status" => "RECEIVED"
    ]
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$headers = $http_response_header;

// Exibir resposta
echo "--- Resposta do Servidor ---\n";
echo "Headers: " . $headers[0] . "\n";
echo "Body: " . $result . "\n";

// Extra: Verificar saldo novo e logs se possível via outro script ou mensagem
echo "\nVerifique no painel se os créditos entraram!\n";
