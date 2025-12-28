<?php
// /projetos/dashboard/index.php

require_once 'config/autoload.php';

use App\Core\Router;
use App\Controllers\DashboardController;
use App\Controllers\WebhookController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\AssinanteController;
use App\Controllers\OperadorController;
use App\Controllers\VendedorController;
use App\Controllers\ClienteController;
use App\Controllers\CreditoController; // Adicionando o novo controller
use App\Controllers\UsuarioController; // Adicionando o controller de usuários

$router = new Router();

// Rotas públicas
$router->get('/', [DashboardController::class, 'paginaInicial']);
// Removido: $router->get('/mostra-cadastro-usuario', [DashboardController::class, 'mostraCadastroUsuario']);
$router->get('/perfil', [DashboardController::class, 'mostraPerfilUsuario']); // Nova rota
$router->get('/logs', [DashboardController::class, 'logs']);
$router->get('/server-logs', [DashboardController::class, 'serverLogs']);
$router->get('/db-monitor', [DashboardController::class, 'dbMonitor']);
$router->get('/frontend', [DashboardController::class, 'frontend']);
$router->get('/cadastro-via-convite', [AuthController::class, 'mostrarCadastroViaConvite']); // Nova rota
$router->post('/webhook/asaas', [WebhookController::class, 'handleAsaas']);

// Removido: $router->post('/cadastro-usuario', [DashboardController::class, 'cadastroUsuario']);
$router->post('/cadastro-via-convite', [AuthController::class, 'cadastroViaConvite']); // Nova rota
$router->post('/atualiza-usuario', [DashboardController::class, 'atualizaUsuario']);
$router->get('/perfil/carregar', [DashboardController::class, 'carregaPerfil']); // Nova rota
$router->get('/gerar-link-convite', [AuthController::class, 'gerarLinkConvite']); // Nova rota

// Rotas de autenticação
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Painéis
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/assinante', [AssinanteController::class, 'dashboard']);
$router->get('/operador', [OperadorController::class, 'dashboard']);
$router->get('/vendedor', [VendedorController::class, 'dashboard']);
$router->get('/operador', [OperadorController::class, 'dashboard']);
$router->get('/cliente', [ClienteController::class, 'dashboard']);

// Rotas de créditos
$router->get('/creditos', [CreditoController::class, 'mostrarGerenciarCreditos']);
$router->get('/creditos/saldo', [CreditoController::class, 'getSaldo']);
$router->get('/creditos/historico', [CreditoController::class, 'getHistoricoTransacoes']);
$router->get('/creditos/perfis-transferencia', [CreditoController::class, 'getPerfisParaTransferencia']);
$router->post('/creditos/transferir', [CreditoController::class, 'transferirCreditos']);

// Rotas de usuários e convites
$router->get('/usuarios/vendedores', [UsuarioController::class, 'getVendedoresDisponiveis']);
$router->get('/usuarios/vendedor-hash/{id}', [UsuarioController::class, 'getHashVendedor']);

$router->get('/verificar-token', function () {
    error_log("Requisição para /verificar-token recebida");
    try {
        $usuario = \App\Middleware\AuthMiddleware::verificar();
        error_log("Token verificado com sucesso: " . print_r($usuario, true));
        header('Content-Type: application/json; charset=utf-8');
        $response = [
            'authenticated' => true,
            'user' => $usuario
        ];
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("Erro ao verificar token: " . $e->getMessage());
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        $response = [
            'authenticated' => false,
            'message' => $e->getMessage()
        ];
        echo json_encode($response);
    }
    exit; // Garantir que nada mais seja executado
});

// Rota temporária para correção do perfil do administrador
$router->get('/corrigir-admin', function () {
    header('Content-Type: text/html; charset=utf-8');
    
    echo "<pre>";
    echo "Iniciando script de correção do administrador...\n";

    try {
        $pdo = \Config\Database::getConnection();

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
    
    echo "</pre>";
});

// === Processa a URL com base no .htaccess ===
$path = $_SERVER['REQUEST_URI'] ?? '/';

// Remover o prefixo do subdiretório para obter o caminho real
$subdir = '/projetos/dashboard';
if (strpos($path, $subdir) === 0) {
    $path = substr($path, strlen($subdir));
    if (empty($path)) {
        $path = '/';
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remover parâmetros da URL
if (strpos($path, '?') !== false) {
    $path = substr($path, 0, strpos($path, '?'));
}

$router->resolve($path, $method);