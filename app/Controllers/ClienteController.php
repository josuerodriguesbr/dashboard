<?php
// app/Controllers/ClienteController.php
namespace App\Controllers;

use App\Middleware\PermissionMiddleware;

class ClienteController
{
    public function dashboard()
    {
        $usuario = PermissionMiddleware::verificarNivel('cliente');
        
        // Lógica específica do cliente
        $dados = [
            'usuario' => $usuario
        ];
        
        return $this->view('cliente/dashboard', $dados);
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