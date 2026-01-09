<?php
// bin/convert_schema_prefix.php

$inputFile = __DIR__ . '/dashboardDB_current.sql';
$outputFile = __DIR__ . '/vendasysDB.sql';

if (!file_exists($inputFile)) {
    die("Arquivo de entrada não encontrado: $inputFile\n");
}

$content = file_get_contents($inputFile);

// Substituir 'CREATE TABLE `integra_' por 'CREATE TABLE `'
$content = str_replace('CREATE TABLE `integra_', 'CREATE TABLE `', $content);

// Substituir 'CREATE TABLE IF NOT EXISTS `integra_' por 'CREATE TABLE IF NOT EXISTS `'
$content = str_replace('CREATE TABLE IF NOT EXISTS `integra_', 'CREATE TABLE IF NOT EXISTS `', $content);

// Substituir referências de chaves estrangeiras e inserts: '`integra_'
$content = str_replace('`integra_', '`', $content);

// Substituir referências sem backticks (se houver, mas sql dump costuma usar backticks)
// OBS: É perigoso dar replace em 'integra_' solto, pois pode haver colunas ou dados.
// O dump do PHP PDO geralmente usa backticks em tabelas.

// Vamos salvar
file_put_contents($outputFile, $content);
echo "Arquivo convertido salvo em: $outputFile\n";
