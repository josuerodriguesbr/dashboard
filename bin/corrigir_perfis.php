<?php
// Script para verificar e corrigir perfis de usuários existentes
require_once __DIR__ . '/../config/autoload.php';

use Config\Database;

$pdo = Database::getConnection();

try {
    // Verificar se o papel 'admin' existe, senão criar
    $stmt = $pdo->query("SELECT id FROM papeis WHERE nivel = 'admin'");
    $adminPapel = $stmt->fetch();
    
    if (!$adminPapel) {
        echo "Criando papel 'admin'...\n";
        $stmt = $pdo->prepare("INSERT INTO papeis (nivel, descricao) VALUES (?, ?)");
        $stmt->execute(['admin', 'Administrador do sistema']);
        $adminPapelId = $pdo->lastInsertId();
        echo "Papel 'admin' criado com ID: $adminPapelId\n";
    } else {
        $adminPapelId = $adminPapel['id'];
        echo "Papel 'admin' já existe com ID: $adminPapelId\n";
    }
    
    // Verificar se o papel 'assinante' existe, senão criar
    $stmt = $pdo->query("SELECT id FROM papeis WHERE nivel = 'assinante'");
    $assinantePapel = $stmt->fetch();
    
    if (!$assinantePapel) {
        echo "Criando papel 'assinante'...\n";
        $stmt = $pdo->prepare("INSERT INTO papeis (nivel, descricao) VALUES (?, ?)");
        $stmt->execute(['assinante', 'Assinante do sistema']);
        $assinantePapelId = $pdo->lastInsertId();
        echo "Papel 'assinante' criado com ID: $assinantePapelId\n";
    } else {
        $assinantePapelId = $assinantePapel['id'];
        echo "Papel 'assinante' já existe com ID: $assinantePapelId\n";
    }
    
    // Verificar se o papel 'vendedor' existe, senão criar
    $stmt = $pdo->query("SELECT id FROM papeis WHERE nivel = 'vendedor'");
    $vendedorPapel = $stmt->fetch();
    
    if (!$vendedorPapel) {
        echo "Criando papel 'vendedor'...\n";
        $stmt = $pdo->prepare("INSERT INTO papeis (nivel, descricao) VALUES (?, ?)");
        $stmt->execute(['vendedor', 'Vendedor do sistema']);
        $vendedorPapelId = $pdo->lastInsertId();
        echo "Papel 'vendedor' criado com ID: $vendedorPapelId\n";
    } else {
        $vendedorPapelId = $vendedorPapel['id'];
        echo "Papel 'vendedor' já existe com ID: $vendedorPapelId\n";
    }
    
    // Verificar se o papel 'operador' existe, senão criar
    $stmt = $pdo->query("SELECT id FROM papeis WHERE nivel = 'operador'");
    $operadorPapel = $stmt->fetch();
    
    if (!$operadorPapel) {
        echo "Criando papel 'operador'...\n";
        $stmt = $pdo->prepare("INSERT INTO papeis (nivel, descricao) VALUES (?, ?)");
        $stmt->execute(['operador', 'Operador do sistema']);
        $operadorPapelId = $pdo->lastInsertId();
        echo "Papel 'operador' criado com ID: $operadorPapelId\n";
    } else {
        $operadorPapelId = $operadorPapel['id'];
        echo "Papel 'operador' já existe com ID: $operadorPapelId\n";
    }
    
    // Verificar se o papel 'cliente' existe, senão criar
    $stmt = $pdo->query("SELECT id FROM papeis WHERE nivel = 'cliente'");
    $clientePapel = $stmt->fetch();
    
    if (!$clientePapel) {
        echo "Criando papel 'cliente'...\n";
        $stmt = $pdo->prepare("INSERT INTO papeis (nivel, descricao) VALUES (?, ?)");
        $stmt->execute(['cliente', 'Cliente do sistema']);
        $clientePapelId = $pdo->lastInsertId();
        echo "Papel 'cliente' criado com ID: $clientePapelId\n";
    } else {
        $clientePapelId = $clientePapel['id'];
        echo "Papel 'cliente' já existe com ID: $clientePapelId\n";
    }
    
    // Verificar e corrigir usuários que não têm perfil ativo
    $stmt = $pdo->query("
        SELECT u.id, u.nome, u.email, u.idPerfilAtivo
        FROM usuarios u
        LEFT JOIN perfis p ON u.id = p.id_usuario
        WHERE u.idPerfilAtivo IS NULL OR u.idPerfilAtivo = 0
        GROUP BY u.id
    ");
    
    $usuariosSemPerfilAtivo = $stmt->fetchAll();
    
    foreach ($usuariosSemPerfilAtivo as $usuario) {
        echo "Corrigindo usuário: {$usuario['nome']} ({$usuario['email']})\n";
        
        // Verificar se o usuário já tem algum perfil criado
        $stmtPerfil = $pdo->prepare("SELECT id, id_papel FROM perfis WHERE id_usuario = ?");
        $stmtPerfil->execute([$usuario['id']]);
        $perfilExistente = $stmtPerfil->fetch();
        
        if ($perfilExistente) {
            // Usuário já tem um perfil, vamos defini-lo como ativo
            $perfilId = $perfilExistente['id'];
            $papelId = $perfilExistente['id_papel'];
            echo "  Usuário já tem perfil (ID: $perfilId) com papel ID: $papelId\n";
        } else {
            // Criar um perfil para o usuário
            // Vamos tentar determinar o papel apropriado com base no email
            if (strpos($usuario['email'], 'admin') !== false) {
                $papelId = $adminPapelId;
            } elseif (strpos($usuario['email'], 'assinante') !== false) {
                $papelId = $assinantePapelId;
            } else {
                // Por padrão, atribuir papel de cliente
                $papelId = $clientePapelId;
            }
            
            // Gerar hash de convite
            $hashConvite = bin2hex(random_bytes(32));
            
            // Criar perfil para o usuário
            $stmtCriarPerfil = $pdo->prepare("
                INSERT INTO perfis (id_papel, id_usuario, creditos, hashConvite, hashAnfitriao, status)
                VALUES (?, ?, 0.00, ?, NULL, 'Ativo')
            ");
            $stmtCriarPerfil->execute([$papelId, $usuario['id'], $hashConvite]);
            $perfilId = $pdo->lastInsertId();
            
            echo "  Criado perfil para o usuário (ID: $perfilId) com papel ID: $papelId\n";
        }
        
        // Atualizar o idPerfilAtivo do usuário
        $stmtAtualizar = $pdo->prepare("UPDATE usuarios SET idPerfilAtivo = ? WHERE id = ?");
        $stmtAtualizar->execute([$perfilId, $usuario['id']]);
        
        echo "  Definido perfil ativo (ID: $perfilId) para o usuário (ID: {$usuario['id']})\n";
    }
    
    echo "\nVerificação e correção concluídas.\n";
    
    // Verificar o usuário admin específico
    $stmt = $pdo->query("
        SELECT u.id, u.nome, u.email, u.idPerfilAtivo, p.id as perfil_id, pa.nivel as papel_nivel
        FROM usuarios u
        LEFT JOIN perfis p ON u.idPerfilAtivo = p.id
        LEFT JOIN papeis pa ON p.id_papel = pa.id
        WHERE u.email = 'admin@sistema.com'
    ");
    
    $admin = $stmt->fetch();
    if ($admin) {
        echo "\nStatus do usuário admin:\n";
        echo "  ID: {$admin['id']}\n";
        echo "  Nome: {$admin['nome']}\n";
        echo "  Email: {$admin['email']}\n";
        echo "  idPerfilAtivo: {$admin['idPerfilAtivo']}\n";
        echo "  Perfil ID: {$admin['perfil_id']}\n";
        echo "  Papel Nível: {$admin['papel_nivel']}\n";
    } else {
        echo "\nUsuário admin não encontrado!\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}