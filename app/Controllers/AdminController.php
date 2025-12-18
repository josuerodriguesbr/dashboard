<?php
// app/Controllers/AdminController.php
namespace App\Controllers;

class AdminController
{
    public function dashboard()
    {
        // Renderiza a view do dashboard do admin
        view('admin/dashboard');
    }
}