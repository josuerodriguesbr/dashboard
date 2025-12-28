<?php
// Script para verificar o usuário admin e perfis
require_once __DIR__ . '/config/autoload.php';

use Config\Database;

$pdo = Database::getConnection();

try {
    // Verificar o usuário admin específico
    $stmt = $pdo->query("
        SELECT u.id, u.nome, u.email, u.idPerfilAtivo, p.id as perfil_id, pa.nivel as papel_nivel
        FROM integra_usuarios u
        LEFT JOIN integra_perfis p ON u.idPerfilAtivo = p.id
        LEFT JOIN integra_papeis pa ON p.id_papel = pa.id
        WHERE u.email = 'admin@sistema.com'
    ");
    
    $admin = $stmt->fetch();
    if ($admin) {
        echo "Status do usuário admin:\n";
        echo "  ID: {$admin['id']}\n";
        echo "  Nome: {$admin['nome']}\n";
        echo "  Email: {$admin['email']}\n";
        echo "  idPerfilAtivo: {$admin['idPerfilAtivo']}\n";
        echo "  Perfil ID: {$admin['perfil_id']}\n";
        echo "  Papel Nível: {$admin['papel_nivel']}\n";
        
        if (!$admin['idPerfilAtivo'] || !$admin['perfil_id']) {
            echo "\nO usuário admin não tem perfil ativo definido!\n";
        } else {
            echo "\nO usuário admin tem perfil ativo definido corretamente.\n";
        }
    } else {
        echo "Usuário admin não encontrado!\n";
    }
    
    // Verificar se o papel 'admin' existe
    $stmt = $pdo->query("SELECT id FROM integra_papeis WHERE nivel = 'admin'");
    $adminPapel = $stmt->fetch();
    
    if (!$adminPapel) {
        echo "\nPapel 'admin' não encontrado na tabela integra_papeis!\n";
    } else {
        echo "\nPapel 'admin' encontrado com ID: {$adminPapel['id']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}