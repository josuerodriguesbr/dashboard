<?php
// app/Controllers/VendedorController.php
namespace App\Controllers;

use App\Middleware\PermissionMiddleware;

class VendedorController
{
    public function dashboard()
    {
        $usuario = PermissionMiddleware::verificarNivel('vendedor');
        
        // Lógica específica do vendedor
        $dados = [
            'usuario' => $usuario,
            'minhasVendas' => $this->getMinhasVendas($usuario['id'])
        ];
        
        return $this->view('vendedor/dashboard', $dados);
    }
    
    private function getMinhasVendas($usuarioId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT v.*, u.nome as cliente 
            FROM integra_vendas v
            JOIN integra_usuarios u ON v.usuarioId = u.id
            WHERE v.usuarioId = ?
            ORDER BY v.createdAt DESC
            LIMIT 10
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
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