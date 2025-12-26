<?php
// /projetos/dashboard/bin/init_data.php
// Script para popular as tabelas com dados iniciais
require_once 'config/autoload.php';

// Função para gerar hash único
function gerarHash() {
    return bin2hex(random_bytes(32)); // 64 caracteres hexadecimais
}

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
    // Primeiro usuário
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
            // Gerar hash de convite para o admin (primeiro usuário, não tem anfitrião)
            $hashConvite = gerarHash();
            
            // Criar perfil admin
            $stmt = $pdo->prepare("INSERT INTO integra_perfis (id_papel, id_usuario, creditos, hashConvite, hashAnfitriao, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$papel['id'], $usuarioId, 0.00, $hashConvite, $hashConvite /* Primeiro usuário tem hashAnfitriao igual ao hashConvite */, 'Ativo']);
            $perfilId = $pdo->lastInsertId();
            echo "Perfil administrador criado com ID: $perfilId e hashConvite: $hashConvite\n";
            
            // Atualizar perfil ativo do usuário
            $stmt = $pdo->prepare("UPDATE integra_usuarios SET idPerfilAtivo = ? WHERE id = ?");
            $stmt->execute([$perfilId, $usuarioId]);
            echo "Perfil ativo do usuário atualizado para o perfil de admin\n";
        }
    } else {
        echo "Usuário administrador já existe\n";
        $usuarioId = $usuario['id'];
    }
    
    // Função para criar perfil se não existir
    function criarPerfil($pdo, $adminId, $nivelPapel, $creditosIniciais) {
        // Obter o ID do papel
        $stmt = $pdo->prepare("SELECT id FROM integra_papeis WHERE nivel = ?");
        $stmt->execute([$nivelPapel]);
        $papel = $stmt->fetch();
        
        if ($papel) {
            // Verificar se o perfil já existe
            $checkStmt = $pdo->prepare("SELECT id FROM integra_perfis WHERE id_usuario = ? AND id_papel = ?");
            $checkStmt->execute([$adminId, $papel['id']]);
            $perfilExistente = $checkStmt->fetch();
            
            if (!$perfilExistente) {
                $hashConvite = gerarHash();
                $insertStmt = $pdo->prepare("INSERT INTO integra_perfis (id_papel, id_usuario, creditos, hashConvite, hashAnfitriao, status) VALUES (?, ?, ?, ?, ?, ?)");
                $insertStmt->execute([$papel['id'], $adminId, $creditosIniciais, $hashConvite, $hashConvite, 'Ativo']);
                echo "Perfil de $nivelPapel criado para o administrador\n";
                return true;
            } else {
                echo "Perfil de $nivelPapel já existe para o administrador\n";
                return false;
            }
        }
        return false;
    }
    
    // Criar alguns perfis adicionais para testes (se o usuário admin já existir)
    echo "Verificando perfis adicionais para o administrador...\n";
    
    // Obter o ID do usuário admin
    $stmt = $pdo->prepare("SELECT id FROM integra_usuarios WHERE email = ?");
    $stmt->execute(['admin@sistema.com']);
    $adminUsuario = $stmt->fetch();
    
    if ($adminUsuario) {
        $adminId = $adminUsuario['id'];
        
        // Perfis a serem criados com seus créditos iniciais
        $perfisParaCriar = [
            ['assinante', 50.00],
            ['vendedor', 25.00],
            ['operador', 10.00]
        ];
        
        // Criar perfis
        foreach ($perfisParaCriar as $perfil) {
            criarPerfil($pdo, $adminId, $perfil[0], $perfil[1]);
        }
        
        // Garantir que o perfil ativo do admin seja o de administrador
        $stmt = $pdo->prepare("SELECT p.id FROM integra_perfis p 
                              JOIN integra_papeis pa ON p.id_papel = pa.id 
                              WHERE p.id_usuario = ? AND pa.nivel = 'admin'");
        $stmt->execute([$adminId]);
        $perfilAdmin = $stmt->fetch();
        
        if ($perfilAdmin) {
            // Obter o perfil ativo atual
            $stmt = $pdo->prepare("SELECT idPerfilAtivo FROM integra_usuarios WHERE id = ?");
            $stmt->execute([$adminId]);
            $usuario = $stmt->fetch();
            
            if ($usuario && $usuario['idPerfilAtivo'] != $perfilAdmin['id']) {
                $stmt = $pdo->prepare("UPDATE integra_usuarios SET idPerfilAtivo = ? WHERE id = ?");
                $stmt->execute([$perfilAdmin['id'], $adminId]);
                echo "Perfil ativo do administrador definido como o perfil de admin\n";
            } else {
                echo "Perfil ativo do administrador já estava definido como o perfil de admin\n";
            }
        }
    }
    
    echo "Processo concluído!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}