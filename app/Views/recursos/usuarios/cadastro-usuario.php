<?php
// /app/Views/recursos/usuarios/cadastro-usuario.php

$logo_icone = '➕';
$logo_titulo = 'Cadastro Usuário';
$page_js_module = '/projetos/dashboard/public/js/recursos/usuarios/cadastro-usuario.js';

// Garantir que o CSS seja carregado
if (!defined('CSS_PADRAO')) {
    define('CSS_PADRAO', '/projetos/dashboard/public/css/style.css');
}

// Inclui o conteúdo HTML da página.
// A função view() que chama este arquivo irá capturar a saída
// e inseri-la na variável $content do layout.php.
include ROOT . 'app/Views/recursos/usuarios/cadastro-usuario.html';