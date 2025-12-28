<?php
// Script para corrigir o perfil do usuário administrador
require_once __DIR__ . '/../config/autoload.php';

use Config\Database;

echo "Iniciando script de correção do administrador...\n";

$pdo = Database::getConnection();

try {
    echo "Conexão com banco de dados estabelecida.\n";
    
    // Encontrar o usuário administrador
    $stmt = $pdo->prepare("SELECT id, nome, email FROM integra_usuarios WHERE email = 'admin@sistema.com'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if (!$admin) {
        echo "Usuário administrador não encontrado!\n";
        exit(1);
    }
    
    $adminId = $admin['id'];
    echo "Usuário administrador encontrado: {$admin['nome']} (ID: $adminId)\n";
    
    // Verificar se já existe um perfil de admin para este usuário
    $stmt = $pdo->prepare("
        SELECT p.id, p.status, p.creditos, p.hashConvite, p.hashAnfitriao, pa.nivel
        FROM integra_perfis p
        JOIN integra_papeis pa ON p.id_papel = pa.id
        WHERE p.id_usuario = ? AND pa.nivel = 'admin'
    ");
    $stmt->execute([$adminId]);
    $perfilAdmin = $stmt->fetch();
    
    if ($perfilAdmin) {
        echo "Perfil de administrador já existe (ID: {$perfilAdmin['id']})\n";
        
        // Verificar se o idPerfilAtivo do usuário já está definido corretamente
        $stmt = $pdo->prepare("SELECT idPerfilAtivo FROM integra_usuarios WHERE id = ?");
        $stmt->execute([$adminId]);
        $usuario = $stmt->fetch();
        
        echo "Perfil ativo atual do usuário: " . ($usuario['idPerfilAtivo'] ?? 'NULL') . "\n";
        
        if ($usuario['idPerfilAtivo'] != $perfilAdmin['id']) {
            // Atualizar o idPerfilAtivo do usuário
            $stmt = $pdo->prepare("UPDATE integra_usuarios SET idPerfilAtivo = ? WHERE id = ?");
            $stmt->execute([$perfilAdmin['id'], $adminId]);
            echo "Perfil ativo do usuário atualizado para o perfil de admin (ID: {$perfilAdmin['id']})\n";
        } else {
            echo "Perfil ativo do usuário já está definido corretamente\n";
        }
    } else {
        echo "Criando perfil de administrador...\n";
        
        // Obter o ID do papel admin
        $stmt = $pdo->prepare("SELECT id FROM integra_papeis WHERE nivel = ?");
        $stmt->execute(['admin']);
        $papel = $stmt->fetch();
        
        if (!$papel) {
            echo "Erro: Papel 'admin' não encontrado!\n";
            exit(1);
        }
        
        echo "Papel admin encontrado com ID: {$papel['id']}\n";
        
        // Gerar hash de convite
        $hashConvite = bin2hex(random_bytes(32));
        
        // Criar perfil admin com hashAnfitriao NULL (primeiro usuário)
        $stmt = $pdo->prepare("
            INSERT INTO integra_perfis (id_papel, id_usuario, creditos, hashConvite, hashAnfitriao, status)
            VALUES (?, ?, ?, ?, ?, 'Ativo')
        ");
        $stmt->execute([$papel['id'], $adminId, 0.00, $hashConvite, NULL]);
        $perfilId = $pdo->lastInsertId();
        
        echo "Perfil admin criado com ID: $perfilId\n";
        
        // Atualizar o idPerfilAtivo do usuário
        $stmt = $pdo->prepare("UPDATE integra_usuarios SET idPerfilAtivo = ? WHERE id = ?");
        $stmt->execute([$perfilId, $adminId]);
        
        echo "Perfil de administrador criado com sucesso (ID: $perfilId)\n";
    }
    
    echo "Correção concluída!\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Erro completo: " . $e->getTraceAsString() . "\n";
}