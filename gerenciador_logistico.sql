-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/11/2026 às 01:01
-- Versão do servidor: 10.11.16-MariaDB
-- Versão do PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gerenciador_logistico`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos`
--

CREATE TABLE `enderecos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `rua` varchar(150) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `identificacao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `enderecos`
--

INSERT INTO `enderecos` (`id`, `codigo`, `rua`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `identificacao`) VALUES
(2, 'end001', 'brasil', '1059', 'centro', 'sd', 'SC', '89835-000', 'matriz');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fretes`
--

CREATE TABLE `fretes` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `transportador_id` int(11) DEFAULT NULL,
  `origem_id` int(11) DEFAULT NULL,
  `destino_id` int(11) DEFAULT NULL,
  `data_saida` date DEFAULT NULL,
  `data_entrega` date DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `situacao` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fretes`
--

INSERT INTO `fretes` (`id`, `produto_id`, `transportador_id`, `origem_id`, `destino_id`, `data_saida`, `data_entrega`, `valor`, `situacao`) VALUES
(1, 5, 2, 2, 2, '2026-01-01', '2026-01-02', 1500.00, 'Pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `unidade` varchar(20) DEFAULT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0,
  `peso` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `codigo`, `nome`, `descricao`, `unidade`, `quantidade`, `peso`) VALUES
(5, 'prod001', 'caixa de papelao', 'caixas', NULL, 100, 0.50);

-- --------------------------------------------------------

--
-- Estrutura para tabela `transportadores`
--

CREATE TABLE `transportadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `transportadores`
--

INSERT INTO `transportadores` (`id`, `nome`, `telefone`, `email`) VALUES
(2, 'alfa transportes', '46988852582', 'teste@gmail.com');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices de tabela `fretes`
--
ALTER TABLE `fretes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `transportador_id` (`transportador_id`),
  ADD KEY `origem_id` (`origem_id`),
  ADD KEY `destino_id` (`destino_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices de tabela `transportadores`
--
ALTER TABLE `transportadores`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `fretes`
--
ALTER TABLE `fretes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `transportadores`
--
ALTER TABLE `transportadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `fretes`
--
ALTER TABLE `fretes`
  ADD CONSTRAINT `fretes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `fretes_ibfk_2` FOREIGN KEY (`transportador_id`) REFERENCES `transportadores` (`id`),
  ADD CONSTRAINT `fretes_ibfk_3` FOREIGN KEY (`origem_id`) REFERENCES `enderecos` (`id`),
  ADD CONSTRAINT `fretes_ibfk_4` FOREIGN KEY (`destino_id`) REFERENCES `enderecos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
