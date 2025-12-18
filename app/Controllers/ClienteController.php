<?php
// app/Controllers/ClienteController.php
namespace App\Controllers;

class ClienteController
{
    public function dashboard()
    {
        // Renderiza a view do dashboard do cliente
        view('cliente/dashboard');
    }
}