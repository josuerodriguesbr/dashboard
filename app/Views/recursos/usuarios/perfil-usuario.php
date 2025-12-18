<?php
// /app/Views/recursos/usuarios/perfil-usuario.php

$logo_icone = '👤';
$logo_titulo = ' Meu Perfil';

// Carregar o módulo JS público (seguindo a mesma convenção do cadastro)
$page_js_module = '/projetos/dashboard/public/js/recursos/usuarios/perfil-usuario.js';

// Inclui o conteúdo HTML da página.
// A função view() que chama este arquivo irá capturar a saída
// e inseri-la na variável $content do layout.php.
include ROOT . 'app/Views/recursos/usuarios/perfil-usuario.html';