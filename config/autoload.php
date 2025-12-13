<?php
// /projetos/dashboard/config/autoload.php

define('ROOT', dirname(__DIR__) . '/');

// === Carrega funções globais primeiro ===
require_once ROOT . 'app/Utils/helpers.php';

// Definições de caminhos públicos (acessíveis por usuários não logados)
define('FULL_BASE_URL', getBaseUrl()); // URL completa dinâmica
define('CSS_PATH', FULL_BASE_URL . '/public/css/style.css');
define('JS_PATH', FULL_BASE_URL . '/public/js/tema.js');

//var_dump(CSS_PATH);
//exit();

// Autoload para classes do namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT . 'app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// Autoload para classes do namespace Config\
spl_autoload_register(function ($class) {
    $prefix = 'Config\\';
    $base_dir = ROOT . 'config/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require_once ROOT . 'config/jwt.php';