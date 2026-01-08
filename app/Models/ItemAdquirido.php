<?php
namespace App\Models;

use PDO;

class ItemAdquirido {
    protected $pdo;
    protected $table = 'integra_itens_adquiridos';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($usuarioId, $recursoId, $quantidade, $dataExpiracao = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} 
            (usuario_id, recurso_id, quantidade_restante, data_expiracao, status, createdAt) 
            VALUES (?, ?, ?, ?, 'ativo', NOW())
        ");
        return $stmt->execute([$usuarioId, $recursoId, $quantidade, $dataExpiracao]);
    }

    public function getByUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, r.nome as recurso_nome 
            FROM {$this->table} i
            JOIN integra_recursos r ON i.recurso_id = r.id
            WHERE i.usuario_id = ?
            ORDER BY i.createdAt DESC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function consumir($id, $quantidadeConsumida = 1) {
        $item = $this->getById($id);
        if (!$item || $item['status'] != 'ativo') return false;

        $novaQuantidade = $item['quantidade_restante'] - $quantidadeConsumida;
        $status = $item['status'];
        
        if ($novaQuantidade <= 0) {
            $novaQuantidade = 0;
            $status = 'esgotado';
        }

        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET quantidade_restante = ?, status = ? WHERE id = ?");
        return $stmt->execute([$novaQuantidade, $status, $id]);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
