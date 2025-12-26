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
    // Limpar todos os buffers de saída sem enviá-los
    while (ob_get_level()) {
        ob_end_clean(); // Descarta o conteúdo do buffer sem enviar
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Garante que será enviado
    echo $json;
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
            $usuario = \App\Middleware\AuthMiddleware::verificar();
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
    /**
     * Retorna a rota do dashboard com base no nível do usuário
     *
     * @param string $nivel Nível do usuário
     * @return string Rota do dashboard
     */
    function getRotaPorUserNivel($nivel) {
        $basePath = '/projetos/dashboard';
        
        // Verificar se o nível foi fornecido
        if (empty($nivel)) {
            $nivel = 'cliente'; // Valor padrão
        }
        
        switch ($nivel) {
            case 'admin':
                return $basePath . '/admin';
            case 'assinante':
                return $basePath . '/assinante';
            case 'operador':
                return $basePath . '/operador';                
            case 'vendedor':
                return $basePath . '/vendedor';
            case 'cliente':
            default:
                return $basePath . '/cliente';
        }
    }
}

if (!function_exists('getRotaPorPapel')) {
    /**
     * Retorna a rota do dashboard com base no papel do usuário
     *
     * @param string $papel Papel do usuário
     * @return string Rota do dashboard
     */
    function getRotaPorPapel($papel) {
        $basePath = '/projetos/dashboard';
        
        switch ($papel) {
            case 'admin':
                return $basePath . '/admin';
            case 'assinante':
                return $basePath . '/assinante';
            case 'operador':
                return $basePath . '/operador';                
            case 'vendedor':
                return $basePath . '/vendedor';
            case 'cliente':
            default:
                return $basePath . '/cliente';
        }
    }
}

if (!function_exists('getNivelAtivo')) {
    /**
     * Retorna o nível ativo do usuário logado
     *
     * @return string|null Nível ativo do usuário
     */
    function getNivelAtivo() {
        return \App\Utils\UserContext::getNivelAtivo();
    }
}

if (!function_exists('getUsuarioLogado')) {
    /**
     * Retorna os dados do usuário logado
     *
     * @return array|null Dados do usuário logado
     */
    function getUsuarioLogado() {
        return \App\Utils\UserContext::getUsuario();
    }
}

if (!function_exists('getPerfilAtivo')) {
    /**
     * Retorna o perfil ativo do usuário logado
     *
     * @return array|null Perfil ativo do usuário
     */
    function getPerfilAtivo() {
        return \App\Utils\UserContext::getPerfilAtivo();
    }
}

}