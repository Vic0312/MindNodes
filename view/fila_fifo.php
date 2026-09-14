<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../view/login.php');
    exit();
}
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$fotoBanco = $_SESSION['usuario_foto'] ?? null;
$fotoExibicao = '../img/default-img.avif';
if (!empty($fotoBanco)) {
    $fotoBanco = trim($fotoBanco);
    if (str_starts_with($fotoBanco, 'data:image')) {
        $fotoExibicao = $fotoBanco;
    } else {
        foreach (['../' . $fotoBanco, '../uploads/usuarios/' . basename($fotoBanco), '../img/' . basename($fotoBanco)] as $caminho) {
            if (file_exists(__DIR__ . '/' . $caminho)) { $fotoExibicao = $caminho; break; }
        }
    }
}
$codigoNo = <<<'CS'
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
CS;
$codigoEnfileirar = <<<'CS'
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
CS;
$codigoDesenfileirar = <<<'CS'
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
CS;
$codigoFrente = <<<'CS'
public int Frente()
{
    if (inicio == null)
        throw new InvalidOperationException("A fila está vazia.");

    return inicio.Valor;
}
CS;
$codigoEstaVazia = <<<'CS'
public bool EstaVazia()
{
    return inicio == null;
}
CS;
$codigoCompleto = <<<'CS'
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
CS;
$codigoUso = <<<'CS'
FilaEncadeada fila = new FilaEncadeada();
fila.Enfileirar(10);
fila.Enfileirar(20);
fila.Enfileirar(30);
Console.WriteLine(fila.Frente()); // 10
fila.Desenfileirar();
Console.WriteLine(fila.Frente()); // 20
CS;
function codigoFila($codigo) { echo htmlspecialchars($codigo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Fila Encadeada FIFO</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estruturas.css">
    <link rel="stylesheet" href="../css/fila_fifo.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="../view/home.php"><span class="simbolo-logo">∞</span><strong>Mind<span>Nodes</span></strong></a>
        <nav class="menu" aria-label="Navegação principal">
            <a href="../view/home.php">Início</a><a href="../view/sobre.php">Sobre</a>
            <a class="ativo" href="../view/estruturas.php">Estruturas</a><a href="../view/exemplos.php">Exemplos</a>
            <a href="../view/simulador.php">Simulador</a><a href="../view/quiz.php">Quiz</a>
            <a href="../view/desempenho.php">Desempenho</a>
        </nav>
        <a class="perfil-usuario" href="../view/perfil.php" title="Perfil de <?php echo htmlspecialchars($nomeUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <img src="<?php echo htmlspecialchars($fotoExibicao, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($nomeUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" class="foto-perfil-nav">
        </a>
    </header>
    <main class="aula-fila">
        <section class="banner-interno fila-hero">
            <span class="etiqueta">Estruturas de Dados · Aula</span>
            <h1>Fila <span>Encadeada FIFO</span></h1>
            <p>Primeiro a entrar, primeiro a sair. Entenda o encadeamento dos nós, acompanhe cada mudança de referência e implemente a estrutura em C#.</p>
            <a class="fila-voltar" href="../view/estruturas.php">← Todas as estruturas</a>
        </section>

        <nav class="fila-indice" aria-label="Nesta aula">
            <a href="#conceito">Conceito</a><a href="#representacao">Representação</a><a href="#operacoes">Operações</a>
            <a href="#implementacao">Código completo</a><a href="#complexidade">Complexidade</a><a href="#revisao">Revisão</a>
        </nav>

        <section id="conceito" class="fila-secao">
            <span class="fila-kicker">01 · Conceito</span><h2>O atendimento segue a ordem de chegada</h2>
            <p>Uma fila é uma estrutura linear: cada elemento aguarda sua vez e o primeiro que entrou é o primeiro a sair. Essa regra é chamada <strong>FIFO</strong>, de <em>First In, First Out</em> — primeiro a entrar, primeiro a sair. Pense em três pessoas que chegam ao atendimento na ordem A, B e C. A ordem de saída será A, depois B, depois C.</p>
            <div class="fila-exemplo" aria-label="Pessoas A, B e C em ordem de atendimento"><span>Atendimento</span><strong>A</strong><i aria-hidden="true">→</i><strong>B</strong><i aria-hidden="true">→</i><strong>C</strong><small>Chegada: A → B → C · Saída: A → B → C</small></div>
            <p>Em programação, a regra continua a mesma. <strong>Enfileirar</strong> acrescenta ao fim; <strong>Desenfileirar</strong> retira do início. A estrutura não permite escolher arbitrariamente um nó para atendimento. Embora use nós parecidos com os de uma lista simplesmente encadeada, sua interface restringe onde as operações ocorrem.</p>
        </section>

        <section id="representacao" class="fila-secao">
            <span class="fila-kicker">02 · Representação</span><h2>Nós conectados por referências</h2>
            <p>“Encadeada” significa que os valores não precisam ocupar posições contíguas de um vetor. Cada <strong>No</strong> guarda um <strong>Valor</strong> e uma referência <strong>Proximo</strong> para o nó seguinte. A fila mantém duas referências externas: <code>inicio</code> aponta para a frente, próximo nó a sair; <code>fim</code> aponta para o último, onde o próximo nó será anexado. O <code>Proximo</code> do último nó é <code>null</code>.</p>
            <div class="fila-diagrama" role="img" aria-label="inicio aponta para o nó 10, que aponta para 20, que aponta para 30; fim aponta para 30 e o próximo de 30 é nulo">
                <div class="fila-no"><span>INÍCIO · frente</span><strong>10</strong><small>Proximo →</small></div><b aria-hidden="true">→</b>
                <div class="fila-no"><span>nó intermediário</span><strong>20</strong><small>Proximo →</small></div><b aria-hidden="true">→</b>
                <div class="fila-no"><span>FIM · último</span><strong>30</strong><small>Proximo = null</small></div>
            </div>
            <div class="fila-grade tres">
                <article><h3>No</h3><p>Unidade que contém o dado e a ligação para o próximo nó. Cada inserção cria um novo nó.</p></article>
                <article><h3>inicio / frente</h3><p>Referência para o primeiro nó. É o valor observado por <code>Frente</code> e retirado por <code>Desenfileirar</code>.</p></article>
                <article><h3>fim</h3><p>Referência para o último nó. Permite anexar diretamente, sem percorrer a cadeia.</p></article>
            </div>
            <h3>O nó em C#</h3>
            <div class="fila-codigo"><span>C# · classe No</span><pre><code><?php codigoFila($codigoNo); ?></code></pre></div>
            <p><code>Valor</code> guarda o inteiro. <code>Proximo</code> é uma referência para outro <code>No</code>; no construtor começa como <code>null</code>, pois o nó ainda não tem sucessor. A classe <code>FilaEncadeada</code> manterá <code>inicio</code> e <code>fim</code>, inicialmente nulos.</p>
        </section>

        <section id="operacoes" class="fila-secao">
            <span class="fila-kicker">03 · Operações</span><h2>Como as referências mudam</h2>
            <article class="fila-operacao" id="enfileirar"><h3>Enfileirar / Enqueue <span>inserção no fim</span></h3>
                <p>O método cria <code>novo</code>. Se a fila está vazia, o mesmo nó passa a ser início e fim. Caso contrário, <code>fim.Proximo = novo</code> liga o antigo último nó ao novo; somente depois <code>fim = novo</code> atualiza a referência do último nó. Nenhum nó precisa ser percorrido.</p>
                <div class="fila-codigo"><span>C# · Enfileirar</span><pre><code><?php codigoFila($codigoEnfileirar); ?></code></pre></div>
                <ol><li>Criar o nó com o valor recebido e <code>Proximo = null</code>.</li><li>Na fila vazia, atribuir <code>inicio</code> e <code>fim</code> ao mesmo nó.</li><li>Na fila não vazia, ligar o antigo fim ao novo nó e mover <code>fim</code> para ele.</li></ol>
                <div class="fila-casos"><div><h4>Quando está vazia</h4><p>Antes: <code>inicio = null</code> e <code>fim = null</code>. Depois de <code>Enfileirar(10)</code>, ambos apontam para <strong>[10 | null]</strong>: ele é ao mesmo tempo o primeiro e o último.</p></div><div><h4>Quando já há nós</h4><p>Antes: <strong>[10] → [20]</strong>, com <code>fim</code> em 20. Ao executar <code>Enfileirar(30)</code>, primeiro 20 aponta para 30; depois <code>fim</code> passa a apontar para 30. Resultado: <strong>[10] → [20] → [30]</strong>. O nó 10 continua na frente, preservando FIFO.</p></div></div>
            </article>
            <article class="fila-operacao" id="desenfileirar"><h3>Desenfileirar / Dequeue <span>remoção do início</span></h3>
                <p>Se a fila estiver vazia, a operação lança uma exceção. Caso contrário, salva <code>inicio.Valor</code>, move <code>inicio</code> para <code>inicio.Proximo</code> e devolve o valor salvo. Com <strong>[10] → [20] → [30]</strong>, sai 10 e o novo início é 20; o fim continua em 30. Isso também não exige percorrer a fila.</p>
                <div class="fila-codigo"><span>C# · Desenfileirar</span><pre><code><?php codigoFila($codigoDesenfileirar); ?></code></pre></div>
                <div class="fila-alerta"><h4>O caso do último elemento</h4><p>Se a fila contém apenas <strong>[10 | null]</strong>, <code>inicio</code> e <code>fim</code> apontam para 10. Ao removê-lo, <code>inicio = inicio.Proximo</code> torna <code>inicio = null</code>. É indispensável fazer também <code>fim = null</code>: deixar <code>fim</code> no nó antigo criaria uma referência desatualizada e quebraria a próxima inserção. Depois, ambos são nulos.</p></div>
            </article>
            <div class="fila-grade duas">
                <article class="fila-operacao" id="frente"><h3>Frente / Peek</h3><p>Consulta <code>inicio.Valor</code> <strong>sem remover</strong> o nó. Se não há início, lança exceção. Repetir <code>Frente()</code> não muda a fila.</p><div class="fila-codigo"><span>C# · Frente</span><pre><code><?php codigoFila($codigoFrente); ?></code></pre></div></article>
                <article class="fila-operacao" id="esta-vazia"><h3>EstaVazia</h3><p>Retorna se <code>inicio == null</code>. Basta verificar o início porque uma fila bem formada não pode ter fim sem ter início.</p><div class="fila-codigo"><span>C# · EstaVazia</span><pre><code><?php codigoFila($codigoEstaVazia); ?></code></pre></div></article>
            </div>
        </section>

        <section class="fila-secao" id="sequencia"><span class="fila-kicker">04 · Acompanhe a fila</span><h2>De vazia a três nós — e de volta à frente</h2>
            <ol class="fila-linha-tempo"><li><strong>Estado inicial</strong><span>Fila vazia: <code>inicio = null</code>, <code>fim = null</code>.</span></li><li><strong>Enfileirar(10)</strong><span>[10] · início e fim apontam para 10.</span></li><li><strong>Enfileirar(20)</strong><span>[10] → [20] · fim aponta para 20.</span></li><li><strong>Enfileirar(30)</strong><span>[10] → [20] → [30] · fim aponta para 30.</span></li><li><strong>Desenfileirar()</strong><span>Sai 10. Restam [20] → [30]; início aponta para 20.</span></li><li><strong>Frente()</strong><span>Retorna 20 e a fila continua [20] → [30].</span></li></ol>
        </section>

        <section class="fila-secao" id="implementacao"><span class="fila-kicker">05 · C# completo</span><h2>Uma implementação pequena e funcional</h2>
            <p>A classe <code>FilaEncadeada</code> usa apenas duas referências. O construtor inicia ambas como <code>null</code>; os quatro métodos mantêm a regra FIFO e tratam a fila vazia. O código abaixo é texto para estudo e não é executado pelo site.</p>
            <div class="fila-codigo"><span>C# · No + FilaEncadeada</span><pre><code><?php codigoFila($codigoCompleto); ?></code></pre></div>
            <h3>Usando a fila</h3><div class="fila-codigo"><span>C# · exemplo de execução</span><pre><code><?php codigoFila($codigoUso); ?></code></pre></div>
            <p>A primeira impressão é <strong>10</strong>, pois ele foi o primeiro a entrar. Após <code>Desenfileirar()</code>, a segunda impressão é <strong>20</strong>. O nó 30 continua no fim.</p>
        </section>

        <section class="fila-secao" id="complexidade"><span class="fila-kicker">06 · Custo das operações</span><h2>Por que todas levam tempo constante?</h2>
            <div class="fila-tabela"><table><caption>Complexidade da Fila Encadeada FIFO</caption><thead><tr><th scope="col">Operação</th><th scope="col">Tempo</th><th scope="col">Motivo</th></tr></thead><tbody><tr><th scope="row">Enfileirar</th><td>O(1)</td><td><code>fim</code> dá acesso direto ao último nó.</td></tr><tr><th scope="row">Desenfileirar</th><td>O(1)</td><td><code>inicio</code> dá acesso direto ao primeiro nó.</td></tr><tr><th scope="row">Frente</th><td>O(1)</td><td>Apenas lê o valor em <code>inicio</code>.</td></tr><tr><th scope="row">EstaVazia</th><td>O(1)</td><td>Apenas verifica se <code>inicio</code> é nulo.</td></tr></tbody></table></div>
            <p>O armazenamento ocupa <strong>O(n) de espaço</strong> para <code>n</code> nós. A referência <code>fim</code> é decisiva: sem ela, uma implementação que partisse de <code>inicio</code> para encontrar o último nó em toda inserção gastaria <strong>O(n)</strong> para enfileirar. Aqui, uma ligação e uma atualização de referência bastam.</p>
        </section>

        <section class="fila-secao" id="comparacao"><span class="fila-kicker">07 · Escolhas de implementação</span><h2>Quando a representação encadeada faz sentido?</h2>
            <div class="fila-grade duas"><article><h3>Vantagens</h3><ul><li>Tamanho dinâmico, limitado pela memória disponível.</li><li>Inserção e remoção O(1) nas extremidades corretas.</li><li>Nenhum deslocamento dos elementos restantes.</li><li>Boa opção quando a quantidade de elementos varia.</li></ul></article><article><h3>Desvantagens</h3><ul><li>Cada nó carrega uma referência adicional e custo de alocação.</li><li>Não há acesso direto por índice.</li><li>As ligações exigem cuidado; referências incorretas podem perder nós ou deixar <code>fim</code> desatualizado.</li></ul></article></div>
            <div class="fila-grade duas"><article><h3>Fila com vetor</h3><p>Usa armazenamento contíguo e normalmente índices para frente e fim. A capacidade pode ser fixa ou exigir redimensionamento; uma fila circular evita deslocar todos os elementos a cada remoção.</p></article><article><h3>Fila encadeada</h3><p>Aloca nós conforme a necessidade e liga cada um ao próximo. Usa referências <code>inicio</code> e <code>fim</code> em vez de índices. A escolha depende de memória, alocação e padrão de uso, não de uma estrutura universalmente melhor.</p></article></div>
            <div class="fila-alerta"><h3>Não confunda FIFO com LIFO</h3><p><strong>Fila = FIFO:</strong> primeiro a entrar, primeiro a sair. <strong>Pilha = LIFO:</strong> último a entrar, primeiro a sair. A representação por nós, sozinha, não determina a regra de acesso.</p></div>
        </section>

        <section class="fila-secao" id="erros"><span class="fila-kicker">08 · Atenção às referências</span><h2>Erros comuns</h2>
            <ol class="fila-erros"><li><strong>Inserir no início:</strong> faria o recém-chegado passar na frente e violaria FIFO.</li><li><strong>Remover do fim:</strong> retiraria o mais recente, comportamento semelhante a LIFO.</li><li><strong>Esquecer <code>fim = novo</code>:</strong> a próxima inserção seria ligada a um fim antigo.</li><li><strong>Manter <code>fim</code> após remover o último:</strong> deixaria a fila vazia com uma referência para nó removido.</li><li><strong>Ler <code>inicio.Valor</code> quando <code>inicio == null</code>:</strong> causaria erro por referência nula; teste antes.</li><li><strong>Percorrer até o fim em toda inserção:</strong> desperdiçaria a referência <code>fim</code> e elevaria o custo para O(n).</li></ol>
        </section>

        <section class="fila-secao" id="revisao"><span class="fila-kicker">09 · Fixação</span><h2>Confira se o raciocínio ficou claro</h2>
            <div class="fila-perguntas"><details><summary>Onde uma fila FIFO recebe novos elementos?</summary><p>No fim. <code>Enfileirar</code> liga o novo nó ao antigo <code>fim</code> e então atualiza <code>fim</code>.</p></details><details><summary>Na fila 10 → 20 → 30, qual valor sai primeiro?</summary><p>10, pois <code>inicio</code> aponta para o primeiro nó inserido.</p></details><details><summary>Por que <code>fim</code> permite inserção O(1)?</summary><p>Porque oferece acesso direto ao último nó; não é necessário percorrer os anteriores.</p></details><details><summary>O que acontece ao remover o único nó?</summary><p><code>inicio</code> e <code>fim</code> tornam-se <code>null</code>. A fila volta ao estado vazio.</p></details></div>
            <a class="fila-cta" href="../view/quiz.php?assunto=fila-fifo">Praticar no Quiz de Fila Encadeada FIFO →</a>
        </section>
    </main>
</body>
</html>
