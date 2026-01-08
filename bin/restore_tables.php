<?php
// bin/restore_tables.php
require_once __DIR__ . '/../config/autoload.php';

try {
    $pdo = \Config\Database::getConnection();
    echo "Conectado ao banco de dados.\n";

    // 1. Criar integra_papeis
    $sqlPapeis = "
    CREATE TABLE IF NOT EXISTS `integra_papeis` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nivel` varchar(50) NOT NULL,
      `descricao` text DEFAULT NULL,
      `createdAt` datetime DEFAULT current_timestamp(),
      `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_nivel` (`nivel`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlPapeis);
    echo "Tabela integra_papeis verificada/criada.\n";

    // 2. Criar integra_perfis
    $sqlPerfis = "
    CREATE TABLE IF NOT EXISTS `integra_perfis` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_papel` int(11) NOT NULL,
      `id_usuario` int(11) NOT NULL,
      `hashConvite` varchar(255) DEFAULT NULL,
      `hashAnfitriao` varchar(255) DEFAULT NULL,
      `status` enum('Ativo','Bloqueado') DEFAULT 'Ativo',
      `createdAt` datetime DEFAULT current_timestamp(),
      `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_id_papel` (`id_papel`),
      KEY `idx_id_usuario` (`id_usuario`),
      CONSTRAINT `fk_perfis_papel` FOREIGN KEY (`id_papel`) REFERENCES `integra_papeis` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_perfis_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlPerfis);
    echo "Tabela integra_perfis verificada/criada.\n";

    // 3. Alterar integra_usuarios (adicionar IDPerfilAtivo)
    // Verifica se colunas existem antes de alterar
    $stmt = $pdo->query("SHOW COLUMNS FROM integra_usuarios LIKE 'idPerfilAtivo'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `integra_usuarios` ADD COLUMN `idPerfilAtivo` int(11) DEFAULT NULL");
        $pdo->exec("ALTER TABLE `integra_usuarios` ADD CONSTRAINT `fk_usuario_perfil_ativo` FOREIGN KEY (`idPerfilAtivo`) REFERENCES `integra_perfis` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        echo "Tabela integra_usuarios alterada (idPerfilAtivo adicionado).\n";
    } else {
        echo "Tabela integra_usuarios já possui idPerfilAtivo.\n";
    }

    // 4. Trigger
    $pdo->exec("DROP TRIGGER IF EXISTS `trg_unico_perfil_por_papel`");
    $sqlTrigger = "
    CREATE TRIGGER `trg_unico_perfil_por_papel` BEFORE INSERT ON `integra_perfis` FOR EACH ROW BEGIN
        DECLARE perfil_existente INT;
        SELECT COUNT(*) INTO perfil_existente
        FROM integra_perfis p
        JOIN integra_papeis pa ON p.id_papel = pa.id
        WHERE p.id_usuario = NEW.id_usuario 
        AND pa.id = NEW.id_papel;
        IF perfil_existente > 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Usuário já possui um perfil com este tipo de papel';
        END IF;
    END
    ";
    $pdo->exec($sqlTrigger);
    echo "Trigger trg_unico_perfil_por_papel criada.\n";
    
    echo "\nEstrutura do banco de dados restaurada com sucesso.\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
