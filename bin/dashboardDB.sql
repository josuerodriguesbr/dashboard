-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 26-Dez-2025 às 20:03
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
-- Estrutura da tabela `integra_logs`
--

CREATE TABLE `integra_logs` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) DEFAULT NULL,
  `acao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalhes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_pagamentos`
--

CREATE TABLE `integra_pagamentos` (
  `id` int(11) NOT NULL,
  `vendaId` int(11) NOT NULL,
  `metodo` enum('pix','credito','debito','dinheiro','asaas') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('pendente','pago','falhou','estornado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `txid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrCode` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataPagamento` datetime DEFAULT NULL,
  `dadosTransacao` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dadosTransacao`)),
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_papeis`
--

CREATE TABLE `integra_papeis` (
  `id` int(11) NOT NULL,
  `nivel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_perfis`
--

CREATE TABLE `integra_perfis` (
  `id` int(11) NOT NULL,
  `id_papel` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `creditos` decimal(15,2) DEFAULT 0.00,
  `hashConvite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hashAnfitriao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Ativo','Bloqueado') COLLATE utf8mb4_unicode_ci DEFAULT 'Ativo',
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Acionadores `integra_perfis`
--
DELIMITER $$
CREATE TRIGGER `trg_unico_perfil_por_papel` BEFORE INSERT ON `integra_perfis` FOR EACH ROW BEGIN
    DECLARE perfil_existente INT;
    
    -- Verificar se o usuário já tem um perfil com o mesmo tipo de papel
    SELECT COUNT(*) INTO perfil_existente
    FROM integra_perfis p
    JOIN integra_papeis pa ON p.id_papel = pa.id
    WHERE p.id_usuario = NEW.id_usuario 
    AND pa.id = NEW.id_papel;
    
    -- Se já existir um perfil com o mesmo papel, lançar um erro
    IF perfil_existente > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Usuário já possui um perfil com este tipo de papel';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_produtos_servicos`
--

CREATE TABLE `integra_produtos_servicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `tipo` enum('produto','servico') COLLATE utf8mb4_unicode_ci DEFAULT 'produto',
  `ativo` tinyint(1) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_saldo`
--

CREATE TABLE `integra_saldo` (
  `perfil_id` int(11) NOT NULL,
  `saldo` decimal(15,2) NOT NULL DEFAULT 0.00,
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_sessoes`
--

CREATE TABLE `integra_sessoes` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) NOT NULL,
  `token` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiresAt` datetime NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_transacoes`
--

CREATE TABLE `integra_transacoes` (
  `id` int(11) NOT NULL,
  `tipo` enum('entrada','saida','transferencia','expiracao','reembolso','ajuste') COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem_perfil_id` int(11) DEFAULT NULL,
  `destino_perfil_id` int(11) DEFAULT NULL,
  `valor` decimal(15,2) NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia_externa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pendente','confirmado','cancelado','expirado') COLLATE utf8mb4_unicode_ci DEFAULT 'confirmado',
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_usuarios`
--

CREATE TABLE `integra_usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idPerfilAtivo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_vendas`
--

CREATE TABLE `integra_vendas` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) NOT NULL,
  `sessaoId` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('aberta','finalizada','cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'aberta',
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `integra_vendas_itens`
--

CREATE TABLE `integra_vendas_itens` (
  `id` int(11) NOT NULL,
  `vendaId` int(11) NOT NULL,
  `produto_servicoId` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `precoUnitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuarioId` (`usuarioId`),
  ADD KEY `idx_acao` (`acao`),
  ADD KEY `idx_createdAt` (`createdAt`);

--
-- Índices para tabela `integra_pagamentos`
--
ALTER TABLE `integra_pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendaId` (`vendaId`),
  ADD KEY `idx_referencia` (`referencia`),
  ADD KEY `idx_txid` (`txid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_createdAt` (`createdAt`);

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
-- Índices para tabela `integra_produtos_servicos`
--
ALTER TABLE `integra_produtos_servicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `integra_saldo`
--
ALTER TABLE `integra_saldo`
  ADD PRIMARY KEY (`perfil_id`);

--
-- Índices para tabela `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`(100)),
  ADD KEY `idx_expiresAt` (`expiresAt`),
  ADD KEY `idx_usuarioId` (`usuarioId`);

--
-- Índices para tabela `integra_transacoes`
--
ALTER TABLE `integra_transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `origem_id` (`origem_perfil_id`),
  ADD KEY `destino_id` (`destino_perfil_id`),
  ADD KEY `origem_perfil_id` (`origem_perfil_id`),
  ADD KEY `destino_perfil_id` (`destino_perfil_id`);

--
-- Índices para tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `fk_usuario_perfil_ativo` (`idPerfilAtivo`);

--
-- Índices para tabela `integra_vendas`
--
ALTER TABLE `integra_vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuarioId` (`usuarioId`),
  ADD KEY `idx_sessaoId` (`sessaoId`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_createdAt` (`createdAt`);

--
-- Índices para tabela `integra_vendas_itens`
--
ALTER TABLE `integra_vendas_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendaId` (`vendaId`),
  ADD KEY `idx_produto_servicoId` (`produto_servicoId`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_pagamentos`
--
ALTER TABLE `integra_pagamentos`
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
-- AUTO_INCREMENT de tabela `integra_produtos_servicos`
--
ALTER TABLE `integra_produtos_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_transacoes`
--
ALTER TABLE `integra_transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_vendas`
--
ALTER TABLE `integra_vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integra_vendas_itens`
--
ALTER TABLE `integra_vendas_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  ADD CONSTRAINT `integra_logs_ibfk_1` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `integra_pagamentos`
--
ALTER TABLE `integra_pagamentos`
  ADD CONSTRAINT `integra_pagamentos_ibfk_1` FOREIGN KEY (`vendaId`) REFERENCES `integra_vendas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_perfis`
--
ALTER TABLE `integra_perfis`
  ADD CONSTRAINT `fk_perfis_papel` FOREIGN KEY (`id_papel`) REFERENCES `integra_papeis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_perfis_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_saldo`
--
ALTER TABLE `integra_saldo`
  ADD CONSTRAINT `integra_saldo_ibfk_1` FOREIGN KEY (`perfil_id`) REFERENCES `integra_perfis` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  ADD CONSTRAINT `integra_sessoes_ibfk_1` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `integra_transacoes`
--
ALTER TABLE `integra_transacoes`
  ADD CONSTRAINT `integra_transacoes_ibfk_1` FOREIGN KEY (`origem_perfil_id`) REFERENCES `integra_perfis` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `integra_transacoes_ibfk_2` FOREIGN KEY (`destino_perfil_id`) REFERENCES `integra_perfis` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  ADD CONSTRAINT `fk_usuario_perfil_ativo` FOREIGN KEY (`idPerfilAtivo`) REFERENCES `integra_perfis` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `integra_vendas`
--
ALTER TABLE `integra_vendas`
  ADD CONSTRAINT `integra_vendas_ibfk_1` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`),
  ADD CONSTRAINT `integra_vendas_ibfk_2` FOREIGN KEY (`sessaoId`) REFERENCES `integra_sessoes` (`id`);

--
-- Limitadores para a tabela `integra_vendas_itens`
--
ALTER TABLE `integra_vendas_itens`
  ADD CONSTRAINT `integra_vendas_itens_ibfk_1` FOREIGN KEY (`vendaId`) REFERENCES `integra_vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `integra_vendas_itens_ibfk_2` FOREIGN KEY (`produto_servicoId`) REFERENCES `integra_produtos_servicos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
