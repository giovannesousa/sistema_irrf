-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09-Fev-2026 às 16:40
-- Versão do servidor: 10.4.25-MariaDB
-- versão do PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_irrf`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razao_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_fantasia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regime_tributario` enum('simples_nacional','lucro_presumido','lucro_real','mei','outros') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outros',
  `ativo` tinyint(1) DEFAULT 1,
  `endereco_completo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id`, `cnpj`, `razao_social`, `nome_fantasia`, `regime_tributario`, `ativo`, `endereco_completo`, `email`, `telefone`, `created_at`) VALUES
(1, '50064197000109', 'TELESTO SISTEMAS LTDA', NULL, 'outros', 1, NULL, NULL, NULL, '2026-02-07 16:07:52');

-- --------------------------------------------------------

--
-- Estrutura da tabela `natureza_servicos`
--

CREATE TABLE `natureza_servicos` (
  `id` int(11) NOT NULL,
  `codigo_rfb` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aliquota_padrao` decimal(5,2) NOT NULL,
  `permite_retencao` tinyint(1) DEFAULT 1,
  `texto_lei` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `natureza_servicos`
--

INSERT INTO `natureza_servicos` (`id`, `codigo_rfb`, `descricao`, `aliquota_padrao`, `permite_retencao`, `texto_lei`) VALUES
(1, '96', 'Demais serviços - IN 2.145', '4.80', 1, 'VALOR REFERENTE AO IRRF, CONFORME IN RFB nº 2.145/2023...'),
(2, '97', 'Serviços de Limpeza', '1.20', 1, 'VALOR REFERENTE AO IRRF, CONFORME IN RFB nº 2.145/2023...'),
(3, '6147', 'Alimentação', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147'),
(4, '6147', 'Energia elétrica', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147'),
(5, '6147', 'Serviços prestados com emprego de materiais', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147 (Materiais discriminados na nota)'),
(6, '6147', 'Construção Civil por empreitada com emprego de materiais', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147'),
(7, '6147', 'Serviços hospitalares de que trata o art. 30', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147 (Arts. 30 e 31)'),
(8, '6147', 'Serviços de auxílio diagnóstico e terapia de que trata o art. 31', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147'),
(9, '6147', 'Transporte de cargas, exceto os relacionados no código 8767', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147'),
(10, '6147', 'Mercadorias e bens em geral', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 6147'),
(11, '8767', 'Transporte internacional de cargas efetuado por empresas nacionais', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 8767'),
(12, '8767', 'Estaleiros navais e de reparo naval', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 8767'),
(13, '8767', 'Produtos farmacêuticos, de perfumaria, de toucador ou de higiene pessoal', '1.20', 1, 'IN RFB 1234/2012 - Anexo I - Código 8767 (Adquirente distribuidor/varejista)'),
(14, '9060', 'Gasolina, óleo diesel, GLP e QAV (Refinarias/Produtores)', '0.24', 1, 'IN RFB 1234/2012 - Anexo I - Código 9060'),
(15, '9060', 'Álcool etílico hidratado, inclusive para fins carburantes (Produtores)', '0.24', 1, 'IN RFB 1234/2012 - Anexo I - Código 9060'),
(16, '9060', 'Biodiesel (Produtores)', '0.24', 1, 'IN RFB 1234/2012 - Anexo I - Código 9060'),
(17, '8739', 'Gasolina, óleo diesel, GLP e álcool etílico (Varejistas/Distribuidores)', '0.24', 1, 'IN RFB 1234/2012 - Anexo I - Código 8739'),
(18, '6175', 'Passagens aéreas, rodoviárias e demais', '2.40', 1, 'IN RFB 1234/2012 - Anexo I - Código 6175'),
(19, '6175', 'Transporte de passageiros', '2.40', 1, 'IN RFB 1234/2012 - Anexo I - Código 6175'),
(20, '6190', 'Serviços de abastecimento de água', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(21, '6190', 'Telefone', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(22, '6190', 'Correios e telégrafos', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(23, '6190', 'Vigilância', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(24, '6190', 'Limpeza e conservação', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(25, '6190', 'Locação de mão de obra', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(26, '6190', 'Intermediação de negócios', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(27, '6190', 'Administração, locação ou cessão de bens imóveis, móveis e direitos', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190'),
(28, '6190', 'Serviços de Hotelaria', '4.80', 1, 'Enquadrado em Demais Serviços - IN RFB 1234/2012 - Cód 6190'),
(29, '6190', 'Serviços de Consultoria', '4.80', 1, 'Enquadrado em Demais Serviços - IN RFB 1234/2012 - Cód 6190'),
(30, '6190', 'Demais serviços', '4.80', 1, 'IN RFB 1234/2012 - Anexo I - Código 6190');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notas_fiscais`
--

CREATE TABLE `notas_fiscais` (
  `id` int(11) NOT NULL,
  `id_orgao` int(11) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `id_natureza_servico` int(11) NOT NULL,
  `numero_nota` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie_nota` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_emissao` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `valor_bruto` decimal(15,2) NOT NULL,
  `aliquota_aplicada` decimal(5,2) NOT NULL,
  `valor_irrf_retido` decimal(15,2) DEFAULT 0.00,
  `valor_iss_retido` decimal(15,2) DEFAULT 0.00,
  `valor_liquido` decimal(15,2) GENERATED ALWAYS AS (`valor_bruto` - `valor_irrf_retido` - `valor_iss_retido`) STORED,
  `caminho_anexo_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_pagamento` enum('pendente','pago') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `nota_ativa` tinyint(1) DEFAULT 1,
  `observacoes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao_servico` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `notas_fiscais`
--

INSERT INTO `notas_fiscais` (`id`, `id_orgao`, `id_fornecedor`, `id_natureza_servico`, `numero_nota`, `serie_nota`, `data_emissao`, `data_pagamento`, `valor_bruto`, `aliquota_aplicada`, `valor_irrf_retido`, `valor_iss_retido`, `caminho_anexo_pdf`, `status_pagamento`, `nota_ativa`, `observacoes`, `descricao_servico`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'NF-1770492897464', NULL, '2026-02-07', '2026-02-08', '3000.00', '1.20', '36.00', '0.00', NULL, 'pago', 1, NULL, '', '2026-02-07 19:34:57', '2026-02-08 19:09:45'),
(2, 1, 1, 1, 'NF-1770570440230', '1', '2026-02-08', '2026-02-08', '3000.00', '4.80', '144.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-08 17:07:20', '2026-02-08 19:09:57'),
(3, 1, 1, 2, 'NF-1770576775774', '1', '2026-02-08', '2026-02-08', '2000.00', '1.20', '24.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-08 18:52:55', '2026-02-08 19:10:01'),
(4, 1, 1, 2, 'NF-1770578518139', '1', '2026-02-08', NULL, '1500.00', '1.20', '18.00', '0.00', NULL, 'pendente', 0, '', '', '2026-02-08 19:21:58', '2026-02-09 01:16:47'),
(5, 1, 1, 1, 'NF-1770591614114', '1', '2026-02-09', '2026-02-08', '3000.00', '4.80', '144.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-08 23:00:14', '2026-02-08 23:00:26'),
(6, 1, 1, 1, 'NF-1770599613784', '1', '2026-02-09', '2026-02-09', '3000.00', '4.80', '144.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-09 01:13:33', '2026-02-09 01:13:45');

-- --------------------------------------------------------

--
-- Estrutura da tabela `orgaos`
--

CREATE TABLE `orgaos` (
  `id` int(11) NOT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_oficial` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caminho_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `texto_cabecalho` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `texto_rodape` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `orgaos`
--

INSERT INTO `orgaos` (`id`, `cnpj`, `nome_oficial`, `cep`, `logradouro`, `numero`, `bairro`, `cidade`, `uf`, `complemento`, `responsavel_nome`, `responsavel_email`, `caminho_logo`, `texto_cabecalho`, `texto_rodape`, `created_at`, `updated_at`) VALUES
(1, '00000000000191', 'PREFEITURA MUNICIPAL DE EXEMPLO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-07 17:06:08', '2026-02-07 17:06:08');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `id_nota` int(11) NOT NULL,
  `data_baixa` date NOT NULL,
  `valor_pago` decimal(15,2) NOT NULL,
  `responsavel_baixa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `id_nota`, `data_baixa`, `valor_pago`, `responsavel_baixa`) VALUES
(1, 1, '2026-02-08', '2964.00', 1),
(2, 2, '2026-02-08', '2856.00', 1),
(3, 3, '2026-02-08', '1976.00', 1),
(4, 5, '2026-02-08', '2856.00', 1),
(5, 6, '2026-02-09', '2856.00', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `id_orgao` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel_acesso` enum('admin','operador') COLLATE utf8mb4_unicode_ci DEFAULT 'operador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `id_orgao`, `nome`, `login`, `senha_hash`, `nivel_acesso`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrador', 'admin', '$2y$10$rKsKdFSXO3wmZxhKmkjF6OPfndB2g32xNi2D9W6iKij.M2SHWFJSq', 'admin', '2026-02-08 23:36:41', '2026-02-08 23:36:41');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD KEY `idx_fornecedores_cnpj` (`cnpj`);

--
-- Índices para tabela `natureza_servicos`
--
ALTER TABLE `natureza_servicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `notas_fiscais`
--
ALTER TABLE `notas_fiscais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_orgao` (`id_orgao`),
  ADD KEY `id_fornecedor` (`id_fornecedor`),
  ADD KEY `id_natureza_servico` (`id_natureza_servico`);

--
-- Índices para tabela `orgaos`
--
ALTER TABLE `orgaos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices para tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nota` (`id_nota`),
  ADD KEY `responsavel_baixa` (`responsavel_baixa`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `id_orgao` (`id_orgao`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `natureza_servicos`
--
ALTER TABLE `natureza_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `notas_fiscais`
--
ALTER TABLE `notas_fiscais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `orgaos`
--
ALTER TABLE `orgaos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `notas_fiscais`
--
ALTER TABLE `notas_fiscais`
  ADD CONSTRAINT `notas_fiscais_ibfk_1` FOREIGN KEY (`id_orgao`) REFERENCES `orgaos` (`id`),
  ADD CONSTRAINT `notas_fiscais_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`),
  ADD CONSTRAINT `notas_fiscais_ibfk_3` FOREIGN KEY (`id_natureza_servico`) REFERENCES `natureza_servicos` (`id`);

--
-- Limitadores para a tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`id_nota`) REFERENCES `notas_fiscais` (`id`),
  ADD CONSTRAINT `pagamentos_ibfk_2` FOREIGN KEY (`responsavel_baixa`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_orgao`) REFERENCES `orgaos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
