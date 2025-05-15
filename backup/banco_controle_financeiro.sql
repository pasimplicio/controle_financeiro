-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/05/2025 às 16:18
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `controle_financeiro`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `dimcartaocredito`
--

CREATE TABLE `dimcartaocredito` (
  `IDCartao` int(11) NOT NULL,
  `IDContaAssociada` int(11) DEFAULT NULL,
  `NomeCartao` varchar(100) NOT NULL,
  `Bandeira` varchar(50) DEFAULT NULL,
  `LimiteTotal` decimal(15,2) DEFAULT NULL,
  `DiaFechamento` int(11) DEFAULT NULL,
  `DiaVencimento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dimcartaocredito`
--

INSERT INTO `dimcartaocredito` (`IDCartao`, `IDContaAssociada`, `NomeCartao`, `Bandeira`, `LimiteTotal`, `DiaFechamento`, `DiaVencimento`) VALUES
(1, 2, 'Nubank Platinum', 'Mastercard', 10000.00, 25, 5),
(2, 2, 'Nubank Internacional', 'Visa', 5000.00, 20, 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `dimcategoria`
--

CREATE TABLE `dimcategoria` (
  `IDCategoria` int(11) NOT NULL,
  `NomeCategoria` varchar(100) NOT NULL,
  `TipoCategoria` varchar(50) NOT NULL,
  `GrupoCategoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dimcategoria`
--

INSERT INTO `dimcategoria` (`IDCategoria`, `NomeCategoria`, `TipoCategoria`, `GrupoCategoria`) VALUES
(1, 'Alimentação', 'Despesa Variável teste', 'Despesa Variável teste'),
(2, 'Transporte', 'Despesa Fixa', 'Mobilidade');

-- --------------------------------------------------------

--
-- Estrutura para tabela `dimconta`
--

CREATE TABLE `dimconta` (
  `IDConta` int(11) NOT NULL,
  `NomeConta` varchar(100) NOT NULL,
  `TipoConta` varchar(50) NOT NULL,
  `InstituicaoFinanceira` varchar(100) DEFAULT NULL,
  `SaldoInicial` decimal(15,2) DEFAULT 0.00,
  `DataAbertura` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dimconta`
--

INSERT INTO `dimconta` (`IDConta`, `NomeConta`, `TipoConta`, `InstituicaoFinanceira`, `SaldoInicial`, `DataAbertura`) VALUES
(1, 'Conta Corrente Principal', 'Banco', 'Banco do Brasil', 5000.00, '2020-01-01'),
(2, 'Cartão de Crédito', 'Cartão', 'Nubank', 0.00, '2020-02-01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `dimdivida`
--

CREATE TABLE `dimdivida` (
  `IDDivida` int(11) NOT NULL,
  `IDContaAssociada` int(11) DEFAULT NULL,
  `DescricaoDivida` text DEFAULT NULL,
  `TipoDivida` varchar(50) DEFAULT NULL,
  `IDPessoaCredor` int(11) DEFAULT NULL,
  `ValorTotalContratado` decimal(15,2) DEFAULT NULL,
  `TaxaJurosMensal` decimal(5,2) DEFAULT NULL,
  `DataContratacao` date DEFAULT NULL,
  `PrazoTotalMeses` int(11) DEFAULT NULL,
  `ValorParcelaOriginal` decimal(15,2) DEFAULT NULL,
  `DiaVencimentoParcela` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dimdivida`
--

INSERT INTO `dimdivida` (`IDDivida`, `IDContaAssociada`, `DescricaoDivida`, `TipoDivida`, `IDPessoaCredor`, `ValorTotalContratado`, `TaxaJurosMensal`, `DataContratacao`, `PrazoTotalMeses`, `ValorParcelaOriginal`, `DiaVencimentoParcela`) VALUES
(1, 1, 'Empréstimo Pessoal', 'Pessoal', 1, 10000.00, 2.50, '2023-01-15', 24, 450.00, 15),
(2, 1, 'Financiamento Veículo', 'Financiamento', 1, 50000.00, 1.20, '2023-02-20', 60, 950.00, 20);

-- --------------------------------------------------------

--
-- Estrutura para tabela `diminstituicaocustodia`
--

CREATE TABLE `diminstituicaocustodia` (
  `IDInstituicaoCustodia` int(11) NOT NULL,
  `NomeInstituicao` varchar(100) NOT NULL,
  `CNPJ` varchar(18) DEFAULT NULL,
  `TipoInstituicao` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `diminstituicaocustodia`
--

INSERT INTO `diminstituicaocustodia` (`IDInstituicaoCustodia`, `NomeInstituicao`, `CNPJ`, `TipoInstituicao`) VALUES
(1, 'Banco do Brasil', '00.000.000/0001-91', 'Banco'),
(2, 'XP Investimentos', '02.332.886/0001-04', 'Corretora');

-- --------------------------------------------------------

--
-- Estrutura para tabela `diminvestimento`
--

CREATE TABLE `diminvestimento` (
  `IDInvestimento` int(11) NOT NULL,
  `IDContaAssociada` int(11) DEFAULT NULL,
  `DescricaoInvestimento` text DEFAULT NULL,
  `TipoInvestimento` varchar(50) DEFAULT NULL,
  `IDInstituicaoCustodia` int(11) DEFAULT NULL,
  `DataAplicacaoInicial` date DEFAULT NULL,
  `RentabilidadeEsperada` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `diminvestimento`
--

INSERT INTO `diminvestimento` (`IDInvestimento`, `IDContaAssociada`, `DescricaoInvestimento`, `TipoInvestimento`, `IDInstituicaoCustodia`, `DataAplicacaoInicial`, `RentabilidadeEsperada`) VALUES
(1, 1, 'Tesouro Direto', 'Renda Fixa', 2, '2023-03-01', 999.99),
(2, 1, 'Ações PETR4', 'Ações', 1, '2023-03-15', 12.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `dimpessoacredordevedor`
--

CREATE TABLE `dimpessoacredordevedor` (
  `IDPessoa` int(11) NOT NULL,
  `NomePessoa` varchar(100) NOT NULL,
  `TipoPessoa` varchar(50) NOT NULL,
  `Documento` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dimpessoacredordevedor`
--

INSERT INTO `dimpessoacredordevedor` (`IDPessoa`, `NomePessoa`, `TipoPessoa`, `Documento`) VALUES
(1, 'Banco Santander', 'Banco', '00.000.000/0001-00'),
(2, 'João Silva', 'Física', '123.456.789-00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `dimtipotransacao`
--

CREATE TABLE `dimtipotransacao` (
  `IDTipoTransacao` int(11) NOT NULL,
  `NomeTipoTransacao` varchar(100) NOT NULL,
  `Natureza` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dimtipotransacao`
--

INSERT INTO `dimtipotransacao` (`IDTipoTransacao`, `NomeTipoTransacao`, `Natureza`) VALUES
(1, 'Transferência', 'Bancária'),
(2, 'Pagamento', 'Comercial');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fatofaturacartao`
--

CREATE TABLE `fatofaturacartao` (
  `IDFatura` int(11) NOT NULL,
  `IDCartao` int(11) DEFAULT NULL,
  `DataFechamento` date DEFAULT NULL,
  `DataVencimento` date DEFAULT NULL,
  `ValorTotalFatura` decimal(15,2) DEFAULT NULL,
  `ValorPago` decimal(15,2) DEFAULT NULL,
  `DataPagamento` date DEFAULT NULL,
  `IDContaPagamento` int(11) DEFAULT NULL,
  `JurosEncargos` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fatofaturacartao`
--

INSERT INTO `fatofaturacartao` (`IDFatura`, `IDCartao`, `DataFechamento`, `DataVencimento`, `ValorTotalFatura`, `ValorPago`, `DataPagamento`, `IDContaPagamento`, `JurosEncargos`) VALUES
(1, 1, '2023-04-25', '2023-05-05', 1500.00, 1500.00, '2023-05-03', 1, 0.00),
(2, 2, '2023-04-20', '2023-05-10', 800.00, 800.00, '2023-05-08', 1, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fatomovimentacaoinvestimento`
--

CREATE TABLE `fatomovimentacaoinvestimento` (
  `IDMovInvest` int(11) NOT NULL,
  `IDInvestimento` int(11) DEFAULT NULL,
  `DataMovimentacao` date DEFAULT NULL,
  `IDTipoTransacao` int(11) DEFAULT NULL,
  `Valor` decimal(15,2) DEFAULT NULL,
  `IDContaOrigemDestino` int(11) DEFAULT NULL,
  `QuantidadeCotas` decimal(15,4) DEFAULT NULL,
  `PrecoUnitarioCota` decimal(15,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fatomovimentacaoinvestimento`
--

INSERT INTO `fatomovimentacaoinvestimento` (`IDMovInvest`, `IDInvestimento`, `DataMovimentacao`, `IDTipoTransacao`, `Valor`, `IDContaOrigemDestino`, `QuantidadeCotas`, `PrecoUnitarioCota`) VALUES
(1, 1, '2023-04-01', 1, 1000.00, 1, 1.0000, 1000.0000),
(2, 2, '2023-04-15', 1, 500.00, 1, 10.0000, 50.0000);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fatoparcelas`
--

CREATE TABLE `fatoparcelas` (
  `IDLancamentoParcela` int(11) NOT NULL,
  `IDDivida` int(11) DEFAULT NULL,
  `DataVencimentoParcela` date DEFAULT NULL,
  `DataPagamentoParcela` date DEFAULT NULL,
  `NumeroParcela` int(11) DEFAULT NULL,
  `ValorParcela` decimal(15,2) DEFAULT NULL,
  `ValorPago` decimal(15,2) DEFAULT NULL,
  `IDContaPagamento` int(11) DEFAULT NULL,
  `JurosMultaAtraso` decimal(15,2) DEFAULT NULL,
  `SaldoDevedorAposPgto` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fatoparcelas`
--

INSERT INTO `fatoparcelas` (`IDLancamentoParcela`, `IDDivida`, `DataVencimentoParcela`, `DataPagamentoParcela`, `NumeroParcela`, `ValorParcela`, `ValorPago`, `IDContaPagamento`, `JurosMultaAtraso`, `SaldoDevedorAposPgto`) VALUES
(1, 1, '2023-05-15', '2023-05-14', 1, 450.00, 450.00, 1, 0.00, 9550.00),
(2, 2, '2023-05-20', '2023-05-19', 1, 950.00, 950.00, 1, 0.00, 49050.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fatotransacoes`
--

CREATE TABLE `fatotransacoes` (
  `IDTransacao` int(11) NOT NULL,
  `DataTransacao` date DEFAULT NULL,
  `IDCategoria` int(11) DEFAULT NULL,
  `IDConta` int(11) DEFAULT NULL,
  `IDContaDestino` int(11) DEFAULT NULL,
  `IDTipoTransacao` int(11) DEFAULT NULL,
  `IDPessoa` int(11) DEFAULT NULL,
  `Descricao` text DEFAULT NULL,
  `Valor` decimal(15,2) NOT NULL,
  `Observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fatotransacoes`
--

INSERT INTO `fatotransacoes` (`IDTransacao`, `DataTransacao`, `IDCategoria`, `IDConta`, `IDContaDestino`, `IDTipoTransacao`, `IDPessoa`, `Descricao`, `Valor`, `Observacao`) VALUES
(1, '2023-05-01', 1, 1, 1, 1, 2, 'Supermercado teste', 350.00, '350.00'),
(2, '2023-05-02', 2, 1, 2, 1, 1, 'Combustível', 20000.00, 'Abastecimento');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `dimcartaocredito`
--
ALTER TABLE `dimcartaocredito`
  ADD PRIMARY KEY (`IDCartao`),
  ADD KEY `IDContaAssociada` (`IDContaAssociada`);

--
-- Índices de tabela `dimcategoria`
--
ALTER TABLE `dimcategoria`
  ADD PRIMARY KEY (`IDCategoria`);

--
-- Índices de tabela `dimconta`
--
ALTER TABLE `dimconta`
  ADD PRIMARY KEY (`IDConta`);

--
-- Índices de tabela `dimdivida`
--
ALTER TABLE `dimdivida`
  ADD PRIMARY KEY (`IDDivida`),
  ADD KEY `IDContaAssociada` (`IDContaAssociada`),
  ADD KEY `IDPessoaCredor` (`IDPessoaCredor`);

--
-- Índices de tabela `diminstituicaocustodia`
--
ALTER TABLE `diminstituicaocustodia`
  ADD PRIMARY KEY (`IDInstituicaoCustodia`);

--
-- Índices de tabela `diminvestimento`
--
ALTER TABLE `diminvestimento`
  ADD PRIMARY KEY (`IDInvestimento`),
  ADD KEY `IDContaAssociada` (`IDContaAssociada`),
  ADD KEY `diminvestimento_ibfk_2` (`IDInstituicaoCustodia`);

--
-- Índices de tabela `dimpessoacredordevedor`
--
ALTER TABLE `dimpessoacredordevedor`
  ADD PRIMARY KEY (`IDPessoa`);

--
-- Índices de tabela `dimtipotransacao`
--
ALTER TABLE `dimtipotransacao`
  ADD PRIMARY KEY (`IDTipoTransacao`);

--
-- Índices de tabela `fatofaturacartao`
--
ALTER TABLE `fatofaturacartao`
  ADD PRIMARY KEY (`IDFatura`),
  ADD KEY `IDCartao` (`IDCartao`),
  ADD KEY `IDContaPagamento` (`IDContaPagamento`);

--
-- Índices de tabela `fatomovimentacaoinvestimento`
--
ALTER TABLE `fatomovimentacaoinvestimento`
  ADD PRIMARY KEY (`IDMovInvest`),
  ADD KEY `IDInvestimento` (`IDInvestimento`),
  ADD KEY `IDTipoTransacao` (`IDTipoTransacao`),
  ADD KEY `IDContaOrigemDestino` (`IDContaOrigemDestino`);

--
-- Índices de tabela `fatoparcelas`
--
ALTER TABLE `fatoparcelas`
  ADD PRIMARY KEY (`IDLancamentoParcela`),
  ADD KEY `IDDivida` (`IDDivida`),
  ADD KEY `IDContaPagamento` (`IDContaPagamento`);

--
-- Índices de tabela `fatotransacoes`
--
ALTER TABLE `fatotransacoes`
  ADD PRIMARY KEY (`IDTransacao`),
  ADD KEY `IDCategoria` (`IDCategoria`),
  ADD KEY `IDConta` (`IDConta`),
  ADD KEY `IDContaDestino` (`IDContaDestino`),
  ADD KEY `IDTipoTransacao` (`IDTipoTransacao`),
  ADD KEY `IDPessoa` (`IDPessoa`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `dimcartaocredito`
--
ALTER TABLE `dimcartaocredito`
  MODIFY `IDCartao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `dimcategoria`
--
ALTER TABLE `dimcategoria`
  MODIFY `IDCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `dimconta`
--
ALTER TABLE `dimconta`
  MODIFY `IDConta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `dimdivida`
--
ALTER TABLE `dimdivida`
  MODIFY `IDDivida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `diminstituicaocustodia`
--
ALTER TABLE `diminstituicaocustodia`
  MODIFY `IDInstituicaoCustodia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `diminvestimento`
--
ALTER TABLE `diminvestimento`
  MODIFY `IDInvestimento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `dimpessoacredordevedor`
--
ALTER TABLE `dimpessoacredordevedor`
  MODIFY `IDPessoa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `dimtipotransacao`
--
ALTER TABLE `dimtipotransacao`
  MODIFY `IDTipoTransacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `fatofaturacartao`
--
ALTER TABLE `fatofaturacartao`
  MODIFY `IDFatura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `fatomovimentacaoinvestimento`
--
ALTER TABLE `fatomovimentacaoinvestimento`
  MODIFY `IDMovInvest` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `fatoparcelas`
--
ALTER TABLE `fatoparcelas`
  MODIFY `IDLancamentoParcela` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `fatotransacoes`
--
ALTER TABLE `fatotransacoes`
  MODIFY `IDTransacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `dimcartaocredito`
--
ALTER TABLE `dimcartaocredito`
  ADD CONSTRAINT `dimcartaocredito_ibfk_1` FOREIGN KEY (`IDContaAssociada`) REFERENCES `dimconta` (`IDConta`);

--
-- Restrições para tabelas `dimdivida`
--
ALTER TABLE `dimdivida`
  ADD CONSTRAINT `dimdivida_ibfk_1` FOREIGN KEY (`IDContaAssociada`) REFERENCES `dimconta` (`IDConta`),
  ADD CONSTRAINT `dimdivida_ibfk_2` FOREIGN KEY (`IDPessoaCredor`) REFERENCES `dimpessoacredordevedor` (`IDPessoa`);

--
-- Restrições para tabelas `diminvestimento`
--
ALTER TABLE `diminvestimento`
  ADD CONSTRAINT `diminvestimento_ibfk_1` FOREIGN KEY (`IDContaAssociada`) REFERENCES `dimconta` (`IDConta`),
  ADD CONSTRAINT `diminvestimento_ibfk_2` FOREIGN KEY (`IDInstituicaoCustodia`) REFERENCES `diminstituicaocustodia` (`IDInstituicaoCustodia`);

--
-- Restrições para tabelas `fatofaturacartao`
--
ALTER TABLE `fatofaturacartao`
  ADD CONSTRAINT `fatofaturacartao_ibfk_1` FOREIGN KEY (`IDCartao`) REFERENCES `dimcartaocredito` (`IDCartao`) ON DELETE CASCADE,
  ADD CONSTRAINT `fatofaturacartao_ibfk_5` FOREIGN KEY (`IDContaPagamento`) REFERENCES `dimconta` (`IDConta`);

--
-- Restrições para tabelas `fatomovimentacaoinvestimento`
--
ALTER TABLE `fatomovimentacaoinvestimento`
  ADD CONSTRAINT `fatomovimentacaoinvestimento_ibfk_1` FOREIGN KEY (`IDInvestimento`) REFERENCES `diminvestimento` (`IDInvestimento`),
  ADD CONSTRAINT `fatomovimentacaoinvestimento_ibfk_3` FOREIGN KEY (`IDTipoTransacao`) REFERENCES `dimtipotransacao` (`IDTipoTransacao`),
  ADD CONSTRAINT `fatomovimentacaoinvestimento_ibfk_4` FOREIGN KEY (`IDContaOrigemDestino`) REFERENCES `dimconta` (`IDConta`);

--
-- Restrições para tabelas `fatoparcelas`
--
ALTER TABLE `fatoparcelas`
  ADD CONSTRAINT `fatoparcelas_ibfk_1` FOREIGN KEY (`IDDivida`) REFERENCES `dimdivida` (`IDDivida`),
  ADD CONSTRAINT `fatoparcelas_ibfk_4` FOREIGN KEY (`IDContaPagamento`) REFERENCES `dimconta` (`IDConta`);

--
-- Restrições para tabelas `fatotransacoes`
--
ALTER TABLE `fatotransacoes`
  ADD CONSTRAINT `fatotransacoes_ibfk_2` FOREIGN KEY (`IDCategoria`) REFERENCES `dimcategoria` (`IDCategoria`),
  ADD CONSTRAINT `fatotransacoes_ibfk_4` FOREIGN KEY (`IDContaDestino`) REFERENCES `dimconta` (`IDConta`),
  ADD CONSTRAINT `fatotransacoes_ibfk_5` FOREIGN KEY (`IDTipoTransacao`) REFERENCES `dimtipotransacao` (`IDTipoTransacao`),
  ADD CONSTRAINT `fatotransacoes_ibfk_6` FOREIGN KEY (`IDPessoa`) REFERENCES `dimpessoacredordevedor` (`IDPessoa`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
