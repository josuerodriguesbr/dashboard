<?php
// app/Controllers/OperadorController.php
namespace App\Controllers;

class OperadorController
{
    public function dashboard()
    {
        // Renderiza a view do dashboard do operador
        view('operador/dashboard');
    }
}