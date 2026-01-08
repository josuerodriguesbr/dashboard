-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 04-Jan-2026 às 12:26
-- Versão do servidor: 10.4.25-MariaDB
-- versão do PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `dashboard`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_carteira`
--

CREATE TABLE `integra_carteira` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `saldo_atual` int(11) DEFAULT 0,
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_credito_transacoes`
--

CREATE TABLE `integra_credito_transacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `valor_nominal` int(11) NOT NULL,
  `saldo_apos` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `recarga_id` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_cred_trans_tipos`
--

CREATE TABLE `integra_cred_trans_tipos` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `multiplicador` tinyint(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_itens_adquiridos`
--

CREATE TABLE `integra_itens_adquiridos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `recurso_id` int(11) NOT NULL,
  `quantidade_restante` int(11) DEFAULT 0,
  `data_expiracao` datetime DEFAULT NULL,
  `status` enum('ativo','expirado','esgotado') DEFAULT 'ativo',
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_logs`
--

CREATE TABLE `integra_logs` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `userAgent` text DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_logs_gateway`
--

CREATE TABLE `integra_logs_gateway` (
  `id` int(11) NOT NULL,
  `recarga_id` int(11) NOT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `payload_envio` longtext DEFAULT NULL,
  `payload_retorno` longtext DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `event_type` varchar(50) DEFAULT NULL COMMENT 'Ex: PAYMENT_CONFIRMED, PAYMENT_RECEIVED',
  `raw_body` longtext DEFAULT NULL COMMENT 'O JSON completo enviado pelo Asaas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_recargas`
--

CREATE TABLE `integra_recargas` (
  `id` int(11) NOT NULL,
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
  `payment_date` datetime DEFAULT NULL COMMENT 'Data da confirmação enviada pelo Asaas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_recursos`
--

CREATE TABLE `integra_recursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo_cobranca` enum('unidade','tempo') NOT NULL,
  `preco_creditos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_sessoes`
--

CREATE TABLE `integra_sessoes` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_papeis`
--

CREATE TABLE `integra_papeis` (
  `id` int(11) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_perfis`
--

CREATE TABLE `integra_perfis` (
  `id` int(11) NOT NULL,
  `id_papel` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `hashConvite` varchar(255) DEFAULT NULL,
  `hashAnfitriao` varchar(255) DEFAULT NULL,
  `status` enum('Ativo','Bloqueado') DEFAULT 'Ativo',
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Acionadores `integra_perfis`
--
DELIMITER $$
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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_usuarios`
--

CREATE TABLE `integra_usuarios` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'Vínculo: Vendedor -> Assinante',
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `idPerfilAtivo` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `integra_carteira`
--
ALTER TABLE `integra_carteira`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_usuario_carteira` (`usuario_id`);

--
-- Índices para tabela `integra_credito_transacoes`
--
ALTER TABLE `integra_credito_transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trans_usuario_orig` (`usuario_id`),
  ADD KEY `fk_trans_tipo_orig` (`tipo_id`),
  ADD KEY `fk_trans_recarga_orig` (`recarga_id`);

--
-- Índices para tabela `integra_cred_trans_tipos`
--
ALTER TABLE `integra_cred_trans_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `integra_itens_adquiridos`
--
ALTER TABLE `integra_itens_adquiridos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_itens_usuario_orig` (`usuario_id`),
  ADD KEY `fk_itens_recurso_orig` (`recurso_id`);

--
-- Índices para tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_logs_usuario_acao` (`usuarioId`);

--
-- Índices para tabela `integra_logs_gateway`
--
ALTER TABLE `integra_logs_gateway`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_logs_gateway_recarga` (`recarga_id`);

--
-- Índices para tabela `integra_recargas`
--
ALTER TABLE `integra_recargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recargas_usuario_original` (`usuario_id`),
  ADD KEY `idx_asaas_id` (`asaas_id`);

--
-- Índices para tabela `integra_recursos`
--
ALTER TABLE `integra_recursos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sessao_usuario` (`usuarioId`);

--
-- Índices para tabela `integra_papeis`
--
ALTER TABLE `integra_papeis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nivel` (`nivel`);

--
-- Índices para tabela `integra_perfis`
--
ALTER TABLE `integra_perfis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_papel` (`id_papel`),
  ADD KEY `idx_id_usuario` (`id_usuario`);

--
-- Índices para tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuario_parent` (`parent_id`),
  ADD KEY `fk_usuario_perfil_ativo` (`idPerfilAtivo`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `integra_carteira`
--
ALTER TABLE `integra_carteira`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_credito_transacoes`
--
ALTER TABLE `integra_credito_transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_cred_trans_tipos`
--
ALTER TABLE `integra_cred_trans_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_itens_adquiridos`
--
ALTER TABLE `integra_itens_adquiridos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_logs_gateway`
--
ALTER TABLE `integra_logs_gateway`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_recargas`
--
ALTER TABLE `integra_recargas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_recursos`
--
ALTER TABLE `integra_recursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_papeis`
--
ALTER TABLE `integra_papeis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_perfis`
--
ALTER TABLE `integra_perfis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `integra_carteira`
--
ALTER TABLE `integra_carteira`
  ADD CONSTRAINT `fk_carteira_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_credito_transacoes`
--
ALTER TABLE `integra_credito_transacoes`
  ADD CONSTRAINT `fk_trans_recarga_orig` FOREIGN KEY (`recarga_id`) REFERENCES `integra_recargas` (`id`),
  ADD CONSTRAINT `fk_trans_tipo_orig` FOREIGN KEY (`tipo_id`) REFERENCES `integra_cred_trans_tipos` (`id`),
  ADD CONSTRAINT `fk_trans_usuario_orig` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_itens_adquiridos`
--
ALTER TABLE `integra_itens_adquiridos`
  ADD CONSTRAINT `fk_itens_recurso_orig` FOREIGN KEY (`recurso_id`) REFERENCES `integra_recursos` (`id`),
  ADD CONSTRAINT `fk_itens_usuario_orig` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  ADD CONSTRAINT `fk_logs_usuario_acao` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `integra_logs_gateway`
--
ALTER TABLE `integra_logs_gateway`
  ADD CONSTRAINT `fk_logs_gateway_recarga` FOREIGN KEY (`recarga_id`) REFERENCES `integra_recargas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_recargas`
--
ALTER TABLE `integra_recargas`
  ADD CONSTRAINT `fk_recargas_usuario_original` FOREIGN KEY (`usuario_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_perfis`
--
ALTER TABLE `integra_perfis`
  ADD CONSTRAINT `fk_perfis_papel` FOREIGN KEY (`id_papel`) REFERENCES `integra_papeis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_perfis_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  ADD CONSTRAINT `fk_sessao_usuario` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  ADD CONSTRAINT `fk_usuario_parent` FOREIGN KEY (`parent_id`) REFERENCES `integra_usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_perfil_ativo` FOREIGN KEY (`idPerfilAtivo`) REFERENCES `integra_perfis` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
