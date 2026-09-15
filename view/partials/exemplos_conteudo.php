<?php
// Conteúdo estático da View. C# é texto, nunca executado pela aplicação.
$exemplos = [];

$exemplos['tad'] = [
    'titulo' => 'TAD - Tipo Abstrato de Dados',
    'descricao' => 'O exemplo mostra encapsulamento: o TAD define comportamento/operações; a implementação pode variar. Aqui a classe Pilha guarda um vetor privado de capacidade fixa, diferente da Pilha Encadeada.',
    'operacoes' => ['Empilhar', 'Desempilhar', 'Topo', 'EstaVazia'],
    'observar' => ['Quem usa a classe chama operações sem acessar o vetor interno.', 'EstaVazia verifica o índice -1; Push rejeita capacidade cheia e Pop/Topo rejeitam pilha vazia.', 'LIFO é o contrato; o vetor é apenas a implementação escolhida.'],
    'complexidade' => 'Empilhar, Desempilhar, Topo e EstaVazia: O(1). Construtor e espaço: O(c), sendo c a capacidade do vetor.',
    'teoria' => 'estruturas.php',
    'quiz' => 'tad',
    'saida' => '20
20
10',
    'execucao' => 'Topo consulta 20 sem remover; Desempilhar devolve 20; a consulta seguinte encontra 10.',
    'codigo' => <<<'CS'
using System;

// TAD Pilha: operações públicas; vetor privado de capacidade fixa.
public class Pilha
{
    private int[] elementos;
    private int topo;

    public Pilha(int capacidade)
    {
        if (capacidade <= 0)
            throw new ArgumentOutOfRangeException(nameof(capacidade));

        elementos = new int[capacidade];
        topo = -1;
    }

    public bool EstaVazia()
    {
        return topo == -1;
    }

    public void Empilhar(int valor)
    {
        if (topo == elementos.Length - 1)
            throw new InvalidOperationException("A pilha está cheia.");

        topo++;
        elementos[topo] = valor;
    }

    public int Desempilhar()
    {
        if (EstaVazia())
            throw new InvalidOperationException("A pilha está vazia.");

        int valor = elementos[topo];
        topo--;
        return valor;
    }

    public int Topo()
    {
        if (EstaVazia())
            throw new InvalidOperationException("A pilha está vazia.");

        return elementos[topo];
    }
}
CS,
    'uso' => <<<'CS'
public static class Programa
{
    public static void Main()
    {
        Pilha pilha = new Pilha(3);
        pilha.Empilhar(10);
        pilha.Empilhar(20);
        Console.WriteLine(pilha.Topo());
        Console.WriteLine(pilha.Desempilhar());
        Console.WriteLine(pilha.Topo());
    }
}
CS,
];

$exemplos['simples'] = [
    'titulo' => 'Lista Simplesmente Encadeada',
    'descricao' => 'O exemplo mostra nós com Valor e Proximo, ligados a partir de inicio, com inserção, remoção, busca e percurso.',
    'operacoes' => ['InserirInicio', 'InserirFinal', 'Remover', 'Buscar', 'Exibir'],
    'observar' => ['InserirInicio liga o novo nó ao inicio antigo antes de atualizar inicio.', 'Remover retorna false se não encontrar o valor e retira apenas a primeira ocorrência.', 'Sem referência de fim, InserirFinal percorre a lista; Exibir avança por Proximo até null.'],
    'complexidade' => 'InserirInicio: O(1). InserirFinal, Remover, Buscar e Exibir: O(n) no pior caso. Espaço: O(n).',
    'teoria' => 'estruturas.php#lista-simples',
    'quiz' => 'lista-simples',
    'saida' => '10 -> 20 -> 30
True
10 -> 30',
    'execucao' => 'A busca encontra 20. Ao removê-lo, a ligação de 10 passa a apontar para 30.',
    'codigo' => <<<'CS'
using System;

public class No
{
    public int Valor { get; set; }
    public No Proximo { get; set; }

    public No(int valor)
    {
        Valor = valor;
        Proximo = null;
    }
}

public class ListaSimples
{
    private No inicio = null;

    public void InserirInicio(int valor)
    {
        No novo = new No(valor);
        novo.Proximo = inicio;
        inicio = novo;
    }

    public void InserirFinal(int valor)
    {
        No novo = new No(valor);
        if (inicio == null)
        {
            inicio = novo;
            return;
        }

        No atual = inicio;
        while (atual.Proximo != null)
            atual = atual.Proximo;

        atual.Proximo = novo;
    }

    // Remove somente a primeira ocorrência do valor.
    public bool Remover(int valor)
    {
        if (inicio == null) return false;
        if (inicio.Valor == valor)
        {
            inicio = inicio.Proximo;
            return true;
        }

        No atual = inicio;
        while (atual.Proximo != null && atual.Proximo.Valor != valor)
            atual = atual.Proximo;

        if (atual.Proximo == null) return false;
        atual.Proximo = atual.Proximo.Proximo;
        return true;
    }

    public bool Buscar(int valor)
    {
        No atual = inicio;
        while (atual != null)
        {
            if (atual.Valor == valor) return true;
            atual = atual.Proximo;
        }
        return false;
    }

    public void Exibir()
    {
        No atual = inicio;
        while (atual != null)
        {
            Console.Write(atual.Valor);
            if (atual.Proximo != null) Console.Write(" -> ");
            atual = atual.Proximo;
        }
        Console.WriteLine();
    }
}
CS,
    'uso' => <<<'CS'
public static class Programa
{
    public static void Main()
    {
        ListaSimples lista = new ListaSimples();
        lista.InserirInicio(20);
        lista.InserirInicio(10);
        lista.InserirFinal(30);
        lista.Exibir();
        Console.WriteLine(lista.Buscar(20));
        lista.Remover(20);
        lista.Exibir();
    }
}
CS,
];

$exemplos['dupla'] = [
    'titulo' => 'Lista Duplamente Encadeada',
    'descricao' => 'O exemplo mostra nós com Anterior e Proximo e referências inicio/fim para inserir nas extremidades e percorrer nos dois sentidos.',
    'operacoes' => ['InserirInicio', 'InserirFinal', 'Remover', 'ExibirFrente', 'ExibirTras'],
    'observar' => ['Toda inserção mantém a ligação de ida e a de volta.', 'Remover reconecta os dois vizinhos e atualiza inicio/fim nos extremos, inclusive ao remover o único nó.', 'A remoção busca a primeira ocorrência por valor: essa busca custa O(n), embora religar vizinhos seja O(1).'],
    'complexidade' => 'InserirInicio e InserirFinal: O(1). Remover por valor e os dois percursos: O(n) no pior caso. Espaço: O(n).',
    'teoria' => 'estruturas.php#lista-dupla',
    'quiz' => 'lista-dupla',
    'saida' => '10 -> 20 -> 30
30 -> 20 -> 10
10 -> 30
30 -> 10',
    'execucao' => 'O percurso inverso usa Anterior. Após remover 20, 10 e 30 ficam ligados nos dois sentidos.',
    'codigo' => <<<'CS'
using System;

public class No
{
    public int Valor { get; set; }
    public No Anterior { get; set; }
    public No Proximo { get; set; }

    public No(int valor)
    {
        Valor = valor;
        Anterior = null;
        Proximo = null;
    }
}

public class ListaDupla
{
    private No inicio = null;
    private No fim = null;

    public void InserirInicio(int valor)
    {
        No novo = new No(valor);
        novo.Proximo = inicio;
        if (inicio == null)
            fim = novo;
        else
            inicio.Anterior = novo;
        inicio = novo;
    }

    public void InserirFinal(int valor)
    {
        No novo = new No(valor);
        novo.Anterior = fim;
        if (fim == null)
            inicio = novo;
        else
            fim.Proximo = novo;
        fim = novo;
    }

    // Busca e remove somente a primeira ocorrência do valor.
    public bool Remover(int valor)
    {
        No atual = inicio;
        while (atual != null && atual.Valor != valor)
            atual = atual.Proximo;
        if (atual == null) return false;

        if (atual.Anterior == null)
            inicio = atual.Proximo;
        else
            atual.Anterior.Proximo = atual.Proximo;

        if (atual.Proximo == null)
            fim = atual.Anterior;
        else
            atual.Proximo.Anterior = atual.Anterior;
        return true;
    }

    public void ExibirFrente()
    {
        No atual = inicio;
        while (atual != null)
        {
            Console.Write(atual.Valor);
            if (atual.Proximo != null) Console.Write(" -> ");
            atual = atual.Proximo;
        }
        Console.WriteLine();
    }

    public void ExibirTras()
    {
        No atual = fim;
        while (atual != null)
        {
            Console.Write(atual.Valor);
            if (atual.Anterior != null) Console.Write(" -> ");
            atual = atual.Anterior;
        }
        Console.WriteLine();
    }
}
CS,
    'uso' => <<<'CS'
public static class Programa
{
    public static void Main()
    {
        ListaDupla lista = new ListaDupla();
        lista.InserirInicio(20);
        lista.InserirInicio(10);
        lista.InserirFinal(30);
        lista.ExibirFrente();
        lista.ExibirTras();
        lista.Remover(20);
        lista.ExibirFrente();
        lista.ExibirTras();
    }
}
CS,
];

$exemplos['fila-fifo'] = [
    'titulo' => 'Fila Encadeada FIFO',
    'descricao' => 'O exemplo mostra uma fila de nós: insere no fim e retira do início, seguindo FIFO (primeiro a entrar, primeiro a sair).',
    'operacoes' => ['EstaVazia', 'Enfileirar', 'Desenfileirar', 'Frente'],
    'observar' => ['inicio e fim dão acesso direto às extremidades: nenhuma operação precisa percorrer a fila.', 'Ao remover o último nó, inicio torna-se null e fim também precisa receber null.', 'Frente consulta sem remover; Frente e Desenfileirar lançam exceção na fila vazia.'],
    'complexidade' => 'Enfileirar, Desenfileirar, Frente e EstaVazia: O(1). Espaço: O(n).',
    'teoria' => 'fila_fifo.php',
    'quiz' => 'fila-fifo',
    'saida' => '10
10
20',
    'execucao' => '10 foi o primeiro inserido: Frente o consulta e Desenfileirar o remove. O novo início contém 20.',
    'codigo' => <<<'CS'
using System;

public class No
{
    public int Valor { get; set; }
    public No Proximo { get; set; }

    public No(int valor)
    {
        Valor = valor;
        Proximo = null;
    }
}

public class FilaEncadeada
{
    private No inicio;
    private No fim;

    public FilaEncadeada()
    {
        inicio = null;
        fim = null;
    }

    public bool EstaVazia()
    {
        return inicio == null;
    }

    public void Enfileirar(int valor)
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
    }

    public int Desenfileirar()
    {
        if (inicio == null)
            throw new InvalidOperationException("A fila está vazia.");

        int valor = inicio.Valor;
        inicio = inicio.Proximo;
        if (inicio == null)
            fim = null;

        return valor;
    }

    public int Frente()
    {
        if (inicio == null)
            throw new InvalidOperationException("A fila está vazia.");

        return inicio.Valor;
    }
}
CS,
    'uso' => <<<'CS'
public static class Programa
{
    public static void Main()
    {
        FilaEncadeada fila = new FilaEncadeada();
        fila.Enfileirar(10);
        fila.Enfileirar(20);
        fila.Enfileirar(30);
        Console.WriteLine(fila.Frente());
        Console.WriteLine(fila.Desenfileirar());
        Console.WriteLine(fila.Frente());
    }
}
CS,
];

$exemplos['fila-prioridade'] = [
    'titulo' => 'Fila de Prioridades Encadeada FIFO',
    'descricao' => 'O exemplo mantém os nós ordenados: menor número = maior prioridade. Entre elementos com a mesma prioridade, vale a ordem de chegada (FIFO).',
    'operacoes' => ['EstaVazia', 'Enfileirar(valor, prioridade)', 'Desenfileirar', 'Frente'],
    'observar' => ['A comparação < insere na frente somente quando a nova prioridade é maior (número menor).', 'A condição <= percorre também os elementos antigos de mesma prioridade. Assim, quem chegou primeiro continua na frente.', 'A ordem lógica é 20 P1, 10 P2, 30 P2, 40 P3. 10 permanece antes de 30, pois ambos têm prioridade 2 e 10 entrou primeiro.', 'Frente consulta sem remover; a consulta e a remoção rejeitam fila vazia.'],
    'complexidade' => 'Enfileirar: O(n) no pior caso. Desenfileirar, Frente e EstaVazia: O(1). Espaço: O(n).',
    'teoria' => 'fila_prioridade.php',
    'quiz' => 'fila-prioridade',
    'saida' => '20
10
30
40',
    'execucao' => 'A saída imprime os valores na ordem de atendimento. P1 sai antes de P2; o empate P2 respeita FIFO; P3 sai por último.',
    'codigo' => <<<'CS'
using System;

public class No
{
    public int Valor { get; set; }
    public int Prioridade { get; set; }
    public No Proximo { get; set; }

    public No(int valor, int prioridade)
    {
        Valor = valor;
        Prioridade = prioridade;
        Proximo = null;
    }
}

public class FilaPrioridadeEncadeada
{
    private No inicio;

    public FilaPrioridadeEncadeada()
    {
        inicio = null;
    }

    public bool EstaVazia()
    {
        return inicio == null;
    }

    public void Enfileirar(int valor, int prioridade)
    {
        No novo = new No(valor, prioridade);
        if (inicio == null || prioridade < inicio.Prioridade)
        {
            novo.Proximo = inicio;
            inicio = novo;
            return;
        }

        No atual = inicio;
        while (atual.Proximo != null &&
               atual.Proximo.Prioridade <= prioridade)
        {
            atual = atual.Proximo;
        }

        novo.Proximo = atual.Proximo;
        atual.Proximo = novo;
    }

    public int Desenfileirar()
    {
        if (inicio == null)
            throw new InvalidOperationException("A fila de prioridades está vazia.");

        int valor = inicio.Valor;
        inicio = inicio.Proximo;
        return valor;
    }

    public int Frente()
    {
        if (inicio == null)
            throw new InvalidOperationException("A fila de prioridades está vazia.");

        return inicio.Valor;
    }
}
CS,
    'uso' => <<<'CS'
public static class Programa
{
    public static void Main()
    {
        FilaPrioridadeEncadeada fila = new FilaPrioridadeEncadeada();
        fila.Enfileirar(10, 2);
        fila.Enfileirar(20, 1);
        fila.Enfileirar(30, 2);
        fila.Enfileirar(40, 3);
        while (!fila.EstaVazia())
        {
            Console.WriteLine(fila.Desenfileirar());
        }
    }
}
CS,
];

$exemplos['pilha-encadeada'] = [
    'titulo' => 'Pilha Encadeada',
    'descricao' => 'O exemplo usa nós e uma referência topo, seguindo LIFO: último a entrar, primeiro a sair. Não utiliza vetor como armazenamento.',
    'operacoes' => ['EstaVazia', 'Empilhar', 'Desempilhar', 'Topo'],
    'observar' => ['novo.Proximo recebe o topo antigo antes de topo receber novo.', 'A remoção guarda topo.Valor antes de atualizar topo para topo.Proximo.', 'Topo consulta sem remover; Topo e Desempilhar lançam exceção quando a pilha está vazia.', 'Push e Pop ocorrem diretamente no topo, sem percurso.'],
    'complexidade' => 'Empilhar, Desempilhar, Topo e EstaVazia: O(1). Espaço: O(n).',
    'teoria' => 'pilha_encadeada.php',
    'quiz' => 'pilha-encadeada',
    'saida' => '30
30
20',
    'execucao' => '30 foi o último inserido: Topo o consulta, Pop o remove e o novo topo passa a ser 20.',
    'codigo' => <<<'CS'
using System;

public class No
{
    public int Valor { get; set; }
    public No Proximo { get; set; }

    public No(int valor)
    {
        Valor = valor;
        Proximo = null;
    }
}

public class PilhaEncadeada
{
    private No topo;

    public PilhaEncadeada()
    {
        topo = null;
    }

    public bool EstaVazia()
    {
        return topo == null;
    }

    public void Empilhar(int valor)
    {
        No novo = new No(valor);

        novo.Proximo = topo;
        topo = novo;
    }

    public int Desempilhar()
    {
        if (topo == null)
        {
            throw new InvalidOperationException(
                "A pilha está vazia."
            );
        }

        int valor = topo.Valor;
        topo = topo.Proximo;
        return valor;
    }

    public int Topo()
    {
        if (topo == null)
        {
            throw new InvalidOperationException(
                "A pilha está vazia."
            );
        }

        return topo.Valor;
    }
}
CS,
    'uso' => <<<'CS'
public static class Programa
{
    public static void Main()
    {
        PilhaEncadeada pilha = new PilhaEncadeada();
        pilha.Empilhar(10);
        pilha.Empilhar(20);
        pilha.Empilhar(30);
        Console.WriteLine(pilha.Topo());
        Console.WriteLine(pilha.Desempilhar());
        Console.WriteLine(pilha.Topo());
    }
}
CS,
];
