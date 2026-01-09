<?php
namespace App\Models;

use PDO;

class Carteira {
    protected $pdo;
    protected $table = 'carteira';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Cria carteira zerada para novo usuário
    public function createForUser($usuarioId) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (usuario_id, saldo_atual) VALUES (?, 0)");
        return $stmt->execute([$usuarioId]);
    }

    // Busca carteira pelo ID do usuário
    public function getByUserId($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Atualiza o saldo (pode ser usado para incrementar ou decrementar)
    // Para debitar, passe um valor negativo.
    public function updateSaldo($usuarioId, $valor) {
        // Primeiro verificamos o saldo atual para garantir consistência
        $current = $this->getByUserId($usuarioId);
        if (!$current) {
            $this->createForUser($usuarioId);
            $current = ['saldo_atual' => 0];
        }

        $novoSaldo = $current['saldo_atual'] + $valor;
        if ($novoSaldo < 0) {
            // Regra de negócio: não permitir saldo negativo (opcional, dependendo do sistema)
            // throw new \Exception("Saldo insuficiente.");
        }

        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET saldo_atual = ?, updatedAt = NOW() WHERE usuario_id = ?");
        return $stmt->execute([$novoSaldo, $usuarioId]);
    }
}
