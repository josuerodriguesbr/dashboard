<?php
// app/Middleware/PermissionMiddleware.php
namespace App\Middleware;

class PermissionMiddleware
{
    public static function verificarNivel($nivelRequirido)
    {
        // Define a hierarquia de permissões explicitamente
        $hierarquia = [
            'cliente' => 1,
            'vendedor' => 2,
            'assinante' => 3,
            'admin' => 4,
        ];
        
        try {
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Verifica se o nível do usuário e o nível requerido existem na hierarquia
            if (!isset($hierarquia[$usuario['nivel']]) || !isset($hierarquia[$nivelRequirido])) {
                throw new \Exception('Nível de permissão inválido.');
            }
            
            // Compara os níveis numéricos
            if ($hierarquia[$usuario['nivel']] < $hierarquia[$nivelRequirido]) {
                throw new \Exception('Acesso negado. Permissões insuficientes.');
            }
            
            return $usuario;
        } catch (\Exception $e) {
            header('Location: /projetos/dashboard/');
            exit;
        }
    }
}