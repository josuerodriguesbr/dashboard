<?php
// app/Controllers/AssinanteController.php
namespace App\Controllers;

class AssinanteController
{
    public function dashboard()
    {
        // Renderiza a view do dashboard do assinante
        view('assinante/dashboard');
    }
}