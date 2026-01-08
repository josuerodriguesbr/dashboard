<?php
require_once __DIR__ . '/../config/autoload.php';
use Config\Database;

try {
    $pdo = Database::getConnection();
    
    // Novo ID gerado no teste de criação que sabemos estar VÁLIDO e com CPF
    $novoAsaasId = 'cus_000007402766'; 
    $cpfCorreto = '40847527387';
    $email = 'carlos@gmail.com';

    echo "Atualizando usuário {$email}...\n";
    echo "De Asaas ID antigo -> Para: {$novoAsaasId}\n";
    echo "CPF Base: {$cpfCorreto}\n";

    $stmt = $pdo->prepare("UPDATE integra_usuarios SET asaas_id = ?, cpf = ? WHERE email = ?");
    $stmt->execute([$novoAsaasId, $cpfCorreto, $email]); // Salvando CPF sem formatação tb para garantir

    echo "Sucesso! Registros afetados: " . $stmt->rowCount() . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
