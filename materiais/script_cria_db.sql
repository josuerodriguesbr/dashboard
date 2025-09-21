-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 20/08/2025 às 01:59
-- Versão do servidor: 10.11.10-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u748224509_bingosys`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `integra_logs`
--

CREATE TABLE `integra_logs` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `userAgent` text DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `integra_logs`
--

INSERT INTO `integra_logs` (`id`, `usuarioId`, `acao`, `detalhes`, `ip`, `userAgent`, `createdAt`) VALUES
(1, 1, 'Teste de Inicialização', 'Tabela integra_logs criada e funcionando', '127.0.0.1', 'Mozilla - Teste', '2025-08-19 20:24:36'),
(2, 3, 'Assinante cadastrado', 'Nome: maria', '170.231.58.31', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-19 22:15:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `integra_pagamentos`
--

CREATE TABLE `integra_pagamentos` (
  `id` int(11) NOT NULL,
  `vendaId` int(11) NOT NULL,
  `metodo` enum('pix','credito','debito','dinheiro','asaas') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('pendente','pago','falhou','estornado') DEFAULT 'pendente',
  `referencia` varchar(255) DEFAULT NULL,
  `txid` varchar(100) DEFAULT NULL,
  `qrCode` text DEFAULT NULL,
  `dataPagamento` datetime DEFAULT NULL,
  `dadosTransacao` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dadosTransacao`)),
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `integra_produtos_servicos`
--

CREATE TABLE `integra_produtos_servicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `tipo` enum('produto','servico') DEFAULT 'produto',
  `ativo` tinyint(1) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `integra_sessoes`
--

CREATE TABLE `integra_sessoes` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `integra_sessoes`
--

INSERT INTO `integra_sessoes` (`id`, `usuarioId`, `token`, `expiresAt`, `isActive`, `createdAt`) VALUES
(1, 1, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c3VhcmlvSWQiOiIxIiwiZW1haWwiOiJhZG1pbkBlbXByZXNhLmNvbSIsIm5pdmVsIjoiYWRtaW4iLCJpYXQiOjE3NTU1Njc5MjIsImV4cCI6MTc1NjE3MjcyMn0=.wOzLRBr8oRe65cHgDNg71i2ei9sBltqzXInsO+pCzgI=', '2025-08-26 01:45:22', 1, '2025-08-19 01:45:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `integra_usuarios`
--

CREATE TABLE `integra_usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `nivel` enum('admin','assinante','vendedor','cliente') DEFAULT 'cliente',
  `email` varchar(100) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `integra_usuarios`
--

INSERT INTO `integra_usuarios` (`id`, `nome`, `nivel`, `email`, `cpf`, `telefone`, `createdAt`, `updatedAt`) VALUES
(1, 'Administrador', 'cliente', 'admin@empresa.com', '000.000.000-00', '(11) 99999-8888', '2025-08-19 01:41:05', '2025-08-19 01:41:05'),
(3, 'maria', 'assinante', 'maria@gmail.com', '1234\'', '4321', '2025-08-19 22:15:57', '2025-08-19 22:15:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `integra_vendas`
--

CREATE TABLE `integra_vendas` (
  `id` int(11) NOT NULL,
  `usuarioId` int(11) NOT NULL,
  `sessaoId` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('aberta','finalizada','cancelada') DEFAULT 'aberta',
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `integra_vendas_itens`
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
-- Índices de tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuarioId` (`usuarioId`),
  ADD KEY `idx_acao` (`acao`),
  ADD KEY `idx_createdAt` (`createdAt`);

--
-- Índices de tabela `integra_pagamentos`
--
ALTER TABLE `integra_pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendaId` (`vendaId`),
  ADD KEY `idx_referencia` (`referencia`),
  ADD KEY `idx_txid` (`txid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_createdAt` (`createdAt`);

--
-- Índices de tabela `integra_produtos_servicos`
--
ALTER TABLE `integra_produtos_servicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`(100)),
  ADD KEY `idx_expiresAt` (`expiresAt`),
  ADD KEY `idx_usuarioId` (`usuarioId`);

--
-- Índices de tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `integra_vendas`
--
ALTER TABLE `integra_vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuarioId` (`usuarioId`),
  ADD KEY `idx_sessaoId` (`sessaoId`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_createdAt` (`createdAt`);

--
-- Índices de tabela `integra_vendas_itens`
--
ALTER TABLE `integra_vendas_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendaId` (`vendaId`),
  ADD KEY `idx_produto_servicoId` (`produto_servicoId`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `integra_logs`
--
ALTER TABLE `integra_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `integra_pagamentos`
--
ALTER TABLE `integra_pagamentos`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `integra_usuarios`
--
ALTER TABLE `integra_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `integra_logs`
--
ALTER TABLE `integra_logs`
  ADD CONSTRAINT `integra_logs_ibfk_1` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `integra_pagamentos`
--
ALTER TABLE `integra_pagamentos`
  ADD CONSTRAINT `integra_pagamentos_ibfk_1` FOREIGN KEY (`vendaId`) REFERENCES `integra_vendas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `integra_sessoes`
--
ALTER TABLE `integra_sessoes`
  ADD CONSTRAINT `integra_sessoes_ibfk_1` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `integra_vendas`
--
ALTER TABLE `integra_vendas`
  ADD CONSTRAINT `integra_vendas_ibfk_1` FOREIGN KEY (`usuarioId`) REFERENCES `integra_usuarios` (`id`),
  ADD CONSTRAINT `integra_vendas_ibfk_2` FOREIGN KEY (`sessaoId`) REFERENCES `integra_sessoes` (`id`);

--
-- Restrições para tabelas `integra_vendas_itens`
--
ALTER TABLE `integra_vendas_itens`
  ADD CONSTRAINT `integra_vendas_itens_ibfk_1` FOREIGN KEY (`vendaId`) REFERENCES `integra_vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `integra_vendas_itens_ibfk_2` FOREIGN KEY (`produto_servicoId`) REFERENCES `integra_produtos_servicos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
