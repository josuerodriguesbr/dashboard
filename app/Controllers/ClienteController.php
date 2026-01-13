<?php
// app/Controllers/ClienteController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\ItemAdquirido;
use App\Models\CreditoTransacao;
use App\Utils\UserContext;

class ClienteController
{
    public function dashboard()
    {
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            redirect('/login');
            return;
        }

        $usuario = UserContext::getUsuario();

        // Saldo
        $saldo = Usuario::getSaldo($usuario['id']);

        // Meus Produtos / Ingressos
        $itemAdquiridoModel = new ItemAdquirido(\Config\Database::getConnection());
        $itensAdquiridos = $itemAdquiridoModel->getByUsuario($usuario['id']);

        // Histórico
        $transacoes = CreditoTransacao::getHistorico($usuario['id'], 5);

        // Renderiza a view do dashboard do cliente
        view('cliente/dashboard', [
            'usuario' => $usuario,
            'saldo' => $saldo,
            'itensAdquiridos' => $itensAdquiridos,
            'transacoes' => $transacoes
        ]);
    }
}