<?php
// app/Controllers/VendedorController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\CreditoTransacao;
use App\Utils\UserContext;

class VendedorController
{
    public function dashboard()
    {
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            redirect('/login');
            return;
        }

        $usuarioSessao = UserContext::getUsuario();

        // Buscar dados completos para ter o hashConvite
        $vendedor = Usuario::buscarPorId($usuarioSessao['id']);

        // Meus Clientes
        $clientes = Usuario::listarPorParentId($vendedor['id']);

        // Saldo (Comissões / Total)
        $saldo = Usuario::getSaldo($vendedor['id']);

        // Histórico recente
        $transacoes = CreditoTransacao::getHistorico($vendedor['id'], 5);

        // Renderiza a view do dashboard do vendedor
        view('vendedor/dashboard', [
            'vendedor' => $vendedor,
            'clientes' => $clientes,
            'saldo' => $saldo,
            'transacoes' => $transacoes
        ]);
    }
}