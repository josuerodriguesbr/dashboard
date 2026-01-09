<?php
require_once __DIR__ . '/../config/autoload.php';

use Config\Database;

try {
    $pdo = Database::getConnection();
    
    // CPF Válido Gerado para Teste
    $novoCpf = '52998224097'; 
    $email = 'carlos@gmail.com';

    $stmt = $pdo->prepare("UPDATE usuarios SET cpf = ? WHERE email = ?");
    $stmt->execute([$novoCpf, $email]);

    if ($stmt->rowCount() > 0) {
        echo "Sucesso: CPF de {$email} atualizado para {$novoCpf}.\n";
    } else {
        echo "Aviso: Nenhum usuário encontrado com o email {$email} ou o CPF já era esse.\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
