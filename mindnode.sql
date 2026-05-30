-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Banco de dados: `mindnode`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

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

CREATE TABLE `quiz_assunto` (
  `id_assunto` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(120) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `descricao` text NOT NULL,
  PRIMARY KEY (`id_assunto`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `quiz_assunto` (`id_assunto`, `titulo`, `slug`, `descricao`) VALUES
(1, 'TAD - Tipo Abstrato de Dados', 'tad', 'Revise abstracao, operacoes e separacao entre comportamento e implementacao.'),
(2, 'Lista Simplesmente Encadeada', 'lista-simples', 'Teste seu entendimento sobre nos, ponteiros para o proximo elemento e operacoes basicas.'),
(3, 'Lista Duplamente Encadeada', 'lista-dupla', 'Pratique conceitos de navegacao em duas direcoes, referencias anterior/proximo e remocoes.');

CREATE TABLE `quiz_pergunta` (
  `id_pergunta` int(11) NOT NULL AUTO_INCREMENT,
  `id_assunto` int(11) NOT NULL,
  `enunciado` text NOT NULL,
  `explicacao` text NOT NULL,
  PRIMARY KEY (`id_pergunta`),
  KEY `id_assunto` (`id_assunto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `quiz_alternativa` (
  `id_alternativa` int(11) NOT NULL AUTO_INCREMENT,
  `id_pergunta` int(11) NOT NULL,
  `texto` text NOT NULL,
  `correta` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_alternativa`),
  KEY `id_pergunta` (`id_pergunta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `quiz_tentativa` (
  `id_tentativa` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_assunto` int(11) NOT NULL,
  `total_perguntas` int(11) NOT NULL,
  `total_acertos` int(11) NOT NULL,
  `data_tentativa` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tentativa`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_assunto` (`id_assunto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `quiz_resposta` (
  `id_resposta` int(11) NOT NULL AUTO_INCREMENT,
  `id_tentativa` int(11) NOT NULL,
  `id_pergunta` int(11) NOT NULL,
  `id_alternativa_marcada` int(11) DEFAULT NULL,
  `id_alternativa_correta` int(11) NOT NULL,
  `acertou` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_resposta`),
  KEY `id_tentativa` (`id_tentativa`),
  KEY `id_pergunta` (`id_pergunta`),
  KEY `id_alternativa_marcada` (`id_alternativa_marcada`),
  KEY `id_alternativa_correta` (`id_alternativa_correta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `quiz_pergunta`
  ADD CONSTRAINT `quiz_pergunta_ibfk_1` FOREIGN KEY (`id_assunto`) REFERENCES `quiz_assunto` (`id_assunto`) ON DELETE CASCADE;

ALTER TABLE `quiz_alternativa`
  ADD CONSTRAINT `quiz_alternativa_ibfk_1` FOREIGN KEY (`id_pergunta`) REFERENCES `quiz_pergunta` (`id_pergunta`) ON DELETE CASCADE;

ALTER TABLE `quiz_tentativa`
  ADD CONSTRAINT `quiz_tentativa_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_tentativa_ibfk_2` FOREIGN KEY (`id_assunto`) REFERENCES `quiz_assunto` (`id_assunto`) ON DELETE CASCADE;

ALTER TABLE `quiz_resposta`
  ADD CONSTRAINT `quiz_resposta_ibfk_1` FOREIGN KEY (`id_tentativa`) REFERENCES `quiz_tentativa` (`id_tentativa`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_resposta_ibfk_2` FOREIGN KEY (`id_pergunta`) REFERENCES `quiz_pergunta` (`id_pergunta`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_resposta_ibfk_3` FOREIGN KEY (`id_alternativa_marcada`) REFERENCES `quiz_alternativa` (`id_alternativa`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_resposta_ibfk_4` FOREIGN KEY (`id_alternativa_correta`) REFERENCES `quiz_alternativa` (`id_alternativa`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
