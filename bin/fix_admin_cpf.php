<?php
require_once __DIR__ . '/../config/autoload.php';

use Config\Database;

try {
    $pdo = Database::getConnection();
    
    // CPF Válido Gerado para Teste (Administrador)
    // Usando um diferente para não dar conflito se o sistema exigir CPF único
    $novoCpfAdmin = '06015505006'; 
    $email = 'admin@sistema.com';

    // Primeiro, vamos ver se o usuário existe
    $stmt = $pdo->prepare("SELECT id, nome, cpf FROM integra_usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Atualiza
        $update = $pdo->prepare("UPDATE integra_usuarios SET cpf = ? WHERE id = ?");
        $update->execute([$novoCpfAdmin, $user['id']]);
        echo "Sucesso: CPF do Administrador ({$user['nome']}) atualizado para {$novoCpfAdmin}.\n";
    } else {
        echo "Aviso: Usuário admin@sistema.com não encontrado.\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
