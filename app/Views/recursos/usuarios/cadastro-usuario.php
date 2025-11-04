<?php
// /app/Views/recursos/usuarios/cadastro-usuario.php

$title = 'Cadastro Usuário';

// Definindo caminhos para os assets
//$page_css = '/projetos/dashboard/app/Views/recursos/usuarios/cadastro-usuario.css';
$page_js = '/projetos/dashboard/app/Views/recursos/usuarios/cadastro-usuario.js';

// Inclui o layout
ob_start();
include ROOT . 'app/Views/recursos/usuarios/cadastro-usuario.html';
$content = ob_get_clean();

include ROOT . 'app/Views/layout.php';