<?php
// bin/add_asaas_column.php
require_once __DIR__ . '/../Config/Database.php'; // Ajuste: path relativo a partir de bin/

try {
    $pdo = \Config\Database::getConnection();
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'asaas_id'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Adicionando coluna asaas_id...\n";
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN asaas_id VARCHAR(50) DEFAULT NULL AFTER telefone");
        echo "Coluna adicionada com sucesso!\n";
    } else {
        echo "Coluna asaas_id já existe.\n";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
