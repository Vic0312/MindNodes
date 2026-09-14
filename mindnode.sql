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
(27, 9, 'Nenhuma ligacao precisa ser alterada.', 0),
(28, 10, 'Define as operacoes disponiveis sem impor a implementacao interna.', 1),
(29, 10, 'Cria automaticamente uma pilha encadeada.', 0),
(30, 10, 'Determina como os nos devem ser armazenados na memoria.', 0),
(31, 11, 'Insere o novo no no inicio da lista.', 1),
(32, 11, 'Remove o primeiro no da lista.', 0),
(33, 11, 'Percorre a lista ate o ultimo no.', 0),
(34, 12, 'Insere novo apos atual e ajusta as referencias anterior e proximo.', 1),
(35, 12, 'Remove atual da lista.', 0),
(36, 12, 'Troca apenas os valores de dois nos.', 0);

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
(3, 'Lista Duplamente Encadeada', 'lista-dupla', 'Pratique conceitos de navegacao em duas direcoes, referencias anterior/proximo e remocoes.'),
(4, 'Fila Encadeada FIFO', 'fila-fifo', 'Explore inserções no fim, remoções no início e a ordem FIFO em nós encadeados.'),
(5, 'Fila de Prioridades Encadeada FIFO', 'fila-prioridade', 'Atenda primeiro a menor prioridade numérica e preserve FIFO nos empates.'),
(6, 'Pilha Encadeada', 'pilha-encadeada', 'Pratique LIFO com nós ligados por referências e operações concentradas no topo.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `quiz_pergunta`
--

CREATE TABLE `quiz_pergunta` (
  `id_pergunta` int(11) NOT NULL,
  `id_assunto` int(11) NOT NULL,
  `enunciado` text NOT NULL,
  `explicacao` text NOT NULL,
  `tipo` enum('teorica','codigo') NOT NULL DEFAULT 'teorica',
  `codigo` text DEFAULT NULL,
  `dica` text DEFAULT NULL
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

-- Questões de código dos três assuntos existentes.
INSERT INTO `quiz_pergunta` (`id_pergunta`, `id_assunto`, `enunciado`, `explicacao`, `tipo`, `codigo`, `dica`) VALUES
(10, 1, 'O que a interface IPilha define neste trecho?', 'A interface declara as operações públicas do TAD pilha; ela não determina a estrutura interna usada para implementá-las.', 'codigo', 'public interface IPilha
{
    void Empilhar(int valor);
    int Desempilhar();
}', 'Observe que há apenas assinaturas de métodos.'),
(11, 2, 'O que este método faz em uma lista simplesmente encadeada?', 'O novo nó aponta para o antigo início antes de se tornar o primeiro nó da lista.', 'codigo', 'public void InserirNoInicio(int valor)
{
    No novo = new No(valor);
    novo.Proximo = inicio;
    inicio = novo;
}', 'Acompanhe o valor de inicio antes e depois da última linha.'),
(12, 3, 'O que acontece com as ligações ao executar este trecho?', 'O novo nó entra após atual: aponta para seu antigo próximo, recebe atual como anterior e atualiza as referências dos nós vizinhos.', 'codigo', 'novo.Anterior = atual;
novo.Proximo = atual.Proximo;
if (atual.Proximo != null)
{
    atual.Proximo.Anterior = novo;
}
atual.Proximo = novo;', 'Observe as referências Anterior e Proximo dos nós vizinhos.');

UPDATE `quiz_pergunta` SET `dica` = 'Pense na diferença entre comportamento público e detalhes internos.' WHERE `id_pergunta` = 1;
UPDATE `quiz_pergunta` SET `dica` = 'Observe quantas referências cada nó precisa guardar.' WHERE `id_pergunta` = 4;
UPDATE `quiz_pergunta` SET `dica` = 'Compare os caminhos disponíveis para percorrer a lista.' WHERE `id_pergunta` = 7;

-- Três novos assuntos: três questões teóricas e três de código por assunto.
-- Na fila de prioridades, menor valor numérico significa maior prioridade.
INSERT INTO `quiz_pergunta` (`id_pergunta`, `id_assunto`, `enunciado`, `explicacao`, `tipo`, `codigo`, `dica`) VALUES
(13, 4, 'Após Enfileirar(8), Enfileirar(3) e Enfileirar(5), quais valores são lidos por duas remoções sucessivas?', 'A fila remove pelo início. Como 8 entrou antes de 3 e 5, as duas primeiras remoções devolvem 8 e 3.', 'teorica', NULL, 'Acompanhe a ordem de entrada, não o tamanho dos valores.'),
(14, 4, 'Em uma fila encadeada com referências inicio e fim, qual estado deve restar depois de remover seu único nó?', 'Depois da remoção, não há primeiro nem último nó; inicio e fim devem ser nulos para representar a fila vazia.', 'teorica', NULL, 'Considere se alguma referência ainda deve apontar para o nó removido.'),
(15, 4, 'Por que inserir pelo fim e remover pelo início preserva FIFO sem percorrer todos os nós?', 'A referência fim permite anexar o novo nó diretamente; inicio aponta para o próximo nó a sair. Assim, ambas as operações usam as extremidades corretas.', 'teorica', NULL, 'Identifique qual extremidade recebe e qual extremidade entrega nós.'),
(16, 4, 'No método abaixo, qual é o estado de uma fila vazia após Enfileirar(7)?', 'No caso vazio, o único nó é simultaneamente primeiro e último; as duas referências apontam para ele.', 'codigo', 'public void Enfileirar(int valor)
{
    No novo = new No(valor);
    if (inicio == null)
    {
        inicio = novo;
        fim = novo;
    }
    else
    {
        fim.Proximo = novo;
        fim = novo;
    }
}', 'Observe as duas atribuições no bloco do caso vazio.'),
(17, 4, 'Após executar o método em uma fila 4 → 9 → 2, que valor é devolvido e qual é o novo início?', 'O método lê o valor do primeiro nó, avança inicio para o próximo e devolve o valor lido: 4, com 9 no início.', 'codigo', 'public int Desenfileirar()
{
    if (inicio == null) throw new InvalidOperationException();
    int valor = inicio.Valor;
    inicio = inicio.Proximo;
    if (inicio == null) fim = null;
    return valor;
}', 'Acompanhe o valor salvo antes de atualizar inicio.'),
(18, 4, 'No trecho abaixo, por que fim.Proximo recebe novo antes de fim ser atualizado?', 'A atribuição liga o antigo último nó ao novo; só depois a referência fim passa a apontar para o último nó atualizado.', 'codigo', 'public void Enfileirar(int valor)
{
    No novo = new No(valor);
    if (fim == null) inicio = novo;
    else fim.Proximo = novo;
    fim = novo;
}', 'Compare a ligação entre nós com a referência externa fim.'),
(19, 5, 'Com menor número indicando maior prioridade, qual é a ordem de atendimento após inserir A(3), B(1) e C(2)?', 'A fila ordena por prioridade numérica crescente: B(1), C(2), A(3).', 'teorica', NULL, 'Ordene os números de prioridade antes de considerar a ordem de chegada.'),
(20, 5, 'A(1) chega antes de B(1), e depois chega C(2). Qual ordem respeita prioridade e FIFO no empate?', 'A e B têm prioridade maior que C porque 1 é menor que 2. Entre A e B, a ordem de chegada permanece A antes de B.', 'teorica', NULL, 'Em prioridades iguais, preserve a ordem de inserção.'),
(21, 5, 'Em uma fila de prioridades encadeada ordenada, de onde se remove o próximo elemento e qual é o custo para localizar esse nó?', 'O próximo atendimento está no início da lista ordenada; a referência inicio permite acessá-lo diretamente, sem busca pela lista.', 'teorica', NULL, 'Pense onde permanece o nó de maior prioridade após cada inserção.'),
(22, 5, 'Considerando menor número como maior prioridade, onde o novo nó de prioridade 1 entra nesta lista ordenada?', 'Como 1 é menor que 2, o novo nó precede o início atual e passa a ser a referência inicio.', 'codigo', '// inicio aponta para um nó de prioridade 2.
No novo = new No(7, 1);
if (inicio == null || novo.Prioridade < inicio.Prioridade)
{
    novo.Proximo = inicio;
    inicio = novo;
}', 'Compare a prioridade nova com a do primeiro nó.'),
(23, 5, 'Por que o laço abaixo usa <= ao avançar sobre nós da mesma prioridade?', 'O avanço atravessa também os nós com prioridade igual, inserindo o novo após eles e preservando FIFO no empate.', 'codigo', '// inicio não é nulo e novo não precede inicio.
No atual = inicio;
while (atual.Proximo != null &&
       atual.Proximo.Prioridade <= novo.Prioridade)
{
    atual = atual.Proximo;
}
novo.Proximo = atual.Proximo;
atual.Proximo = novo;', 'Observe o que acontece quando Proximo.Prioridade é igual à de novo.'),
(24, 5, 'Na lista A(1) → B(1) → C(3), onde o trecho insere D(2)?', 'O laço passa por B(1) e para antes de C(3); D(2) fica entre B e C, mantendo ordem crescente de prioridade.', 'codigo', '// novo representa D, com Prioridade = 2.
No atual = inicio;
while (atual.Proximo != null &&
       atual.Proximo.Prioridade <= novo.Prioridade)
    atual = atual.Proximo;
novo.Proximo = atual.Proximo;
atual.Proximo = novo;', 'Acompanhe a condição do laço para o próximo nó.'),
(25, 6, 'Após Empilhar(10), Empilhar(20) e Empilhar(30) em uma pilha de nós, quais valores saem em duas remoções?', 'Cada empilhamento cria o novo topo; 30 sai primeiro, seguido de 20, conforme LIFO.', 'teorica', NULL, 'Acompanhe qual nó passa a ser o topo após cada inserção.'),
(26, 6, 'Em uma pilha encadeada, qual ligação permite manter os elementos anteriores ao empilhar um novo nó?', 'O campo Proximo do novo nó aponta para o antigo topo antes de topo receber o novo nó; assim a cadeia anterior permanece acessível.', 'teorica', NULL, 'Considere para onde o novo nó deve apontar antes de mudar topo.'),
(27, 6, 'Depois de desempilhar o único nó de uma pilha encadeada, qual é o estado correto?', 'O topo avança para Proximo, que é nulo no único nó; a pilha fica vazia com topo nulo.', 'teorica', NULL, 'Observe o campo Proximo de um nó sem sucessor.'),
(28, 6, 'Se topo aponta para o nó 12 antes deste método, o que ocorre após Empilhar(25)?', 'O novo nó 25 aponta para o antigo topo 12 e se torna o topo; a sequência passa a ser 25 → 12.', 'codigo', 'public void Empilhar(int valor)
{
    No novo = new No(valor);
    novo.Proximo = topo;
    topo = novo;
}', 'Leia as atribuições na ordem em que são executadas.'),
(29, 6, 'Com topo em 9 → 4, qual valor o método retorna e onde topo passa a apontar?', 'O método salva 9, avança topo para o próximo nó 4 e retorna 9. O nó 4 permanece na pilha.', 'codigo', 'public int Desempilhar()
{
    if (topo == null) throw new InvalidOperationException();
    int valor = topo.Valor;
    topo = topo.Proximo;
    return valor;
}', 'Identifique o valor lido antes da mudança da referência topo.'),
(30, 6, 'O que acontece ao chamar este método quando a pilha encadeada está vazia?', 'Com topo nulo, o método lança InvalidOperationException antes de acessar Valor; nenhum nó é criado.', 'codigo', 'public int ConsultarTopo()
{
    if (topo == null) throw new InvalidOperationException();
    return topo.Valor;
}', 'Observe a condição verificada antes do acesso ao nó.');

INSERT INTO `quiz_alternativa` (`id_alternativa`, `id_pergunta`, `texto`, `correta`) VALUES
(37, 13, '8 e 3', 1),
(38, 13, '5 e 3', 0),
(39, 13, '3 e 5', 0),
(40, 13, '8 e 5', 0),
(41, 14, 'Apenas inicio fica nulo; fim mantém o nó antigo.', 0),
(42, 14, 'Apenas fim fica nulo; inicio mantém o nó antigo.', 0),
(43, 14, 'inicio e fim ficam nulos.', 1),
(44, 14, 'inicio e fim passam a apontar para um novo nó.', 0),
(45, 15, 'Porque cada novo nó substitui inicio.', 0),
(46, 15, 'Porque fim localiza a inserção e inicio localiza a remoção.', 1),
(47, 15, 'Porque os nós ficam ordenados pelo valor.', 0),
(48, 15, 'Porque Proximo aponta para todos os nós anteriores.', 0),
(49, 16, 'inicio aponta para novo e fim fica nulo.', 0),
(50, 16, 'inicio e fim apontam para novo.', 1),
(51, 16, 'fim aponta para novo e inicio permanece nulo.', 0),
(52, 16, 'novo.Proximo aponta para inicio.', 0),
(53, 17, 'Devolve 2; início passa a 9.', 0),
(54, 17, 'Devolve 4; início passa a 9.', 1),
(55, 17, 'Devolve 9; início passa a 2.', 0),
(56, 17, 'Devolve 4; início passa a 2.', 0),
(57, 18, 'Para ligar o antigo último nó ao novo nó.', 1),
(58, 18, 'Para remover o primeiro nó.', 0),
(59, 18, 'Para ordenar os nós pelo valor.', 0),
(60, 18, 'Para fazer novo apontar para o antigo inicio.', 0),
(61, 19, 'A, C, B', 0),
(62, 19, 'B, C, A', 1),
(63, 19, 'C, B, A', 0),
(64, 19, 'A, B, C', 0),
(65, 20, 'B, A, C', 0),
(66, 20, 'C, A, B', 0),
(67, 20, 'A, B, C', 1),
(68, 20, 'A, C, B', 0),
(69, 21, 'Do fim, após percorrer todos os nós.', 0),
(70, 21, 'Do início, por acesso direto à referência inicio.', 1),
(71, 21, 'De uma posição aleatória, por busca binária.', 0),
(72, 21, 'Do nó de maior valor, após ordenar novamente.', 0),
(73, 22, 'Depois de todos os nós de prioridade 2.', 0),
(74, 22, 'Antes do antigo início.', 1),
(75, 22, 'No fim da lista, sem alterar inicio.', 0),
(76, 22, 'No lugar do antigo início, descartando-o.', 0),
(77, 23, 'Para inserir novo antes dos nós de mesma prioridade.', 0),
(78, 23, 'Para manter novo após quem chegou antes com a mesma prioridade.', 1),
(79, 23, 'Para inverter a ordem dos nós de prioridade menor.', 0),
(80, 23, 'Para remover nós de prioridade repetida.', 0),
(81, 24, 'Antes de A(1).', 0),
(82, 24, 'Entre A(1) e B(1).', 0),
(83, 24, 'Entre B(1) e C(3).', 1),
(84, 24, 'Depois de C(3).', 0),
(85, 25, '10 e 20', 0),
(86, 25, '30 e 20', 1),
(87, 25, '20 e 10', 0),
(88, 25, '30 e 10', 0),
(89, 26, 'novo.Proximo aponta para o antigo topo.', 1),
(90, 26, 'O antigo topo aponta para todos os nós anteriores.', 0),
(91, 26, 'topo aponta para o último nó da cadeia.', 0),
(92, 26, 'Todos os nós são copiados para um vetor.', 0),
(93, 27, 'topo permanece no nó removido.', 0),
(94, 27, 'topo aponta para um vetor vazio.', 0),
(95, 27, 'topo fica nulo.', 1),
(96, 27, 'topo aponta para um novo nó com valor zero.', 0),
(97, 28, 'topo continua em 12 e 25 vai ao fim.', 0),
(98, 28, 'topo passa a 25, que aponta para 12.', 1),
(99, 28, 'topo passa a 25 e o nó 12 é perdido.', 0),
(100, 28, 'O valor 25 substitui o valor 12 no mesmo nó.', 0),
(101, 29, 'Retorna 4; topo permanece em 9.', 0),
(102, 29, 'Retorna 9; topo passa a 4.', 1),
(103, 29, 'Retorna 9; topo fica nulo.', 0),
(104, 29, 'Retorna 4; topo fica nulo.', 0),
(105, 30, 'Retorna zero sem alterar topo.', 0),
(106, 30, 'Cria um nó de valor zero.', 0),
(107, 30, 'Lança InvalidOperationException.', 1),
(108, 30, 'Retorna o valor do último nó removido.', 0);

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
  `moedas_ganhas` int(11) NOT NULL DEFAULT 0,
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
  `senha` varchar(255) NOT NULL,
  `foto_perfil` longblob DEFAULT NULL,
  `moedas` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `cpf`, `nome`, `sobrenome`, `dataNasc`, `telefone`, `email`, `senha`, `foto_perfil`) VALUES
(1, '11111111111', 'Maria', 'Brito', '2007-12-31', '18997289078', 'maria@gmail.com', '1234', 0x75706c6f6164732f7573756172696f732f70657266696c5f36613164626431666161373632332e31303834313636312e6a706567),
(2, '22222222222', 'Bruno', 'Lima', '2000-04-13', '189945367', 'bru@gmail.com', '123', 0x75706c6f6164732f7573756172696f732f70657266696c5f36613164646138643166383763332e38343139343339312e6a706567);

-- --------------------------------------------------------

--
-- Estrutura da tabela `item`
--

CREATE TABLE `item` (
  `id_item` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` enum('cabelo','rosto','roupa','acessorio') NOT NULL,
  `preco` int(11) NOT NULL DEFAULT 0,
  `imagem` varchar(255) NOT NULL,
  `habilidade` varchar(100) DEFAULT NULL,
  `descricao_habilidade` text DEFAULT NULL,
  `valor_habilidade` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dados iniciais da tabela `item`
--

INSERT INTO `item` (`id_item`, `nome`, `descricao`, `categoria`, `preco`, `imagem`, `habilidade`, `descricao_habilidade`, `valor_habilidade`, `ativo`) VALUES
(1, 'Cabelo Padrão', 'Visual inicial gratuito para o cabelo do avatar.', 'cabelo', 0, 'img/avatar/cabelo/cabelo_padrao.png', NULL, NULL, 0, 1),
(2, 'Rosto Padrão', 'Visual inicial gratuito para o rosto do avatar.', 'rosto', 0, 'img/avatar/rosto/rosto_padrao.png', NULL, NULL, 0, 1),
(3, 'Roupa Padrão', 'Visual inicial gratuito para a roupa do avatar.', 'roupa', 0, 'img/avatar/roupa/roupa_padrao.png', NULL, NULL, 0, 1),
(4, 'Boné FIFO', 'Boné inspirado no princípio First In, First Out.', 'cabelo', 100, 'img/avatar/cabelo/bone_fifo.png', 'eliminar_alternativa', 'Elimina uma alternativa incorreta de uma questão do Quiz.', 1, 1),
(5, 'Óculos Debug', 'Óculos para encontrar erros com mais estilo.', 'rosto', 120, 'img/avatar/rosto/oculos_debug.png', 'dica', 'Permite visualizar uma dica em uma questão do Quiz.', 1, 1),
(6, 'Camiseta Stack', 'Camiseta inspirada no princípio Last In, First Out.', 'roupa', 150, 'img/avatar/roupa/camiseta_stack.png', 'resumo_rapido', 'Permite consultar um resumo rápido do assunto durante o Quiz.', 1, 1);

-- --------------------------------------------------------

CREATE TABLE `usuario_item` (
  `id_usuario_item` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_item` int(11) NOT NULL,
  `data_compra` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Itens básicos disponíveis no inventário dos usuários existentes
--

INSERT INTO `usuario_item` (`id_usuario_item`, `id_usuario`, `id_item`, `data_compra`) VALUES
(1, 1, 1, current_timestamp()),
(2, 1, 2, current_timestamp()),
(3, 1, 3, current_timestamp()),
(4, 2, 1, current_timestamp()),
(5, 2, 2, current_timestamp()),
(6, 2, 3, current_timestamp());

-- --------------------------------------------------------

CREATE TABLE `avatar_usuario` (
  `id_avatar` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cabelo` int(11) DEFAULT NULL,
  `id_rosto` int(11) DEFAULT NULL,
  `id_roupa` int(11) DEFAULT NULL,
  `id_acessorio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Avatar inicial dos usuários existentes
--

INSERT INTO `avatar_usuario` (`id_avatar`, `id_usuario`, `id_cabelo`, `id_rosto`, `id_roupa`, `id_acessorio`) VALUES
(1, 1, 1, 2, 3, NULL),
(2, 2, 1, 2, 3, NULL);

-- --------------------------------------------------------

CREATE TABLE `transacao_moeda` (
  `id_transacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('credito','debito') NOT NULL,
  `valor` int(11) NOT NULL,
  `origem` enum('quiz','loja','bonus') NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `data_transacao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uk_usuario_email` (`email`);

ALTER TABLE `usuario`
ADD UNIQUE KEY `uk_usuario_cpf` (`cpf`);

ALTER TABLE `item`
  ADD PRIMARY KEY (`id_item`);

ALTER TABLE `usuario_item`
  ADD PRIMARY KEY (`id_usuario_item`),
  ADD UNIQUE KEY `uk_usuario_item` (`id_usuario`, `id_item`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_item` (`id_item`);

ALTER TABLE `avatar_usuario`
  ADD PRIMARY KEY (`id_avatar`),
  ADD UNIQUE KEY `uk_avatar_usuario` (`id_usuario`),
  ADD KEY `id_cabelo` (`id_cabelo`),
  ADD KEY `id_rosto` (`id_rosto`),
  ADD KEY `id_roupa` (`id_roupa`),
  ADD KEY `id_acessorio` (`id_acessorio`);

ALTER TABLE `transacao_moeda`
  ADD PRIMARY KEY (`id_transacao`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `quiz_alternativa`
--
ALTER TABLE `quiz_alternativa`
  MODIFY `id_alternativa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT de tabela `quiz_assunto`
--
ALTER TABLE `quiz_assunto`
  MODIFY `id_assunto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `quiz_pergunta`
--
ALTER TABLE `quiz_pergunta`
  MODIFY `id_pergunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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

ALTER TABLE `item`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `usuario_item`
  MODIFY `id_usuario_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `avatar_usuario`
  MODIFY `id_avatar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `transacao_moeda`
  MODIFY `id_transacao` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `usuario_item`
  ADD CONSTRAINT `usuario_item_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_item_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `item` (`id_item`) ON DELETE CASCADE;

ALTER TABLE `avatar_usuario`
  ADD CONSTRAINT `avatar_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `avatar_usuario_ibfk_2` FOREIGN KEY (`id_cabelo`) REFERENCES `item` (`id_item`) ON DELETE SET NULL,
  ADD CONSTRAINT `avatar_usuario_ibfk_3` FOREIGN KEY (`id_rosto`) REFERENCES `item` (`id_item`) ON DELETE SET NULL,
  ADD CONSTRAINT `avatar_usuario_ibfk_4` FOREIGN KEY (`id_roupa`) REFERENCES `item` (`id_item`) ON DELETE SET NULL,
  ADD CONSTRAINT `avatar_usuario_ibfk_5` FOREIGN KEY (`id_acessorio`) REFERENCES `item` (`id_item`) ON DELETE SET NULL;

ALTER TABLE `transacao_moeda`
  ADD CONSTRAINT `transacao_moeda_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
