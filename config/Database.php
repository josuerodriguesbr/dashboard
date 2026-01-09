<?php
// config/Database.php

namespace Config;

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {

            /*
            $dsn = "mysql:host=localhost;dbname=u748224509_bingosys;charset=utf8mb4";
            $usuario = "u748224509_bingosys";
            $senha = "bingosys2020";
            */
            
            $dsn = "mysql:host=localhost;dbname=dashboard;charset=utf8mb4";
            $usuario = "root";
            $senha = "";

            // Detecção de ambiente VPS (Produção)
            if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vendasys.com.br') !== false) {
                $dsn = "mysql:host=localhost;dbname=vendasys;charset=utf8mb4";
                $usuario = "josuerodrigues";
                $senha = "RootJRP@2026";
            }

            try {
                self::$instance = new \PDO($dsn, $usuario, $senha, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (\PDOException $e) {
                error_log("Erro de banco: " . $e->getMessage());
                http_response_code(500);
                die("Erro interno de banco de dados.");
            }
        }

        return self::$instance;
    }

// Dentro de Database.php

    private static function getConfig()
    {
        // Em produção, pode vir de variáveis de ambiente
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'dashboard';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        return [
            'dsn' => "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            'user' => $user,
            'pass' => $pass
        ];
    }    
}