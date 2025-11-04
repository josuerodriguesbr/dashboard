<?php
// /projetos/dashboard/public/autoload.php

// Definir ROOT apenas uma vez e antes de usá-lo
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__) . '/');
}

// Carregar configurações JWT primeiro
require_once ROOT . 'app/Config/jwt.php';

// === Carrega funções globais primeiro ===
require_once ROOT . 'app/Utils/helpers.php'; // ou functions.php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT . 'app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// Remover a linha duplicada que tenta carregar novamente o jwt.php
// require_once ROOT . 'config/jwt.php'; // Esta linha deve ser removida