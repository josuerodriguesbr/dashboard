<?php
// bin/reorder_schema.php

$inputFile = __DIR__ . '/vendasysDB.sql';
$outputFile = __DIR__ . '/vendasysDB.sql'; // Overwrite

$content = file_get_contents($inputFile);

// Split by "-- Table structure" or just by "DROP TABLE"
// My export script used "DROP TABLE IF EXISTS" as separator mostly.
$statements = explode('DROP TABLE IF EXISTS', $content);

$tables = [];
foreach ($statements as $stmt) {
    if (trim($stmt) == '') continue;
    
    // Extract table name
    if (preg_match('/CREATE TABLE `(\w+)`/', $stmt, $matches)) {
        $tableName = $matches[1];
        $tables[$tableName] = "DROP TABLE IF EXISTS" . $stmt;
    }
}

// Order of creation
$order = [
    'papeis',
    'cred_trans_tipos',
    'recursos',
    'usuarios',
    'carteira',
    'recargas',
    'logs',
    'perfis',
    'sessoes',
    'itens_adquiridos',
    'logs_gateway',
    'credito_transacoes'
];

$newContent = "";
foreach ($order as $table) {
    if (isset($tables[$table])) {
        $newContent .= $tables[$table];
        unset($tables[$table]); // Remove processed
    }
}

// Add remaining (if any)
foreach ($tables as $name => $sql) {
    $newContent .= $sql;
}

file_put_contents($outputFile, $newContent);
echo "Schema reordenado em: $outputFile\n";
