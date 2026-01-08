<?php
namespace App\Models;

use PDO;

class Recarga {
    protected $pdo;
    protected $table = 'integra_recargas';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($dados) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} 
            (usuario_id, valor_reais, quantidade_creditos, status, txid, qrCode, external_id, asaas_id, invoice_url, createdAt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $status = $dados['status'] ?? 'pendente';
        
        $stmt->execute([
            $dados['usuario_id'],
            $dados['valor_reais'],
            $dados['quantidade_creditos'],
            $status,
            $dados['txid'] ?? null,
            $dados['qrCode'] ?? null,
            $dados['external_id'] ?? null,
            $dados['asaas_id'] ?? null,
            $dados['invoice_url'] ?? null
        ]);

        return $this->pdo->lastInsertId();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByExternalId($externalId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE external_id = ?");
        $stmt->execute([$externalId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByAsaasId($asaasId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE asaas_id = ?");
        $stmt->execute([$asaasId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $paymentDate = null) {
        $sql = "UPDATE {$this->table} SET status = ?";
        $params = [$status];

        if ($paymentDate) {
            $sql .= ", data_pagamento = ?, payment_date = ?";
            $params[] = $paymentDate;
            $params[] = $paymentDate;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getByUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = ? ORDER BY createdAt DESC");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
