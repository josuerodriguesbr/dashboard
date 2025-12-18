// public/js/operador/dashboard.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Dashboard do Operador carregado');
    
    // Adiciona classe 'active' ao link da navegação atual
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar nav ul li a');
    
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
    
    // Atualiza o contador de horas trabalhadas (exemplo)
    const horasTrabalhadasCard = document.querySelector('.card:nth-child(3) p');
    if (horasTrabadasCard) {
        // Simula atualização de horas trabalhadas
        setTimeout(() => {
            horasTrabalhadasCard.textContent = '42h';
        }, 3000);
    }
});
/* public/css/style.css */

:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --border-radius: 4px;
    --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f6fa;
    color: #2f3640;
    line-height: 1.6;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    background-color: var(--dark-color);
    color: white;
    width: 250px;
    padding: 20px;
}

.sidebar h2 {
    margin-bottom: 30px;
    font-size: 24px;
    text-align: center;
}

.sidebar nav ul {
    list-style: none;
}

.sidebar nav ul li {
    margin: 15px 0;
}

.sidebar nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.sidebar nav ul li a:hover {
    color: var(--primary-color);
}

.main-content {
    flex: 1;
    padding: 20px 40px;
    background-color: #ffffff;
}

.main-content h1 {
    margin-bottom: 20px;
    color: #343a40;
}

.status-cards {
    display: flex;
    gap: 20px;
    margin-top: 30px;
}

.card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
    flex: 1;
    min-width: 200px;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.card h3 {
    margin-bottom: 10px;
    font-size: 18px;
    color: #4a4a4a;
}

.card p {
    font-size: 24px;
    font-weight: bold;
    color: var(--primary-color);
}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($logo_titulo) ?></title>
    <link rel="stylesheet" href="/projetos/dashboard/public/css/style.css">
    <script type="module" src="<?= htmlspecialchars($page_js_module) ?>"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Operador</h2>
            <nav>
                <ul>
                    <li><a href="#">Início</a></li>
                    <li><a href="#">Minhas Tarefas</a></li>
                    <li><a href="#">Relatórios</a></li>
                    <li><a href="#">Perfil</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1>Dashboard do Operador</h1>
            <p>Você está no dashboard de Operador</p>
            
            <!-- Status Cards -->
            <div class="status-cards">
                <div class="card">
                    <h3>Tarefas Atuais</h3>
                    <p>15</p>
                </div>
                <div class="card">
                    <h3>Relatórios Pendentes</h3>
                    <p>7</p>
                </div>
                <div class="card">
                    <h3>Horas Trabalhadas</h3>
                    <p>40h</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php
// app/Views/operador/dashboard.php

$logo_icone = '📊';
$logo_titulo = 'Dashboard Operador';
$page_js_module = '/projetos/dashboard/public/js/operador/dashboard.js';

// Inclui o conteúdo HTML da página.
// A função view() que chama este arquivo irá capturar a saída
// e inseri-la na variável $content do layout.php.
include ROOT . 'app/Views/operador/dashboard.html';

