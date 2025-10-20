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

/*
if (!function_exists('view')) {
     // Carrega uma view e exibe como HTML
    function view($name, $data = []) {
        $file = ROOT . 'app/Views/' . $name . '.php';
        if (file_exists($file)) {
            header('Content-Type: text/html; charset=utf-8');
            extract($data);
            include $file;
            exit;
        }
        http_response_code(500);
        echo "View X não encontrada: $file";
        exit;
    }
}
*/

if (!function_exists('view')) {
function view($viewName, $data = []) {
    // Verificar se a verificação de autenticação deve ser ignorada
    $ignorarAutenticacao = isset($data['ignorarAutenticacao']) && $data['ignorarAutenticacao'] === true;
    
    // Obter os dados do usuário logado usando o middleware existente (se não ignorar autenticação)
    if (!$ignorarAutenticacao) {
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificarOuFalse();
            $data['usuario'] = $usuario;
        } catch (Exception $e) {
            $data['usuario'] = false;
        }
    } else {
        $data['usuario'] = false;
    }
    
    // Extrair os dados para variáveis
    extract($data);
    
    // Verificar se a página deve ser renderizada sem layout (para iframes)
    $semLayout = isset($semLayout) && $semLayout === true;
    
    // Caminho para o arquivo de view
    $viewFile = 'app/Views/' . $viewName . '.php';
    
    // Verificar se o arquivo da view existe
    if (file_exists($viewFile)) {
        // Iniciar buffer de saída para a view
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        // Se não estiver em modo "sem layout", incluir o layout
        if (!$semLayout) {
            $layoutFile = 'app/Views/layout.php';
            include $layoutFile;
        } else {
            // Se estiver em modo "sem layout", apenas retornar o conteúdo
            echo $content;
        }
    } else {
        // Caso a view não exista
        echo "View não encontrada: " . $viewFile;
    }
}

}