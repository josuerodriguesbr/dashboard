<?php
require_once __DIR__ . '/../config/autoload.php';
use Config\Database;

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id, nome, email, cpf, asaas_id FROM usuarios WHERE email LIKE 'carlos@%'");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
