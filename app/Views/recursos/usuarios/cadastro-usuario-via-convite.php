<?php
// /app/Views/recursos/usuarios/cadastro-usuario-via-convite.php

$logo_icone = '➕';
$logo_titulo = 'Cadastro via Convite';
$page_js_module = '/projetos/dashboard/public/js/recursos/usuarios/cadastro-usuario-via-convite.js';

// Garantir que o CSS seja carregado
if (!defined('CSS_PADRAO')) {
    define('CSS_PADRAO', '/projetos/dashboard/public/css/style.css');
}

// Inclui o conteúdo HTML da página.
// A função view() que chama este arquivo irá capturar a saída
// e inseri-la na variável $content do layout.php.
include ROOT . 'app/Views/recursos/usuarios/cadastro-usuario-via-convite.html';