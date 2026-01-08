<?php
namespace App\Services;

use Config\Asaas;

class AsaasService
{
    /**
     * Cria um cliente no Asaas
     */
    public function createCustomer(array $customerData)
    {
        return $this->request('/customers', 'POST', $customerData);
    }

    /**
     * Atualiza dados de um cliente no Asaas
     */
    public function updateCustomer($id, array $customerData)
    {
        return $this->request("/customers/{$id}", 'PUT', $customerData); // Asaas usa POST ou PUT, mas doc as vezes pede POST para updates parciais, vamos testar. Na v3 geralmente é POST ou PUT.
    }

    /**
     * Busca um cliente por Email
     */
    public function getCustomerByEmail($email)
    {
        // O Asaas permite buscar por email via filtro
        $response = $this->request('/customers?email=' . urlencode($email), 'GET');
        if (isset($response['data']) && count($response['data']) > 0) {
            return $response['data'][0]; // Retorna o primeiro encontrado
        }
        return null;
    }

    /**
     * Cria uma cobrança (Pix, Boleto, Cartão)
     */
    public function createPayment(array $paymentData)
    {
        return $this->request('/payments', 'POST', $paymentData);
    }

    /**
     * Obtém o QR Code / Payload Pix para uma cobrança
     */
    public function getPixQrCode($paymentId)
    {
        return $this->request("/payments/{$paymentId}/pixQrCode", 'GET');
    }

    /**
     * Método genérico para requisições HTTP
     */
    private function request($endpoint, $method, $data = null)
    {
        $url = Asaas::BASE_URL . $endpoint;
        $headers = Asaas::getHeaders();
        $headers[] = 'User-Agent: Dashboard-Sistema/1.0';

        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false, // Fix para XAMPP/Localhost
            CURLOPT_SSL_VERIFYHOST => false
        ];

        if ($data && ($method === 'POST' || $method === 'PUT')) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            error_log("[AsaasService] cURL Error: " . $err);
            throw new \Exception("cURL Error #: " . $err);
        }

        error_log("[AsaasService] Response Raw: " . substr($response, 0, 1000)); // Logar os primeiros 1000 chars

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[AsaasService] JSON Decode Error: " . json_last_error_msg());
            error_log("[AsaasService] Response Body: " . $response);
        }

        return $decoded;
    }
}
