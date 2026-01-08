<?php
// app/Utils/UserContext.php
namespace App\Utils;

class UserContext
{
    private static $nivelAtivo = null;
    private static $perfilAtivo = null;
    private static $usuario = null;

    /**
     * Define o nível ativo do usuário
     *
     * @param string $nivel
     * @return void
     */
    public static function setNivelAtivo($nivel)
    {
        self::$nivelAtivo = $nivel;
    }

    /**
     * Obtém o nível ativo do usuário
     *
     * @return string|null
     */
    public static function getNivelAtivo()
    {
        // Se nivelAtivo não estiver setado, tenta pegar do usuario ou perfil
        if (self::$nivelAtivo === null) {
            if (isset(self::$perfilAtivo['papel_nivel'])) {
                return self::$perfilAtivo['papel_nivel'];
            }
            if (isset(self::$usuario['papel_nivel'])) {
                return self::$usuario['papel_nivel'];
            }
            if (isset(self::$usuario['nivel'])) {
                return self::$usuario['nivel'];
            }
        }
        return self::$nivelAtivo;
    }

    /**
     * Define os dados do usuário logado
     *
     * @param array $usuario
     * @return void
     */
    public static function setUsuario($usuario)
    {
        self::$usuario = $usuario;
        // Tenta inferir nivel
        if (isset($usuario['papel_nivel'])) {
            self::$nivelAtivo = $usuario['papel_nivel'];
        } elseif (isset($usuario['nivel'])) {
            self::$nivelAtivo = $usuario['nivel'];
        }
    }

    /**
     * Obtém os dados do usuário logado
     *
     * @return array|null
     */
    public static function getUsuario()
    {
        return self::$usuario;
    }

    public static function setPerfilAtivo($perfil) {
        self::$perfilAtivo = $perfil;
        if (isset($perfil['papel_nivel'])) {
            self::$nivelAtivo = $perfil['papel_nivel'];
        }
    }
    
    public static function getPerfilAtivo() { 
        return self::$perfilAtivo; 
    }

    /**
     * Limpa todos os dados do contexto do usuário
     *
     * @return void
     */
    public static function limpar()
    {
        self::$nivelAtivo = null;
        self::$perfilAtivo = null;
        self::$usuario = null;
    }
}