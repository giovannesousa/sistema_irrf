-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 10-Fev-2026 às 18:05
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
(6, 1, 1, 1, 'NF-1770599613784', '1', '2026-02-09', '2026-02-09', '3000.00', '4.80', '144.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-09 01:13:33', '2026-02-09 01:13:45'),
(7, 1, 1, 23, 'NF-1770740749585', '1', '2026-02-10', '2026-02-10', '4500.00', '4.80', '216.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-10 16:25:49', '2026-02-10 16:26:10'),
(8, 1, 1, 5, 'NF-1770741878120', '1', '2026-02-10', '2026-02-10', '2100.00', '1.20', '252.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-10 16:44:38', '2026-02-10 16:44:52'),
(9, 1, 1, 14, 'NF-1770742164271', '1', '2026-02-10', NULL, '10000.00', '0.24', '24.00', '0.00', NULL, 'pendente', 1, '', '', '2026-02-10 16:49:24', '2026-02-10 16:49:24'),
(10, 1, 1, 19, 'NF-1770742513559', '1', '2026-02-10', '2026-02-10', '5000.00', '2.40', '120.00', '0.00', NULL, 'pago', 1, '', '', '2026-02-10 16:55:13', '2026-02-10 16:55:26');

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
(1, '00860058000105', 'MUNICIPIO DE FRANCISCO SANTOS - CAMARA MUNICIPAL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-07 17:06:08', '2026-02-09 20:07:12');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `id_nota` int(11) NOT NULL,
  `data_pagamento` date NOT NULL,
  `valor_bruto` decimal(10,2) DEFAULT 0.00,
  `valor_base_ir` decimal(10,2) DEFAULT 0.00,
  `valor_ir` decimal(10,2) DEFAULT 0.00,
  `valor_pago` decimal(15,2) NOT NULL,
  `responsavel_baixa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `id_nota`, `data_pagamento`, `valor_bruto`, `valor_base_ir`, `valor_ir`, `valor_pago`, `responsavel_baixa`) VALUES
(1, 1, '2026-02-08', '3000.00', '3000.00', '36.00', '2964.00', 1),
(2, 2, '2026-02-08', '3000.00', '3000.00', '144.00', '2856.00', 1),
(3, 3, '2026-02-08', '2000.00', '2000.00', '24.00', '1976.00', 1),
(4, 5, '2026-02-08', '3000.00', '3000.00', '144.00', '2856.00', 1),
(5, 6, '2026-02-09', '3000.00', '3000.00', '144.00', '2856.00', 1),
(6, 7, '2026-02-10', '0.00', '0.00', '0.00', '4284.00', 1),
(7, 8, '2026-02-10', '0.00', '0.00', '0.00', '1848.00', 1),
(8, 10, '2026-02-10', '5000.00', '5000.00', '120.00', '4880.00', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `reinf_eventos`
--

CREATE TABLE `reinf_eventos` (
  `id` int(11) NOT NULL,
  `id_lote` int(11) DEFAULT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `per_apuracao` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_evento` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'R-4020',
  `id_evento_xml` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_recibo` varchar(52) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pendente','assinado','em_lote','sucesso','rejeitado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `xml_assinado` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensagem_erro` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `reinf_eventos`
--

INSERT INTO `reinf_eventos` (`id`, `id_lote`, `id_fornecedor`, `per_apuracao`, `tipo_evento`, `id_evento_xml`, `numero_recibo`, `status`, `xml_assinado`, `mensagem_erro`) VALUES
(1, 1, 1, '2026-02', 'R-4020', 'ID1008600580001052026021005385681787', NULL, 'em_lote', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Reinf xmlns=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\"><evtRetPJ id=\"ID1008600580001052026021005385681787\"><ideEvento><indRetif>1</indRetif><perApur>2026-02</perApur><tpAmb>2</tpAmb><procEmi>1</procEmi><verProc>1.0</verProc></ideEvento><ideContri><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContri><ideEstab><tpInscEstab>1</tpInscEstab><nrInscEstab>00860058000105</nrInscEstab><ideBenef><cnpjBenef>50064197000109</cnpjBenef><idePgto><natRend>97</natRend><infoPgto><dtFG>2026-02-08</dtFG><vlrBruto>3000.00</vlrBruto><retencoes><vlrBaseIR>3000.00</vlrBaseIR><vlrIR>36.00</vlrIR></retencoes></infoPgto><infoPgto><dtFG>2026-02-08</dtFG><vlrBruto>3000.00</vlrBruto><retencoes><vlrBaseIR>3000.00</vlrBaseIR><vlrIR>144.00</vlrIR></retencoes></infoPgto><infoPgto><dtFG>2026-02-08</dtFG><vlrBruto>2000.00</vlrBruto><retencoes><vlrBaseIR>2000.00</vlrBaseIR><vlrIR>24.00</vlrIR></retencoes></infoPgto><infoPgto><dtFG>2026-02-08</dtFG><vlrBruto>3000.00</vlrBruto><retencoes><vlrBaseIR>3000.00</vlrBaseIR><vlrIR>144.00</vlrIR></retencoes></infoPgto><infoPgto><dtFG>2026-02-09</dtFG><vlrBruto>3000.00</vlrBruto><retencoes><vlrBaseIR>3000.00</vlrBaseIR><vlrIR>144.00</vlrIR></retencoes></infoPgto></idePgto></ideBenef></ideEstab></evtRetPJ><Signature xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><SignedInfo xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><SignatureMethod Algorithm=\"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256\"/><Reference URI=\"#ID1008600580001052026021005385681787\"><Transforms><Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/><Transform Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/></Transforms><DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"/><DigestValue>j8JNKuG/IaWAEOkv/0EUY0WlBuBGmsWNdiFQaYxLIuE=</DigestValue></Reference></SignedInfo><SignatureValue>Fld/kBobukJev7PjdptWp7nw/BlMcTnTIf7pxqnY7Ue8xNRvbA0zOHeJg5J5iPwW6DzU+LN42r25TuiIm8LI+d7YdoYtcBEJ8sES7NK14aWT238uDTBbJ8zn6z2rKIWs/BB7A4yiOnXVZwL8R1+0b8HNiOKgwY4AFLPvrgInNIIsb4CM/9acxkD9L2nnPHNiwDbaF8Pev6bGNS6A4DukXWSh7siRPK40UkkC6lja3GK/3BMNNGKJ3/tYRRbW/j8aOb/mMVAzLkzt8mnoby0+XFgHwZjSJmNdVXdhnkpxM6SYkPBMwVTL4JVzTvSefWLXo8xCN6F//BXWHZAuD5FhSw==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></Reinf>\n', NULL),
(2, 2, 1, '2026-02', 'R-4020', 'ID1008600580001052026021017555539264', NULL, 'em_lote', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Reinf xmlns=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\"><evtRetPJ id=\"ID1008600580001052026021017555539264\"><ideEvento><indRetif>1</indRetif><perApur>2026-02</perApur><tpAmb>2</tpAmb><procEmi>1</procEmi><verProc>1.0</verProc></ideEvento><ideContri><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContri><ideEstab><tpInscEstab>1</tpInscEstab><nrInscEstab>00860058000105</nrInscEstab><ideBenef><cnpjBenef>50064197000109</cnpjBenef><idePgto><natRend>6190</natRend><infoPgto><dtFG>2026-02-10</dtFG><vlrBruto>4500.00</vlrBruto><retencoes><vlrBaseIR>4500.00</vlrBaseIR><vlrIR>216.00</vlrIR></retencoes></infoPgto><infoPgto><dtFG>2026-02-10</dtFG><vlrBruto>2100.00</vlrBruto><retencoes><vlrBaseIR>2100.00</vlrBaseIR><vlrIR>252.00</vlrIR></retencoes></infoPgto><infoPgto><dtFG>2026-02-10</dtFG><vlrBruto>5000.00</vlrBruto><retencoes><vlrBaseIR>5000.00</vlrBaseIR><vlrIR>120.00</vlrIR></retencoes></infoPgto></idePgto></ideBenef></ideEstab></evtRetPJ><Signature xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><SignedInfo xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><SignatureMethod Algorithm=\"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256\"/><Reference URI=\"#ID1008600580001052026021017555539264\"><Transforms><Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/><Transform Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/></Transforms><DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"/><DigestValue>1YzmYg8OoMhV9OsrrihcibXuDzwuy6gK52E9YF5zIFw=</DigestValue></Reference></SignedInfo><SignatureValue>jtSddHfVoiNmBXzMZxWVZ+4ijqg+ArJYqY2Cumpdm/ccVboeiKQlL+YJKlcwWo1AxAczsK6rvsHveCDpwSMMWHX5wi8nMDAu9NZwvj2DCrG9VW7vD5zB51EkzrmMdpO2AcNYZ6D9a1saC0AuAdpVCKsMArs251fZ3p0kzE41TXiemDH4H1rZyHchNAnk7NofrPods3hMvJLP/HiRqa1CXyyV6WaR/v6x+OJK+Q9tbdBCmINleBv18b4zUna4pvWbjlK0N5hoZGi4lcXYwZEgOdWKRolDm8NVbLix4bujfhLf/QslQbt1bCjpH2AteeVprETSrjWacPde2y3TMIxdNA==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></Reinf>\n', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `reinf_evento_pagamentos`
--

CREATE TABLE `reinf_evento_pagamentos` (
  `id_evento` int(11) NOT NULL,
  `id_pagamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `reinf_evento_pagamentos`
--

INSERT INTO `reinf_evento_pagamentos` (`id_evento`, `id_pagamento`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 6),
(2, 7),
(2, 8);

-- --------------------------------------------------------

--
-- Estrutura da tabela `reinf_lotes`
--

CREATE TABLE `reinf_lotes` (
  `id` int(11) NOT NULL,
  `id_orgao` int(11) NOT NULL,
  `protocolo` varchar(50) DEFAULT NULL,
  `ambiente` tinyint(1) NOT NULL DEFAULT 2,
  `status` enum('gerado','enviado','processado','erro') DEFAULT 'gerado',
  `xml_envio` longtext DEFAULT NULL,
  `xml_retorno` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `reinf_lotes`
--

INSERT INTO `reinf_lotes` (`id`, `id_orgao`, `protocolo`, `ambiente`, `status`, `xml_envio`, `xml_retorno`, `created_at`) VALUES
(1, 1, '2.202602.31686015', 2, 'enviado', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Reinf xmlns=\"http://www.reinf.esocial.gov.br/schemas/envioLoteEventosAssincrono/v1_00_00\"><envioLoteEventos><ideContribuinte><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContribuinte><eventos xmlns:default=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\" xmlns:default1=\"http://www.w3.org/2000/09/xmldsig#\"><evento xmlns:default=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\" xmlns:default1=\"http://www.w3.org/2000/09/xmldsig#\" Id=\"ID1008600580001052026021005385681787\"><default:Reinf xmlns=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\" xmlns:default=\"http://www.w3.org/2000/09/xmldsig#\"><default:evtRetPJ id=\"ID1008600580001052026021005385681787\"><default:ideEvento><default:indRetif>1</default:indRetif><default:perApur>2026-02</default:perApur><default:tpAmb>2</default:tpAmb><default:procEmi>1</default:procEmi><default:verProc>1.0</default:verProc></default:ideEvento><default:ideContri><default:tpInsc>1</default:tpInsc><default:nrInsc>00860058</default:nrInsc></default:ideContri><default:ideEstab><default:tpInscEstab>1</default:tpInscEstab><default:nrInscEstab>00860058000105</default:nrInscEstab><default:ideBenef><default:cnpjBenef>50064197000109</default:cnpjBenef><default:idePgto><default:natRend>97</default:natRend><default:infoPgto><default:dtFG>2026-02-08</default:dtFG><default:vlrBruto>3000.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>3000.00</default:vlrBaseIR><default:vlrIR>36.00</default:vlrIR></default:retencoes></default:infoPgto><default:infoPgto><default:dtFG>2026-02-08</default:dtFG><default:vlrBruto>3000.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>3000.00</default:vlrBaseIR><default:vlrIR>144.00</default:vlrIR></default:retencoes></default:infoPgto><default:infoPgto><default:dtFG>2026-02-08</default:dtFG><default:vlrBruto>2000.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>2000.00</default:vlrBaseIR><default:vlrIR>24.00</default:vlrIR></default:retencoes></default:infoPgto><default:infoPgto><default:dtFG>2026-02-08</default:dtFG><default:vlrBruto>3000.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>3000.00</default:vlrBaseIR><default:vlrIR>144.00</default:vlrIR></default:retencoes></default:infoPgto><default:infoPgto><default:dtFG>2026-02-09</default:dtFG><default:vlrBruto>3000.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>3000.00</default:vlrBaseIR><default:vlrIR>144.00</default:vlrIR></default:retencoes></default:infoPgto></default:idePgto></default:ideBenef></default:ideEstab></default:evtRetPJ><default1:Signature xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><default1:SignedInfo xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><default1:CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><default1:SignatureMethod Algorithm=\"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256\"/><default1:Reference URI=\"#ID1008600580001052026021005385681787\"><default1:Transforms><default1:Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/><default1:Transform Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/></default1:Transforms><default1:DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"/><default1:DigestValue>j8JNKuG/IaWAEOkv/0EUY0WlBuBGmsWNdiFQaYxLIuE=</default1:DigestValue></default1:Reference></default1:SignedInfo><default1:SignatureValue>Fld/kBobukJev7PjdptWp7nw/BlMcTnTIf7pxqnY7Ue8xNRvbA0zOHeJg5J5iPwW6DzU+LN42r25TuiIm8LI+d7YdoYtcBEJ8sES7NK14aWT238uDTBbJ8zn6z2rKIWs/BB7A4yiOnXVZwL8R1+0b8HNiOKgwY4AFLPvrgInNIIsb4CM/9acxkD9L2nnPHNiwDbaF8Pev6bGNS6A4DukXWSh7siRPK40UkkC6lja3GK/3BMNNGKJ3/tYRRbW/j8aOb/mMVAzLkzt8mnoby0+XFgHwZjSJmNdVXdhnkpxM6SYkPBMwVTL4JVzTvSefWLXo8xCN6F//BXWHZAuD5FhSw==</default1:SignatureValue><default1:KeyInfo><default1:X509Data><default1:X509Certificate>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</default1:X509Certificate></default1:X509Data></default1:KeyInfo></default1:Signature></default:Reinf></evento></eventos></envioLoteEventos></Reinf>\n', '<Reinf xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns=\"http://www.reinf.esocial.gov.br/schemas/retornoLoteEventosAssincrono/v1_00_00\"><retornoLoteEventosAssincrono><ideContribuinte><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContribuinte><status><cdResposta>3</cdResposta><descResposta>Lote processado com sucesso - Possui um ou mais eventos com ocorrências de erro.</descResposta></status><dadosRecepcaoLote><dhRecepcao>2026-02-10T01:38:57.017</dhRecepcao><versaoAplicativoRecepcao>3.0.0-3360763</versaoAplicativoRecepcao><protocoloEnvio>2.202602.31686015</protocoloEnvio></dadosRecepcaoLote><dadosProcessamentoLote><versaoAplicativoProcessamentoLote>3.0.1-3450168</versaoAplicativoProcessamentoLote></dadosProcessamentoLote><retornoEventos><evento Id=\"ID1008600580001052026021005385681787\"><retornoEvento><Reinf xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns=\"http://www.reinf.esocial.gov.br/schemas/evtTotal/v2_01_02\"><evtTotal id=\"ID900100000000000542765149\"><ideEvento><perApur>2026-02</perApur></ideEvento><ideContri><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContri><ideRecRetorno><ideStatus><cdRetorno>1</cdRetorno><descRetorno>ERRO</descRetorno><regOcorrs><tpOcorr>1</tpOcorr><localErroAviso /><codResp>MS0030</codResp><dscResp>A estrutura do arquivo XML está em desconformidade com o esquema XSD. NamespaceURI inválido ou inexistente no xml do evento. </dscResp></regOcorrs></ideStatus></ideRecRetorno><infoRecEv><dhRecepcao>2026-02-10T01:38:57.017</dhRecepcao><dhProcess>2026-02-10T01:38:57.5196184-03:00</dhProcess><tpEv>0</tpEv><idEv>ID1008600580001052026021005385681787</idEv><hash>j8JNKuG/IaWAEOkv/0EUY0WlBuBGmsWNdiFQaYxLIuE=</hash></infoRecEv></evtTotal><Signature xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><SignedInfo><CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\" /><SignatureMethod Algorithm=\"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256\" /><Reference URI=\"#ID900100000000000542765149\"><Transforms><Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\" /><Transform Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\" /></Transforms><DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\" /><DigestValue>sOoB3a2n4ZLKzs8u1zSG2NQ6oZIeplYfdu1etiayuzY=</DigestValue></Reference></SignedInfo><SignatureValue>RqamiAbfY54JopPy3eq2ISkc6Iqg5VikcaIGVfpBtnCUP2+cMghLHkQhAyY90CjoftnB+QEDIuM9u7xeurqFo1UZ+U6BOLy5VPfvKmeRQbzrlk4JViknaVqxDUHVDE+mB9kqEhX259FiaXDXbX06Xy2VbQCthrYW8+thREoGH6eRSHwHiFm+xr0FvC2bjyK68kS3R+FVj40jEZsbdyJBTkioNNuoBgU98bi2dWkQItKNDgFymKOqAia9D17o07g/obk413s2+qpp3W3U6ywrMpOB4U9FRCXTVSmL+TwWhz84fE2ui+zmftKMqTw1q2F/9RKVwFdqTzrrHkz5c+roGQ==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></Reinf></retornoEvento></evento></retornoEventos></retornoLoteEventosAssincrono></Reinf>', '2026-02-10 04:38:56'),
(2, 1, '2.202602.31688644', 2, 'enviado', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Reinf xmlns=\"http://www.reinf.esocial.gov.br/schemas/envioLoteEventosAssincrono/v1_00_00\"><envioLoteEventos><ideContribuinte><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContribuinte><eventos xmlns:default=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\" xmlns:default1=\"http://www.w3.org/2000/09/xmldsig#\"><evento xmlns:default=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\" xmlns:default1=\"http://www.w3.org/2000/09/xmldsig#\" Id=\"ID1008600580001052026021017555539264\"><default:Reinf xmlns=\"http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02\" xmlns:default=\"http://www.w3.org/2000/09/xmldsig#\"><default:evtRetPJ id=\"ID1008600580001052026021017555539264\"><default:ideEvento><default:indRetif>1</default:indRetif><default:perApur>2026-02</default:perApur><default:tpAmb>2</default:tpAmb><default:procEmi>1</default:procEmi><default:verProc>1.0</default:verProc></default:ideEvento><default:ideContri><default:tpInsc>1</default:tpInsc><default:nrInsc>00860058</default:nrInsc></default:ideContri><default:ideEstab><default:tpInscEstab>1</default:tpInscEstab><default:nrInscEstab>00860058000105</default:nrInscEstab><default:ideBenef><default:cnpjBenef>50064197000109</default:cnpjBenef><default:idePgto><default:natRend>6190</default:natRend><default:infoPgto><default:dtFG>2026-02-10</default:dtFG><default:vlrBruto>4500.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>4500.00</default:vlrBaseIR><default:vlrIR>216.00</default:vlrIR></default:retencoes></default:infoPgto><default:infoPgto><default:dtFG>2026-02-10</default:dtFG><default:vlrBruto>2100.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>2100.00</default:vlrBaseIR><default:vlrIR>252.00</default:vlrIR></default:retencoes></default:infoPgto><default:infoPgto><default:dtFG>2026-02-10</default:dtFG><default:vlrBruto>5000.00</default:vlrBruto><default:retencoes><default:vlrBaseIR>5000.00</default:vlrBaseIR><default:vlrIR>120.00</default:vlrIR></default:retencoes></default:infoPgto></default:idePgto></default:ideBenef></default:ideEstab></default:evtRetPJ><default1:Signature xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><default1:SignedInfo xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><default1:CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><default1:SignatureMethod Algorithm=\"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256\"/><default1:Reference URI=\"#ID1008600580001052026021017555539264\"><default1:Transforms><default1:Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/><default1:Transform Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/></default1:Transforms><default1:DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"/><default1:DigestValue>1YzmYg8OoMhV9OsrrihcibXuDzwuy6gK52E9YF5zIFw=</default1:DigestValue></default1:Reference></default1:SignedInfo><default1:SignatureValue>jtSddHfVoiNmBXzMZxWVZ+4ijqg+ArJYqY2Cumpdm/ccVboeiKQlL+YJKlcwWo1AxAczsK6rvsHveCDpwSMMWHX5wi8nMDAu9NZwvj2DCrG9VW7vD5zB51EkzrmMdpO2AcNYZ6D9a1saC0AuAdpVCKsMArs251fZ3p0kzE41TXiemDH4H1rZyHchNAnk7NofrPods3hMvJLP/HiRqa1CXyyV6WaR/v6x+OJK+Q9tbdBCmINleBv18b4zUna4pvWbjlK0N5hoZGi4lcXYwZEgOdWKRolDm8NVbLix4bujfhLf/QslQbt1bCjpH2AteeVprETSrjWacPde2y3TMIxdNA==</default1:SignatureValue><default1:KeyInfo><default1:X509Data><default1:X509Certificate>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</default1:X509Certificate></default1:X509Data></default1:KeyInfo></default1:Signature></default:Reinf></evento></eventos></envioLoteEventos></Reinf>\n', '<Reinf xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns=\"http://www.reinf.esocial.gov.br/schemas/retornoLoteEventosAssincrono/v1_00_00\"><retornoLoteEventosAssincrono><ideContribuinte><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContribuinte><status><cdResposta>3</cdResposta><descResposta>Lote processado com sucesso - Possui um ou mais eventos com ocorrências de erro.</descResposta></status><dadosRecepcaoLote><dhRecepcao>2026-02-10T13:55:56.093</dhRecepcao><versaoAplicativoRecepcao>3.0.0-3360763</versaoAplicativoRecepcao><protocoloEnvio>2.202602.31688644</protocoloEnvio></dadosRecepcaoLote><dadosProcessamentoLote><versaoAplicativoProcessamentoLote>3.0.1-3450168</versaoAplicativoProcessamentoLote></dadosProcessamentoLote><retornoEventos><evento Id=\"ID1008600580001052026021017555539264\"><retornoEvento><Reinf xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns=\"http://www.reinf.esocial.gov.br/schemas/evtTotal/v2_01_02\"><evtTotal id=\"ID900100000000000338724358\"><ideEvento><perApur>2026-02</perApur></ideEvento><ideContri><tpInsc>1</tpInsc><nrInsc>00860058</nrInsc></ideContri><ideRecRetorno><ideStatus><cdRetorno>1</cdRetorno><descRetorno>ERRO</descRetorno><regOcorrs><tpOcorr>1</tpOcorr><localErroAviso /><codResp>MS0030</codResp><dscResp>A estrutura do arquivo XML está em desconformidade com o esquema XSD. NamespaceURI inválido ou inexistente no xml do evento. </dscResp></regOcorrs></ideStatus></ideRecRetorno><infoRecEv><dhRecepcao>2026-02-10T13:55:56.093</dhRecepcao><dhProcess>2026-02-10T13:55:56.227345-03:00</dhProcess><tpEv>0</tpEv><idEv>ID1008600580001052026021017555539264</idEv><hash>1YzmYg8OoMhV9OsrrihcibXuDzwuy6gK52E9YF5zIFw=</hash></infoRecEv></evtTotal><Signature xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><SignedInfo><CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\" /><SignatureMethod Algorithm=\"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256\" /><Reference URI=\"#ID900100000000000338724358\"><Transforms><Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\" /><Transform Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\" /></Transforms><DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\" /><DigestValue>gT1RwalUYmSXB/W3HbRYa8IbCw8IynTCTNt283z16rc=</DigestValue></Reference></SignedInfo><SignatureValue>FHtdjvgKs1YXy7NUo8tEYHXWWUqFsfoLc/0F6DDSYZ8FgYiJkTJFIIKLfv2Kla93di/gf+2tnO+iMGAc5D7h+2LDkxTOhsHyhAqRjjPQdQrefg7lGrpeZmTp8A7KUNbhN+OdRg7JWpNJEpiNCfw8E/oZ4IyrOpTCncE2Z4gvm4MDIP21+BCuzg+Eyp7UvVUEEasxMzP3yOGUHyjPOg1mjduJds8pTOBHLVSSDUs6zRmiW+aCKk6cru7H9C7vyjgFm1t8Kj+n8vp2LGN7xchEOSVjcvTSyTmwc/F0GzOK8XVo4/w3V7hG70lqIw8e+A9JpOwMYUL6YTxxo6HUqCJ7eg==</SignatureValue><KeyInfo><X509Data><X509Certificate>MIIIdDCCBlygAwIBAgINAJP6iwvJ3UEwXSqcDTANBgkqhkiG9w0BAQsFADCBjDELMAkGA1UEBhMCQlIxEzARBgNVBAoMCklDUC1CcmFzaWwxNTAzBgNVBAsMLEF1dG9yaWRhZGUgQ2VydGlmaWNhZG9yYSBSYWl6IEJyYXNpbGVpcmEgdjEwMTEwLwYDVQQDDChBdXRvcmlkYWRlIENlcnRpZmljYWRvcmEgZG8gU0VSUFJPIFNTTHYxMB4XDTI1MDYwMzEyMzk1OFoXDTI2MDYwMzEyMzk1OFowgd4xCzAJBgNVBAYTAkJSMQswCQYDVQQIDAJERjERMA8GA1UEBwwIQlJBU0lMSUExOzA5BgNVBAoMMlNFUlZJQ08gRkVERVJBTCBERSBQUk9DRVNTQU1FTlRPIERFIERBRE9TIChTRVJQUk8pMRcwFQYDVQQFEw4zMzY4MzExMTAwMDEwNzEqMCgGA1UEAwwhcHJlLXJlaW5mLnJlY2VpdGEuZWNvbm9taWEuZ292LmJyMRgwFgYDVQQPEw9CdXNpbmVzcyBFbnRpdHkxEzARBgsrBgEEAYI3PAIBAxMCQlIwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCsYRJj+e6LXQ8kEmqSUpMWVDvgHZQotxO98om4Bp5yHVYv90r0seQfWcKDMQBDoaOBSab0GHbJ/OYfBzOwmh8pTROrIbGr65MA9HAlHSaT/SGOQiMXqvaV8TS3R9HEKmpESBcLD5TcHNM4eyCBRwUulfvwZPEfklzCedztl8gn0YH+jsQFOM6ORn7PkHPE39Pdtz3pPsSOOv85z4ljn5CkknQXjDAtQsBcY4UHdpz+HKNXzeGX0OOB+wsvh/bb6/ojriAdRJNG8XyOtM0y5AODgqXBxk3mObvoUqhx7qu7J+a6d3qOeww78MVinHS601/Ftj2GpDLvCCiFQbL5ha1HAgMBAAGjggN/MIIDezAfBgNVHSMEGDAWgBStFk9L8Qy+woqihRjXDUYlkyLjzTAOBgNVHQ8BAf8EBAMCBaAwYwYDVR0gBFwwWjAIBgZngQwBAgIwTgYGYEwBAgFpMEQwQgYIKwYBBQUHAgEWNmh0dHA6Ly9yZXBvc2l0b3Jpby5zZXJwcm8uZ292LmJyL2RvY3MvZHBjc2VycHJvc3NsLnBkZjAsBgNVHREEJTAjgiFwcmUtcmVpbmYucmVjZWl0YS5lY29ub21pYS5nb3YuYnIwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwMBMIGIBgNVHR8EgYAwfjA8oDqgOIY2aHR0cDovL3JlcG9zaXRvcmlvLnNlcnByby5nb3YuYnIvbGNyL2Fjc2VycHJvc3NsdjEuY3JsMD6gPKA6hjhodHRwOi8vY2VydGlmaWNhZG9zMi5zZXJwcm8uZ292LmJyL2xjci9hY3NlcnByb3NzbHYxLmNybDCBhwYIKwYBBQUHAQEEezB5MEIGCCsGAQUFBzAChjZodHRwOi8vcmVwb3NpdG9yaW8uc2VycHJvLmdvdi5ici9jYWRlaWFzL3NlcnByb3NzbC5wN2IwMwYIKwYBBQUHMAGGJ2h0dHA6Ly9vY3NwLnNlcnByby5nb3YuYnIvYWNzZXJwcm9zc2x2MTCCAX8GCisGAQQB1nkCBAIEggFvBIIBawFpAHcADleUvPOuqT4zGyyZB7P3kN+bwj1xMiXdIaklrGHFTiEAAAGXNc5uHgAABAMASDBGAiEA+MBKHYT623XtWHMnY+DNABSG9uUJR9I+NOQYj0QAqToCIQDHzn8MlOtHopmjba5q9ZeqlBc33Ix4ReQWm4clY+gipAB2AJaXZL9VWJet90OHaDcIQnfp8DrV9qTzNm5GpD8PyqnGAAABlzXOc3QAAAQDAEcwRQIhAIineKJyXCYtriz7r3EqdccvzZzFuxegX8cG/SOIxjrrAiA2Nqz7+4Wr1/0KH7QtjqljSMmi2ORSw8q9jVPxMG6L+wB2AFZs1aN2voPf40K2dcScIySYp2m6w4LLq0mjh32asy0BAAABlzXOhS8AAAQDAEcwRQIhAPtfURu5MCEnckP9SEf+td4jeKC8n9WB8rMO1+nHgJgVAiBq1bezIDNYUhFb2+/L8wtL3jtSPFIUAJ0FQaf2Ks64nzANBgkqhkiG9w0BAQsFAAOCAgEAVGCLFv6VeFSF7f/OCFPb7K7cEuDZhIfSYCTQ9D6qq1AQpqWy274yN4ofoxSbfiCM7goWfh3F6RsV7TxKd7yzD+eqgJqCMtMQXzOJEcR1l6Eq88DPGU8JteRBOjNvJPFYy9bLtwDLfLniLM76q23OdsU66dmiAfmfR5Dgi2qRY51eeeVOWmKBUfrVZaUCL0sIrHXU9WQdiyZ8L0Gmpvze67JqtTBpHxDJVYatq6LkrqyFz2mbO7vhNvj34ojHoEzU2qPUqjY1VXdJO2wGwBo/WcQSTFSw0HrLHAx+B0a2ktXqtdd4Ow6dFTBjW3ZhpvG6q78g7D9TL/Rsl13D/5xDEPUsHFJGprwiK6ONBkQo/KwFMZMBQXJvBAAHWuiUgjYAyFClmscIJ/evThBUUuvwFMmcDVki98p2wHAWjWk1FmWKoSSKxU6zuIctDVvDy7RaUR8tis/4uGE1SdmPMOiDX/JOCQY1uZ66ZrixBlCzU++nUu9swl5EWlx35w6HzYaFrE59wJ/GYnKN3Ijpwu93toPOFoa9yKUJYt1aq1kPkk0D3cU1TJD0NOU9rFZYE1m1to4RKmXSRv4GE0/o4It+Aa2cTO7IlNaXc3WhJgLRMppjysV5LUAVEPLe7u8Yg+cTJ48/gWjXGpQvQKfz/RbLBmkD1ry1N6ssse/vVjVt9N0=</X509Certificate></X509Data></KeyInfo></Signature></Reinf></retornoEvento></evento></retornoEventos></retornoLoteEventosAssincrono></Reinf>', '2026-02-10 16:55:55');

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
-- Índices para tabela `reinf_eventos`
--
ALTER TABLE `reinf_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_lote` (`id_lote`),
  ADD KEY `id_fornecedor` (`id_fornecedor`);

--
-- Índices para tabela `reinf_evento_pagamentos`
--
ALTER TABLE `reinf_evento_pagamentos`
  ADD PRIMARY KEY (`id_evento`,`id_pagamento`),
  ADD KEY `id_pagamento` (`id_pagamento`);

--
-- Índices para tabela `reinf_lotes`
--
ALTER TABLE `reinf_lotes`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `orgaos`
--
ALTER TABLE `orgaos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `reinf_eventos`
--
ALTER TABLE `reinf_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `reinf_lotes`
--
ALTER TABLE `reinf_lotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Limitadores para a tabela `reinf_eventos`
--
ALTER TABLE `reinf_eventos`
  ADD CONSTRAINT `reinf_eventos_ibfk_1` FOREIGN KEY (`id_lote`) REFERENCES `reinf_lotes` (`id`),
  ADD CONSTRAINT `reinf_eventos_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`);

--
-- Limitadores para a tabela `reinf_evento_pagamentos`
--
ALTER TABLE `reinf_evento_pagamentos`
  ADD CONSTRAINT `reinf_evento_pagamentos_ibfk_1` FOREIGN KEY (`id_evento`) REFERENCES `reinf_eventos` (`id`),
  ADD CONSTRAINT `reinf_evento_pagamentos_ibfk_2` FOREIGN KEY (`id_pagamento`) REFERENCES `pagamentos` (`id`);

--
-- Limitadores para a tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_orgao`) REFERENCES `orgaos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
