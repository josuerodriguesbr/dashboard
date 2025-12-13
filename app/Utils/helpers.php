<?php
// /app/Utils/helpers.php

// Função para obter a URL base dinamicamente
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // Remove o nome do script e ajusta o caminho
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = str_replace('/index.php', '', $scriptName);
    
    return $protocol . '://' . $host . $basePath;
}

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

if (!function_exists('getRotaPorUserNivel')) {
    function getRotaPorUserNivel($nivel) {
        $basePath = '/projetos/dashboard';

        switch ($nivel) {
            case 'admin':
                return $basePath . '/admin';
            case 'assinante':
                return $basePath . '/assinante';
            case 'vendedor':
                return $basePath . '/vendedor';
            case 'cliente':
            default:
                return $basePath . '/cliente';
        }
    }
}

}