<?php
// /app/Views/recursos/usuarios/perfil-usuario.php

$title = 'Meu Perfil';

// Definindo caminhos para os assets
$page_css = '/projetos/dashboard/app/Views/recursos/usuarios/perfil-usuario.css';
$page_js = '/projetos/dashboard/app/Views/recursos/usuarios/perfil-usuario.js';

// Inclui o layout
ob_start();
include ROOT . 'app/Views/recursos/usuarios/perfil-usuario.html';
$content = ob_get_clean();

include ROOT . 'app/Views/layout.php';