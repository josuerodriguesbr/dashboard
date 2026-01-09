<?php
// /projetos/dashboard/bin/reset_db_full.php

require_once __DIR__ . '/../config/autoload.php';

try {
    $pdo = \Config\Database::getConnection();

    echo "⚠️  ATENÇÃO: INICIANDO O RESET COMPLETO DO BANCO DE DADOS ⚠️\n";
    echo "Isso apagará TODOS os dados. Você tem 3 segundos para cancelar (Ctrl+C)...\n";
    sleep(3);

    // Desativar verificação de chave estrangeira
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Buscar todas as tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Encontradas " . count($tables) . " tabelas para remover.\n";

    foreach ($tables as $table) {
        echo "Removendo tabela: $table... ";
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "OK\n";
    }

    // Reativar verificação de chave estrangeira
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n✅ Todas as tabelas foram removidas.\n";
    echo "\n🔄 Importando novo schema (vendasysDB.sql)...\n";
    
    $sql = file_get_contents(__DIR__ . '/vendasysDB.sql');
    $pdo->exec($sql);
    echo "✅ Schema importado com sucesso!\n";

    echo "\n🔄 Iniciando reinicialização dos dados padrão (init_data.php)...\n";
    echo "--------------------------------------------------------\n";

    // Chama o script de inicialização
    require __DIR__ . '/init_data.php';

    echo "--------------------------------------------------------\n";
    echo "✅ Reset completo finalizado!\n";

} catch (Exception $e) {
    echo "\n❌ Erro Fatal: " . $e->getMessage() . "\n";
    // Tenta reativar as foreign keys pro caso de erro no meio do caminho
    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch(Exception $x) {}
}
