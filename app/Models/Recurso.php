<?php
namespace App\Models;

use PDO;

class Recurso {
    protected $pdo;
    protected $table = 'integra_recursos';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($dados) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (nome, tipo_cobranca, preco_creditos) VALUES (?, ?, ?)");
        return $stmt->execute([
            $dados['nome'],
            $dados['tipo_cobranca'], // 'unidade' ou 'tempo'
            $dados['preco_creditos']
        ]);
    }

    public function update($id, $dados) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET nome = ?, tipo_cobranca = ?, preco_creditos = ? WHERE id = ?");
        return $stmt->execute([
            $dados['nome'],
            $dados['tipo_cobranca'],
            $dados['preco_creditos'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
