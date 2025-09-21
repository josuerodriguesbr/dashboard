<?php
// app/Controllers/AssinanteController.php
namespace App\Controllers;

use App\Middleware\PermissionMiddleware;

class AssinanteController
{
    public function dashboard()
    {
        $usuario = PermissionMiddleware::verificarNivel('assinante');
        
        // Lógica específica do assinante
        $dados = [
            'usuario' => $usuario,
            'meusDados' => $this->getMeusDados($usuario['id'])
        ];
        
        return $this->view('assinante/dashboard', $dados);
    }
    
    private function getMeusDados($usuarioId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM integra_usuarios WHERE id = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch();
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