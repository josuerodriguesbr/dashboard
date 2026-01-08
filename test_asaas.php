<?php
require_once 'app/Config/Asaas.php';

use Config\Asaas;

echo "Testando Conexão Asaas...\n";

$url = Asaas::BASE_URL . '/customers?limit=1';
$headers = Asaas::getHeaders();

echo "URL: $url\n";
echo "Headers: " . print_r($headers, true) . "\n";

$curl = curl_init();

$options = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => false, // Desabilitar SSL para teste (XAMPP costuma falhar)
    CURLOPT_SSL_VERIFYHOST => false
];

curl_setopt_array($curl, $options);

$response = curl_exec($curl);
$err = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

if ($err) {
    echo "cURL Error #: " . $err . "\n";
} else {
    echo "HTTP Code: " . $httpCode . "\n";
    echo "Response: " . $response . "\n";
}
