<?php
// bin/export_current_schema.php

require_once __DIR__ . '/../config/autoload.php';

try {
    $pdo = \Config\Database::getConnection();
    echo "Conectado ao banco. Exportando schema...\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $sql = "";

    foreach ($tables as $table) {
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $createTable['Create Table'] . ";\n\n";
        echo "Tabela exportada: $table\n";
    }

    file_put_contents(__DIR__ . '/dashboardDB_current.sql', $sql);
    echo "Schema salvo em bin/dashboardDB_current.sql\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
