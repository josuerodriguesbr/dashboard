<?php
// app/Config/jwt.php

// Garantir que ROOT esteja definido
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

$dotenv = ROOT . '.env';
if (file_exists($dotenv)) {
    $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Definir valores padrão caso não consiga ler do .env
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? 'fallback_inseguro');
define('JWT_EXPIRE', (int)($_ENV['JWT_EXPIRE'] ?? getenv('JWT_EXPIRE') ?? 7200)); // 2 horas como padrão