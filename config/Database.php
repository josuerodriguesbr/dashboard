<?php
// config/Database.php

namespace Config;

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {

            // Tenta ler do config.json
            $configFile = ROOT . 'config.json';
            $config = [];

            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
            }

            // Padrões se não houver config (dev local seguro)
            $dbConfig = $config['database'] ?? [
                'host' => 'localhost',
                'name' => 'dashboard_db',
                'user' => 'root',
                'password' => ''
            ];

            // Detecção de ambiente via config ou fallback
            // (A detecção automática baseada em HTTP_HOST foi removida em favor do config.json)

            try {
                $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4";
                if (isset($dbConfig['port'])) {
                    $dsn .= ";port={$dbConfig['port']}";
                }

                self::$instance = new \PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
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