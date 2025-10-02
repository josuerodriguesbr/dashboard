<?php
// app/Controllers/AdminController.php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

class AdminController
{

    public function dashboard()
    {
        try {
            // Verifica autenticação e permissões
            $usuario = PermissionMiddleware::verificarNivel('admin');
            
            // Carrega a view completa do dashboard
            view('admin/dashboard');
            
        } catch (\Exception $e) {
            // Redireciona para a página inicial se não tiver permissão
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
    
    /*
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
        */
        
}