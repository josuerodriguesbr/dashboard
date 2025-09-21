<?php
// app/Controllers/AdminController.php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

class AdminController
{
    public function dashboard()
    {
        // Verificar se há token antes de tentar verificar permissões
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? null;
        
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            // Redireciona para a página inicial se não houver token válido
            header('Location: /projetos/dashboard/');
            exit;
        }
        
        try {
            // Verificar autenticação e nível de permissão
            $usuario = PermissionMiddleware::verificarNivel('admin');
            
            // Lógica específica do admin
            $totalUsuarios = $this->getTotalUsuarios();
            $ultimosLogs = \App\Models\Log::listar(10);
            
            $dados = [
                'usuario' => $usuario,
                'totalUsuarios' => $totalUsuarios,
                'ultimosLogs' => $ultimosLogs
            ];
            
            return $this->view('admin/dashboard', $dados);
        } catch (\Exception $e) {
            error_log("Erro no AdminController: " . $e->getMessage());
            // Redireciona para a página inicial em caso de erro de autenticação
            header('Location: /projetos/dashboard/');
            exit;
        }
    }
    
    private function getTotalUsuarios()
    {
        global $pdo;
        
        if (!$pdo) {
            error_log("Conexão com o banco de dados não está disponível");
            return 0;
        }
        
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM integra_usuarios");
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            error_log("Erro ao buscar total de usuários: " . $e->getMessage());
            return 0;
        }
    }
    
    protected function view($name, $data = [])
    {
        $file = ROOT . 'app/Views/' . $name . '.php';
        if (file_exists($file)) {
            ob_start();
            extract($data);
            include $file;
            return ob_get_clean();
        }
        return "View não encontrada: $file";
    }
}