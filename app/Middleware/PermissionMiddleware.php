<?php
// app/Middleware/PermissionMiddleware.php
namespace App\Middleware;

use App\Utils\UserContext;

class PermissionMiddleware
{
    public static function verificarNivel($nivelRequirido)
    {
        // Define a hierarquia de permissões explicitamente
        $hierarquia = [
            'cliente' => 1,
            'vendedor' => 2,
            'operador' => 3,
            'assinante' => 4,
            'admin' => 5,
        ];
        
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Obter o nível ativo do contexto do usuário
            $nivelAtivo = UserContext::getNivelAtivo() ?? 'cliente';
            
            // Verifica se o nível do usuário e o nível requerido existem na hierarquia
            if (!isset($hierarquia[$nivelAtivo]) || !isset($hierarquia[$nivelRequirido])) {
                throw new \Exception('Nível de permissão inválido.');
            }
            
            // Compara os níveis numéricos
            if ($hierarquia[$nivelAtivo] < $hierarquia[$nivelRequirido]) {
                throw new \Exception('Acesso negado. Permissões insuficientes.');
            }
            
            return $usuario;
        } catch (\Exception $e) {
            header('Location: /projetos/dashboard/');
            exit;
        }
    }
    
    public static function verificarPapel($papelRequerido)
    {
        // Define a hierarquia de permissões explicitamente
        $hierarquia = [
            'cliente' => 1,
            'vendedor' => 2,
            'operador' => 3,
            'assinante' => 4,
            'admin' => 5,
        ];
        
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Obtém todos os papéis do usuário do banco de dados
            $papeisUsuario = \App\Models\Usuario::getPapeisDoUsuario($usuario['id']);
            
            if (empty($papeisUsuario)) {
                throw new \Exception('Usuário não possui papéis atribuídos.');
            }
            
            // Verificar se o usuário tem o papel requerido
            $temPapel = false;
            $papelAtivo = false;
            
            foreach ($papeisUsuario as $papel) {
                // Verifica se tem o papel requerido
                if ($papel['nivel'] === $papelRequerido) {
                    $temPapel = true;
                    // Verifica se o perfil está ativo
                    if ($papel['perfil_status'] === 'Ativo') {
                        $papelAtivo = true;
                        $usuario['perfil_atual'] = $papel;
                    }
                }
            }
            
            if (!$temPapel) {
                throw new \Exception('Usuário não possui o papel requerido.');
            }
            
            if (!$papelAtivo) {
                throw new \Exception('Perfil bloqueado.');
            }
            
            // Atualiza os dados do usuário com o papel
            $usuario['nivel'] = $papelRequerido;
            $usuario['perfil_status'] = $usuario['perfil_atual']['perfil_status'];
            $usuario['perfil_creditos'] = $usuario['perfil_atual']['creditos'];
            
            // Atualizar o contexto do usuário
            UserContext::setNivelAtivo($papelRequerido);
            UserContext::setPerfilAtivo($usuario['perfil_atual']);
            
            return $usuario;
        } catch (\Exception $e) {
            header('Location: /projetos/dashboard/');
            exit;
        }
    }
    
    public static function verificarQualquerPapel($papeisRequeridos)
    {
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Obtém todos os papéis do usuário do banco de dados
            $papeisUsuario = \App\Models\Usuario::getPapeisDoUsuario($usuario['id']);
            
            if (empty($papeisUsuario)) {
                throw new \Exception('Usuário não possui papéis atribuídos.');
            }
            
            // Verificar se o usuário tem algum dos papéis requeridos
            $temPapel = false;
            $papelAtivo = false;
            $papelEncontrado = null;
            
            foreach ($papeisUsuario as $papelUsuario) {
                // Verifica se tem algum dos papéis requeridos
                if (in_array($papelUsuario['nivel'], $papeisRequeridos)) {
                    $temPapel = true;
                    // Verifica se o perfil está ativo
                    if ($papelUsuario['perfil_status'] === 'Ativo') {
                        $papelAtivo = true;
                        $papelEncontrado = $papelUsuario;
                    }
                }
            }
            
            if (!$temPapel) {
                throw new \Exception('Usuário não possui nenhum dos papéis requeridos.');
            }
            
            if (!$papelAtivo) {
                throw new \Exception('Perfil bloqueado.');
            }
            
            // Atualiza os dados do usuário com o papel
            $usuario['nivel'] = $papelEncontrado['nivel'];
            $usuario['perfil_status'] = $papelEncontrado['perfil_status'];
            $usuario['perfil_creditos'] = $papelEncontrado['creditos'];
            $usuario['perfil_atual'] = $papelEncontrado;
            
            // Atualizar o contexto do usuário
            UserContext::setNivelAtivo($papelEncontrado['nivel']);
            UserContext::setPerfilAtivo($papelEncontrado);
            
            return $usuario;
        } catch (\Exception $e) {
            header('Location: /projetos/dashboard/');
            exit;
        }
    }
}