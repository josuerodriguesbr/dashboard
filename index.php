<?php
// /projetos/dashboard/index.php

require_once 'public/autoload.php';

use App\Core\Router;
use App\Controllers\DashboardController;
use App\Controllers\WebhookController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\AssinanteController;
use App\Controllers\VendedorController;
use App\Controllers\ClienteController;

$router = new Router();

// Rotas públicas
$router->get('/', [DashboardController::class, 'paginaInicial']);
$router->get('/cadastro-usuario', [DashboardController::class, 'mostraCadastroUsuario']);
$router->get('/perfil', [DashboardController::class, 'mostraPerfilUsuario']); // Nova rota
$router->get('/logs', [DashboardController::class, 'logs']);
$router->get('/server-logs', [DashboardController::class, 'serverLogs']);
$router->get('/db-monitor', [DashboardController::class, 'dbMonitor']);
$router->get('/frontend', [DashboardController::class, 'frontend']);
$router->post('/webhook/asaas', [WebhookController::class, 'handleAsaas']);

$router->post('/cadastro-usuario', [DashboardController::class, 'cadastroUsuario']);
$router->post('/atualiza-usuario', [DashboardController::class, 'atualizaUsuario']);
$router->get('/perfil/carregar', [DashboardController::class, 'carregaPerfil']); // Nova rota

// Rotas de autenticação
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Painéis
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/assinante', [AssinanteController::class, 'dashboard']);
$router->get('/vendedor', [VendedorController::class, 'dashboard']);
$router->get('/cliente', [ClienteController::class, 'dashboard']);

$router->get('/verificar-token', function () {
    try {
        $usuario = \App\Middleware\AuthMiddleware::verificar();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'authenticated' => true,
            'user' => $usuario
        ]);
    } catch (Exception $e) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'authenticated' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit; // Garantir que nada mais seja executado
});

// === Processa a URL com base no .htaccess ===
$url = $_GET['url'] ?? '';
$base = dirname($_SERVER['SCRIPT_NAME']);
$base = ($base == '/' || $base == '\\') ? '' : $base;

if ($base && strncmp($url, $base, strlen($base)) === 0) {
    $url = substr($url, strlen($base));
}
$url = ltrim($url, '/');

$router->resolve($url, $_SERVER['REQUEST_METHOD']);