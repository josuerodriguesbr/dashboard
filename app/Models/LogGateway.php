<?php
namespace App\Models;

use PDO;

class LogGateway {
    protected $pdo;
    protected $table = 'logs_gateway';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function log($recargaId, $endpoint, $payloadEnvio, $payloadRetorno, $eventType = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} 
            (recarga_id, endpoint, payload_envio, payload_retorno, event_type, createdAt) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $recargaId,
            $endpoint,
            is_array($payloadEnvio) ? json_encode($payloadEnvio) : $payloadEnvio,
            is_array($payloadRetorno) ? json_encode($payloadRetorno) : $payloadRetorno,
            $eventType
        ]);
    }
}
