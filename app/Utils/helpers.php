<?php
// /app/Utils/helpers.php

function json_response($data, $status = 200) {
    // 🔥 Força o envio imediato
    while (ob_get_level()) {
        ob_end_flush(); // Libera todos os buffers
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Garante que será enviado
    echo $json;
    flush(); // Força o envio para o navegador
    exit;
}

if (!function_exists('redirect')) {
    /**
     * Redireciona para uma URL relativa, considerando subdiretórios
     */
    function redirect($url) {
        $base = dirname($_SERVER['SCRIPT_NAME']);
        $base = ($base === '/' || $base === '\\') ? '' : $base;
        header('Location: ' . $base . $url);
        exit;
    }
}

if (!function_exists('view')) {
    /**
     * Carrega uma view e exibe como HTML
     */
    function view($name, $data = []) {
        $file = ROOT . 'app/Views/' . $name . '.php';
        if (file_exists($file)) {
            header('Content-Type: text/html; charset=utf-8');
            extract($data);
            include $file;
            //exit;
        }
        http_response_code(500);
        echo "View não encontrada: $file";
        exit;
    }
}