<?php
// /projetos/dashboard/bin/init_data.php
// Script para popular as tabelas com dados iniciais
require_once __DIR__ . '/../config/autoload.php';

try {
    $pdo = \Config\Database::getConnection();
    
    // 1. Inserir Papéis
    echo "Inserindo papéis...\n";
    $papeis = [
        ['admin', 'Administrador do sistema'],
        ['assinante', 'Assinante'],
        ['vendedor', 'Vendedor'],
        ['operador', 'Operador'],
        ['cliente', 'Cliente']
    ];
    
    foreach ($papeis as $papel) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO integra_papeis (nivel, descricao) VALUES (?, ?)");
        $stmt->execute($papel);
    }
    
    // 2. Inserir Tipos de Transação
    echo "Inserindo tipos de transação...\n";
    $tiposTransacao = [
        [1, 'Compra', 1], 
        [2, 'Consumo', -1], 
        [3, 'Ajuste Manual (Crédito)', 1],
        [4, 'Ajuste Manual (Débito)', -1],
        [5, 'Bônus', 1],
        [6, 'Estorno', 1]
    ];

    foreach ($tiposTransacao as $tipo) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO integra_cred_trans_tipos (id, nome, multiplicador) VALUES (?, ?, ?)");
        $stmt->execute($tipo);
    }
    
    // 3. Verificar/Criar Admin
    $stmt = $pdo->prepare("SELECT id FROM integra_usuarios WHERE email = ?");
    $stmt->execute(['admin@sistema.com']);
    $usuario = $stmt->fetch();
    
    $usuarioId = null;
    
    if (!$usuario) {
        echo "Inserindo usuário administrador...\n";
        $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO integra_usuarios (nome, email, senha, cpf, telefone, parent_id) VALUES (?, ?, ?, ?, ?, NULL)");
        $stmt->execute(['Administrador', 'admin@sistema.com', $senhaHash, '000.000.000-00', '(11) 99999-9999']);
        $usuarioId = $pdo->lastInsertId();
        
        // Criar Carteira
        $carteiraModel = new \App\Models\Carteira($pdo);
        $carteiraModel->createForUser($usuarioId);
    } else {
        echo "Usuário administrador já existe.\n";
        $usuarioId = $usuario['id'];
        
        // Garantir carteira
        $carteiraModel = new \App\Models\Carteira($pdo);
        if(!$carteiraModel->getByUserId($usuarioId)){
             $carteiraModel->createForUser($usuarioId);
        }
    }
    
    // 4. Criar Perfil Admin e definir como ativo
    if ($usuarioId) {
        $papelAdmin = \App\Models\Papel::buscarPorNivel('admin');
        if ($papelAdmin) {
            // Verifica se ja tem perfil
            $stmt = $pdo->prepare("SELECT id FROM integra_perfis WHERE id_usuario = ? AND id_papel = ?");
            $stmt->execute([$usuarioId, $papelAdmin['id']]);
            $perfil = $stmt->fetch();
            
            if (!$perfil) {
                echo "Criando Perfil Admin...\n";
                $perfilId = \App\Models\Perfil::criar($usuarioId, $papelAdmin['id']);
                \App\Models\Usuario::definirPerfilAtivo($usuarioId, $perfilId);
                echo "Perfil Admin criado e definido como ativo.\n";
            } else {
                echo "Perfil Admin já existe.\n";
                // Garantir ativo
                \App\Models\Usuario::definirPerfilAtivo($usuarioId, $perfil['id']);
            }
        }
    }
    
    echo "Processo concluído!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}