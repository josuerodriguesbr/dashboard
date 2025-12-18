<?php
// app/Utils/UserContext.php
namespace App\Utils;

class UserContext
{
    private static $nivelAtivo = null;
    private static $usuario = null;
    private static $perfilAtivo = null;

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

    /**
     * Define o perfil ativo do usuário
     *
     * @param array $perfil
     * @return void
     */
    public static function setPerfilAtivo($perfil)
    {
        self::$perfilAtivo = $perfil;
    }

    /**
     * Obtém o perfil ativo do usuário
     *
     * @return array|null
     */
    public static function getPerfilAtivo()
    {
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
        self::$usuario = null;
        self::$perfilAtivo = null;
    }
}