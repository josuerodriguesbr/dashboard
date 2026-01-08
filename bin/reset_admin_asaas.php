<?php
require_once __DIR__ . '/../config/autoload.php';

use Config\Database;

try {
    $pdo = Database::getConnection();
    $email = 'admin@sistema.com';

    // Limpar asaas_id para forçar recriação do cliente no Asaas com os dados novos (CPF)
    $stmt = $pdo->prepare("UPDATE integra_usuarios SET asaas_id = NULL WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "Sucesso: asaas_id do administrador ({$email}) foi limpo.\n";
    } else {
        echo "Aviso: asaas_id já estava vazio ou usuário não encontrado.\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
