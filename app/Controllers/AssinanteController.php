<?php
// app/Controllers/AssinanteController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\ItemAdquirido;
use App\Utils\UserContext;

class AssinanteController
{
    public function dashboard()
    {
        // Verificar autenticação
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            redirect('/login');
            return;
        }

        $usuario = UserContext::getUsuario();
        
        // Recuperar Itens Adquiridos (Meus Serviços/Planos)
        $itemAdquiridoModel = new ItemAdquirido(\Config\Database::getConnection());
        $itensAdquiridos = $itemAdquiridoModel->getByUsuario($usuario['id']);
        
        // Renderiza a view do dashboard do assinante com dados
        view('assinante/dashboard', [
            'itensAdquiridos' => $itensAdquiridos,
            'usuario' => $usuario
        ]);
    }
}