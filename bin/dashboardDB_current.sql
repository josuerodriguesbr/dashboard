DROP TABLE IF EXISTS `integra_carteira`;
CREATE TABLE `integra_carteira` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `saldo_atual` int(11) DEFAULT 0,
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_usuario_carteira` (`usuario_id`),
  CONSTRAINT `fk_carteira_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_cred_trans_tipos`;
CREATE TABLE `integra_cred_trans_tipos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `multiplicador` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_credito_transacoes`;
CREATE TABLE `integra_credito_transacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `valor_nominal` int(11) NOT NULL,
  `saldo_apos` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `recarga_id` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_trans_usuario_orig` (`usuario_id`),
  KEY `fk_trans_tipo_orig` (`tipo_id`),
  KEY `fk_trans_recarga_orig` (`recarga_id`),
  CONSTRAINT `fk_trans_recarga_orig` FOREIGN KEY (`recarga_id`) REFERENCES `integra_recargas` (`id`),
  CONSTRAINT `fk_trans_tipo_orig` FOREIGN KEY (`tipo_id`) REFERENCES `integra_cred_trans_tipos` (`id`),
  CONSTRAINT `fk_trans_usuario_orig` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_itens_adquiridos`;
CREATE TABLE `integra_itens_adquiridos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `recurso_id` int(11) NOT NULL,
  `quantidade_restante` int(11) DEFAULT 0,
  `data_expiracao` datetime DEFAULT NULL,
  `status` enum('ativo','expirado','esgotado') DEFAULT 'ativo',
  `createdAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_itens_usuario_orig` (`usuario_id`),
  KEY `fk_itens_recurso_orig` (`recurso_id`),
  CONSTRAINT `fk_itens_recurso_orig` FOREIGN KEY (`recurso_id`) REFERENCES `integra_recursos` (`id`),
  CONSTRAINT `fk_itens_usuario_orig` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_logs`;
CREATE TABLE `integra_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuarioId` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `userAgent` text DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_logs_usuario_acao` (`usuarioId`),
  CONSTRAINT `fk_logs_usuario_acao` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_logs_gateway`;
CREATE TABLE `integra_logs_gateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recarga_id` int(11) NOT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `payload_envio` longtext DEFAULT NULL,
  `payload_retorno` longtext DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `event_type` varchar(50) DEFAULT NULL COMMENT 'Ex: PAYMENT_CONFIRMED, PAYMENT_RECEIVED',
  `raw_body` longtext DEFAULT NULL COMMENT 'O JSON completo enviado pelo Asaas',
  PRIMARY KEY (`id`),
  KEY `fk_logs_gateway_recarga` (`recarga_id`),
  CONSTRAINT `fk_logs_gateway_recarga` FOREIGN KEY (`recarga_id`) REFERENCES `integra_recargas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_papeis`;
CREATE TABLE `integra_papeis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nivel` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nivel` (`nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_perfis`;
CREATE TABLE `integra_perfis` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_recargas`;
CREATE TABLE `integra_recargas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `valor_reais` decimal(10,2) NOT NULL,
  `quantidade_creditos` int(11) NOT NULL,
  `status` enum('pendente','pago','cancelado','estornado') DEFAULT 'pendente',
  `txid` varchar(100) DEFAULT NULL,
  `qrCode` text DEFAULT NULL,
  `external_id` varchar(255) DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `asaas_id` varchar(100) DEFAULT NULL COMMENT 'ID da cobrança no Asaas (pay_...)',
  `invoice_url` text DEFAULT NULL COMMENT 'Link da fatura para o cliente',
  `payment_date` datetime DEFAULT NULL COMMENT 'Data da confirmação enviada pelo Asaas',
  PRIMARY KEY (`id`),
  KEY `fk_recargas_usuario_original` (`usuario_id`),
  KEY `idx_asaas_id` (`asaas_id`),
  CONSTRAINT `fk_recargas_usuario_original` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_recursos`;
CREATE TABLE `integra_recursos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `tipo_cobranca` enum('unidade','tempo') NOT NULL,
  `preco_creditos` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_sessoes`;
CREATE TABLE `integra_sessoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuarioId` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_sessao_usuario` (`usuarioId`),
  CONSTRAINT `fk_sessao_usuario` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `integra_usuarios`;
CREATE TABLE `integra_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT 'Vínculo: Vendedor -> Assinante',
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `asaas_id` varchar(50) DEFAULT NULL,
  `idPerfilAtivo` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_usuario_parent` (`parent_id`),
  CONSTRAINT `fk_usuario_parent` FOREIGN KEY (`parent_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

