<?php
// app/Views/cliente/dashboard.php

$logo_icone = '📊';
$logo_titulo = 'Dashboard Cliente';
$page_js_module = '/projetos/dashboard/public/js/cliente/dashboard.js';

// Inclui o conteúdo HTML da página.
// A função view() que chama este arquivo irá capturar a saída
// e inseri-la na variável $content do layout.php.
include ROOT . 'app/Views/cliente/dashboard.html';