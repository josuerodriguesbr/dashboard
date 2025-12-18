<?php
// app/Controllers/VendedorController.php
namespace App\Controllers;

class VendedorController
{
    public function dashboard()
    {
        // Renderiza a view do dashboard do vendedor
        view('vendedor/dashboard');
    }
}