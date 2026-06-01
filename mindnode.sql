-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01-Jun-2026 às 21:45
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mindnode`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `quiz_alternativa`
--

CREATE TABLE `quiz_alternativa` (
  `id_alternativa` int(11) NOT NULL,
  `id_pergunta` int(11) NOT NULL,
  `texto` text NOT NULL,
  `correta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `quiz_alternativa`
--

INSERT INTO `quiz_alternativa` (`id_alternativa`, `id_pergunta`, `texto`, `correta`) VALUES
(1, 1, 'Apenas a aparencia visual de uma tela.', 0),
(2, 1, 'Os dados e as operacoes disponiveis, sem depender dos detalhes internos.', 1),
(3, 1, 'Somente o tipo fisico usado pelo banco de dados.', 0),
(4, 2, 'Porque mistura interface e implementacao no mesmo bloco.', 0),
(5, 2, 'Porque separa o uso da estrutura dos detalhes de implementacao.', 1),
(6, 2, 'Porque elimina a necessidade de criar metodos.', 0),
(7, 3, 'InserirNoFim e BuscarPorIndice.', 0),
(8, 3, 'Empilhar e Desempilhar.', 1),
(9, 3, 'Ordenar e Embaralhar.', 0),
(10, 4, 'Cada no aponta para o proximo no.', 1),
(11, 4, 'Cada no aponta sempre para todos os outros nos.', 0),
(12, 4, 'Os dados ficam obrigatoriamente em posicoes continuas de memoria.', 0),
(13, 5, 'O no e a cabeca da lista.', 0),
(14, 5, 'O no e o ultimo elemento da lista.', 1),
(15, 5, 'A lista esta sempre vazia.', 0),
(16, 6, 'Fazer o novo no apontar para o antigo inicio e depois atualizar a cabeca.', 1),
(17, 6, 'Apagar todos os nos antes de inserir.', 0),
(18, 6, 'Obrigatoriamente percorrer a lista inteira.', 0),
(19, 7, 'A lista dupla possui referencias para proximo e anterior.', 1),
(20, 7, 'A lista dupla so aceita numeros inteiros.', 0),
(21, 7, 'A lista dupla nao usa nos.', 0),
(22, 8, 'Permite navegacao para frente e para tras.', 1),
(23, 8, 'Impede remocoes no meio da lista.', 0),
(24, 8, 'Faz todos os nos ocuparem a mesma posicao.', 0),
(25, 9, 'Anterior aponta para proximo, e proximo aponta para anterior.', 1),
(26, 9, 'Apenas a cabeca da lista precisa mudar.', 0),
(27, 9, 'Nenhuma ligacao precisa ser alterada.', 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `quiz_assunto`
--

CREATE TABLE `quiz_assunto` (
  `id_assunto` int(11) NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `descricao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `quiz_assunto`
--

INSERT INTO `quiz_assunto` (`id_assunto`, `titulo`, `slug`, `descricao`) VALUES
(1, 'TAD - Tipo Abstrato de Dados', 'tad', 'Revise abstracao, operacoes e separacao entre comportamento e implementacao.'),
(2, 'Lista Simplesmente Encadeada', 'lista-simples', 'Teste seu entendimento sobre nos, ponteiros para o proximo elemento e operacoes basicas.'),
(3, 'Lista Duplamente Encadeada', 'lista-dupla', 'Pratique conceitos de navegacao em duas direcoes, referencias anterior/proximo e remocoes.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `quiz_pergunta`
--

CREATE TABLE `quiz_pergunta` (
  `id_pergunta` int(11) NOT NULL,
  `id_assunto` int(11) NOT NULL,
  `enunciado` text NOT NULL,
  `explicacao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `quiz_pergunta`
--

INSERT INTO `quiz_pergunta` (`id_pergunta`, `id_assunto`, `enunciado`, `explicacao`) VALUES
(1, 1, 'O que um Tipo Abstrato de Dados define principalmente?', 'Um TAD descreve quais dados existem e quais operacoes podem ser feitas, sem expor obrigatoriamente a implementacao interna.'),
(2, 1, 'Por que o TAD ajuda na organizacao do codigo?', 'Ele separa a forma de usar uma estrutura da forma como ela foi implementada, deixando o codigo mais modular.'),
(3, 1, 'Em uma pilha como TAD, quais operacoes representam melhor seu comportamento?', 'Empilhar e desempilhar representam o comportamento principal de uma pilha, seguindo a logica LIFO.'),
(4, 2, 'Qual e a principal caracteristica de uma lista simplesmente encadeada?', 'Cada no guarda um valor e uma referencia para o proximo no da sequencia.'),
(5, 2, 'O que acontece quando o campo proximo de um no e nulo?', 'Isso indica que aquele no e o ultimo elemento da lista.'),
(6, 2, 'Qual cuidado e necessario ao inserir um novo no no inicio da lista?', 'O novo no precisa apontar para o antigo primeiro no antes de se tornar a nova cabeca da lista.'),
(7, 3, 'O que diferencia uma lista duplamente encadeada de uma lista simplesmente encadeada?', 'Na lista dupla, cada no possui referencia para o proximo no e tambem para o no anterior.'),
(8, 3, 'Qual vantagem a referencia para o no anterior oferece?', 'Ela permite percorrer a lista nos dois sentidos e facilita algumas remocoes e insercoes.'),
(9, 3, 'Ao remover um no do meio de uma lista duplamente encadeada, quais ligacoes precisam ser ajustadas?', 'O no anterior deve apontar para o proximo, e o proximo deve apontar de volta para o anterior.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `quiz_resposta`
--

CREATE TABLE `quiz_resposta` (
  `id_resposta` int(11) NOT NULL,
  `id_tentativa` int(11) NOT NULL,
  `id_pergunta` int(11) NOT NULL,
  `id_alternativa_marcada` int(11) DEFAULT NULL,
  `id_alternativa_correta` int(11) NOT NULL,
  `acertou` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `quiz_resposta`
--

INSERT INTO `quiz_resposta` (`id_resposta`, `id_tentativa`, `id_pergunta`, `id_alternativa_marcada`, `id_alternativa_correta`, `acertou`) VALUES
(1, 3, 1, 2, 2, 1),
(2, 3, 2, 5, 5, 1),
(3, 3, 3, 8, 8, 1),
(4, 4, 7, 19, 19, 1),
(5, 4, 8, 24, 22, 0),
(6, 4, 9, 27, 25, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `quiz_tentativa`
--

CREATE TABLE `quiz_tentativa` (
  `id_tentativa` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_assunto` int(11) NOT NULL,
  `total_perguntas` int(11) NOT NULL,
  `total_acertos` int(11) NOT NULL,
  `data_tentativa` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `quiz_tentativa`
--

INSERT INTO `quiz_tentativa` (`id_tentativa`, `id_usuario`, `id_assunto`, `total_perguntas`, `total_acertos`, `data_tentativa`) VALUES
(3, 1, 1, 3, 3, '2026-06-01 14:35:48'),
(4, 2, 3, 3, 1, '2026-06-01 16:20:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `cpf` varchar(15) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `sobrenome` varchar(60) NOT NULL,
  `dataNasc` date NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `email` varchar(50) NOT NULL,
  `senha` varchar(20) NOT NULL,
  `foto_perfil` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `cpf`, `nome`, `sobrenome`, `dataNasc`, `telefone`, `email`, `senha`, `foto_perfil`) VALUES
(1, '11111111111', 'Maria', 'Brito', '2007-12-31', '18997289078', 'maria@gmail.com', '1234', 0x75706c6f6164732f7573756172696f732f70657266696c5f36613164626431666161373632332e31303834313636312e6a706567),
(2, '11111111111', 'Bruno', 'Lima', '2000-04-13', '189945367', 'bru@gmail.com', '123', 0x75706c6f6164732f7573756172696f732f70657266696c5f36613164646138643166383763332e38343139343339312e6a706567);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `quiz_alternativa`
--
ALTER TABLE `quiz_alternativa`
  ADD PRIMARY KEY (`id_alternativa`),
  ADD KEY `id_pergunta` (`id_pergunta`);

--
-- Índices para tabela `quiz_assunto`
--
ALTER TABLE `quiz_assunto`
  ADD PRIMARY KEY (`id_assunto`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices para tabela `quiz_pergunta`
--
ALTER TABLE `quiz_pergunta`
  ADD PRIMARY KEY (`id_pergunta`),
  ADD KEY `id_assunto` (`id_assunto`);

--
-- Índices para tabela `quiz_resposta`
--
ALTER TABLE `quiz_resposta`
  ADD PRIMARY KEY (`id_resposta`),
  ADD KEY `id_tentativa` (`id_tentativa`),
  ADD KEY `id_pergunta` (`id_pergunta`),
  ADD KEY `id_alternativa_marcada` (`id_alternativa_marcada`),
  ADD KEY `id_alternativa_correta` (`id_alternativa_correta`);

--
-- Índices para tabela `quiz_tentativa`
--
ALTER TABLE `quiz_tentativa`
  ADD PRIMARY KEY (`id_tentativa`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_assunto` (`id_assunto`);

--
-- Índices para tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `quiz_alternativa`
--
ALTER TABLE `quiz_alternativa`
  MODIFY `id_alternativa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `quiz_assunto`
--
ALTER TABLE `quiz_assunto`
  MODIFY `id_assunto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `quiz_pergunta`
--
ALTER TABLE `quiz_pergunta`
  MODIFY `id_pergunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `quiz_resposta`
--
ALTER TABLE `quiz_resposta`
  MODIFY `id_resposta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `quiz_tentativa`
--
ALTER TABLE `quiz_tentativa`
  MODIFY `id_tentativa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `quiz_alternativa`
--
ALTER TABLE `quiz_alternativa`
  ADD CONSTRAINT `quiz_alternativa_ibfk_1` FOREIGN KEY (`id_pergunta`) REFERENCES `quiz_pergunta` (`id_pergunta`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `quiz_pergunta`
--
ALTER TABLE `quiz_pergunta`
  ADD CONSTRAINT `quiz_pergunta_ibfk_1` FOREIGN KEY (`id_assunto`) REFERENCES `quiz_assunto` (`id_assunto`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `quiz_resposta`
--
ALTER TABLE `quiz_resposta`
  ADD CONSTRAINT `quiz_resposta_ibfk_1` FOREIGN KEY (`id_tentativa`) REFERENCES `quiz_tentativa` (`id_tentativa`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_resposta_ibfk_2` FOREIGN KEY (`id_pergunta`) REFERENCES `quiz_pergunta` (`id_pergunta`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_resposta_ibfk_3` FOREIGN KEY (`id_alternativa_marcada`) REFERENCES `quiz_alternativa` (`id_alternativa`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_resposta_ibfk_4` FOREIGN KEY (`id_alternativa_correta`) REFERENCES `quiz_alternativa` (`id_alternativa`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `quiz_tentativa`
--
ALTER TABLE `quiz_tentativa`
  ADD CONSTRAINT `quiz_tentativa_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_tentativa_ibfk_2` FOREIGN KEY (`id_assunto`) REFERENCES `quiz_assunto` (`id_assunto`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
