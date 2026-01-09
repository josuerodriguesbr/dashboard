<?php
// /app/Utils/helpers.php

// Função para obter o caminho base (path) relativo ao domínio
if (!function_exists('getBasePath')) {
    function getBasePath() {
        if (php_sapi_name() === 'cli') {
            return '/projetos/dashboard'; 
        }
        $scriptName = $_SERVER['SCRIPT_NAME'];
        return str_replace('/index.php', '', $scriptName);
    }
}

// Função para obter a URL base dinamicamente
function getBaseUrl() {
    // Check if running from CLI
    if (php_sapi_name() === 'cli') {
        return 'http://localhost' . getBasePath(); 
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    return $protocol . '://' . $host . getBasePath();
}

if (!function_exists('cleanCPF')) {
    /**
     * Remove caracteres não numéricos do CPF e retorna null se inválido
     */
    function cleanCPF($cpf)
    {
        if (!$cpf) return null;
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) {
            error_log("[Helpers] CPF com número incorreto de dígitos: '$cpf'");
            return null;
        }
        return $cpf;
    }
}

if (!function_exists('isValidCPF')) {
    /**
     * Valida CPF com dígitos verificadores
     */
    function isValidCPF($cpf)
    {
        if (!$cpf) return false;
        if (preg_match('/^(\d)\1+$/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }
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
        $base = getBasePath();
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
        $basePath = getBasePath();
        
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
        $basePath = getBasePath();
        
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