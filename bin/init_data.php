<?php
// Script para popular as tabelas com dados iniciais
require_once 'config/autoload.php';

try {
    $pdo = \Config\Database::getConnection();
    
    // Inserir papéis se não existirem
    echo "Inserindo papéis...\n";
    $papeis = [
        ['admin', 'Administrador do sistema com acesso total'],
        ['assinante', 'Assinante com acesso a funcionalidades especiais'],
        ['operador', 'Operador com acesso a funcionalidades administrativas limitadas'],
        ['vendedor', 'Vendedor com acesso a funcionalidades de vendas'],
        ['cliente', 'Cliente com acesso básico ao sistema']
    ];
    
    foreach ($papeis as $papel) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO integra_papeis (nivel, descricao) VALUES (?, ?)");
        $stmt->execute($papel);
        echo "Papel '{$papel[0]}' inserido ou já existente.\n";
    }
    
    // Verificar se existe o usuário admin
    $stmt = $pdo->prepare("SELECT id FROM integra_usuarios WHERE email = ?");
    $stmt->execute(['admin@sistema.com']);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo "Inserindo usuário administrador...\n";
        // Inserir usuário administrador
        $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO integra_usuarios (nome, email, senha, cpf, telefone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@sistema.com', $senhaHash, '000.000.000-00', '(11) 99999-9999']);
        $usuarioId = $pdo->lastInsertId();
        echo "Usuário administrador inserido com ID: $usuarioId\n";
        
        // Obter ID do papel admin
        $stmt = $pdo->prepare("SELECT id FROM integra_papeis WHERE nivel = ?");
        $stmt->execute(['admin']);
        $papel = $stmt->fetch();
        
        if ($papel) {
            // Criar perfil admin
            $stmt = $pdo->prepare("INSERT INTO integra_perfis (id_papel, id_usuario, creditos, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$papel['id'], $usuarioId, 0.00, 'Ativo']);
            $perfilId = $pdo->lastInsertId();
            echo "Perfil administrador criado com ID: $perfilId\n";
            
            // Atualizar perfil ativo do usuário
            $stmt = $pdo->prepare("UPDATE integra_usuarios SET idPerfilAtivo = ? WHERE id = ?");
            $stmt->execute([$perfilId, $usuarioId]);
            echo "Perfil ativo do usuário atualizado\n";
        }
    } else {
        echo "Usuário administrador já existe\n";
    }
    
    echo "Processo concluído!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}