<?php
require_once __DIR__ . '/../config/autoload.php';
use Config\Database;

try {
    $pdo = Database::getConnection();
    
    $email = 'marcoslima@gmail.com';
    echo "Limpando Asaas ID do usuário {$email}...\n";

    $stmt = $pdo->prepare("UPDATE usuarios SET asaas_id = NULL WHERE email = ?");
    $stmt->execute([$email]);

    echo "Sucesso! O sistema gerará um ID novo na próxima tentativa.\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
