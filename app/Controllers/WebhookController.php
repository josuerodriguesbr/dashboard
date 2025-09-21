<?php
namespace App\Controllers;

use App\Models\Log;

class WebhookController
{
    public function handleAsaas()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Registra o webhook recebido
        Log::registrar(null, 'Webhook Recebido', "Evento: " . ($data['event'] ?? 'desconhecido'));

        // Aqui você processa o pagamento, cria cliente, etc.
        // Vamos deixar vazio por enquanto

        http_response_code(200);
        echo '{"status": "ok"}';
    }
}