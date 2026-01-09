<?php
// app/Views/admin/dashboard.php

$logo_icone = '📊';
$logo_titulo = 'Dashboard Admin';
$page_js_module = getBasePath() . '/public/js/admin/dashboard.js';

// Inclui o conteúdo HTML da página.
// A função view() que chama este arquivo irá capturar a saída
// e inseri-la na variável $content do layout.php.
include ROOT . 'app/Views/admin/dashboard_content.php';