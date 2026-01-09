<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Diagnóstico DB</title></head><body style='font-family:sans-serif; padding:20px;'>";
echo "<h1>🕵️ Diagnóstico de Banco de Dados</h1>";

$host = $_SERVER['HTTP_HOST'] ?? 'Não detectado';
echo "<p><strong>HTTP_HOST detectado:</strong> " . htmlspecialchars($host) . "</p>";

// Simulação da lógica do Database.php
$isVps = (strpos($host, 'vendasys.com.br') !== false);
echo "<p><strong>É ambiente VPS?</strong> " . ($isVps ? "<span style='color:green'>SIM</span>" : "<span style='color:red'>NÃO</span>") . "</p>";

if ($isVps) {
    $dsn = "mysql:host=localhost;dbname=vendasys;charset=utf8mb4";
    $usuario = "josuerodrigues";
    $senha = "RootJRP@2026";
    echo "<p>Tentando conectar no banco <code>vendasys</code> com usuário <code>josuerodrigues</code>...</p>";
} else {
    $dsn = "mysql:host=localhost;dbname=dashboard;charset=utf8mb4";
    $usuario = "root";
    $senha = "";
    echo "<p>Tentando conectar no banco <code>dashboard</code> (Localhost)...</p>";
}

try {
    $pdo = new PDO($dsn, $usuario, $senha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<h2 style='color:green'>✅ Conexão BEM SUCEDIDA!</h2>";
    echo "<p>O banco de dados está acessível.</p>";
} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ FALHA NA CONEXÃO</h2>";
    echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    if ($isVps) {
        echo "<hr><h3>Possíveis Causas no VPS:</h3>";
        echo "<ul>";
        echo "<li>Você usou uma senha diferente de <code>SuaSenhaForte123</code> ao criar o usuário?</li>";
        echo "<li>O usuário <code>dashboard_user</code> não foi criado?</li>";
        echo "<li>O banco <code>vendasys</code> não existe?</li>";
        echo "</ul>";
    }
}

echo "</body></html>";
