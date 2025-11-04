<?php
// /app/Views/recursos/usuarios/perfil-usuario.php

// Definindo caminhos para os assets
$page_css = '/projetos/dashboard/public/css/perfil-usuario.css';
$page_js = '/projetos/dashboard/public/js/perfil-usuario.js';

// Verificar se o arquivo HTML existe antes de incluir
$htmlFile = ROOT . 'app/Views/recursos/usuarios/perfil-usuario.html';

if (file_exists($htmlFile)) {
    // Este conteúdo será inserido no layout através da variável $content
    ob_start();
    include $htmlFile;
    $content = ob_get_clean();
} else {
    // Conteúdo de fallback caso o arquivo não exista
    $content = '<div class="form-wrapper"><p>Formulário não encontrado.</p></div>';
}

// Não incluímos mais o layout aqui diretamente
// O sistema de layouts novo cuidará disso
return $content;
?>