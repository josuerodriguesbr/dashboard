<?php
// /app/Views/recursos/usuarios/cadastro-usuario.php

$title = '➕ Cadastrar Usuário';

// Definindo caminhos para os assets
$page_js_module = '/projetos/dashboard/public/js/recursos/usuarios/cadastro-usuario.js';

// Inclui o layout
ob_start();
include ROOT . 'app/Views/recursos/usuarios/cadastro-usuario.html';
$content = ob_get_clean();

include ROOT . 'app/Views/layout.php';