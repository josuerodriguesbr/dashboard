<?php
// app/Views/vendedor/dashboard.php

$logo_icone = '📊';
$logo_titulo = 'Dashboard Vendedor';
$page_js_module = '/projetos/dashboard/public/js/vendedor/dashboard.js';

// Inclui o conteúdo HTML da página.
// A função view() que chama este arquivo irá capturar a saída
// e inseri-la na variável $content do layout.php.
include ROOT . 'app/Views/vendedor/dashboard.html';