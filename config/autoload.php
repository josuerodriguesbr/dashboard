<?php
// /projetos/dashboard/config/autoload.php

define('ROOT', dirname(__DIR__) . '/');

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

require_once ROOT . 'config/jwt.php';