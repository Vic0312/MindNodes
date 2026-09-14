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
    public int Prioridade { get; set; }
    public No Proximo { get; set; }

    public No(int valor, int prioridade)
    {
        Valor = valor;
        Prioridade = prioridade;
        Proximo = null;
    }
}
CS;
$codigoEnfileirar = <<<'CS'
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
CS;
$codigoDesenfileirar = <<<'CS'
public int Desenfileirar()
{
    if (inicio == null)
        throw new InvalidOperationException("A fila de prioridades está vazia.");

    int valor = inicio.Valor;
    inicio = inicio.Proximo;
    return valor;
}
CS;
$codigoFrente = <<<'CS'
public int Frente()
{
    if (inicio == null)
        throw new InvalidOperationException("A fila de prioridades está vazia.");

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
CS;
$codigoUso = <<<'CS'
FilaPrioridadeEncadeada fila = new FilaPrioridadeEncadeada();
fila.Enfileirar(10, 2);
fila.Enfileirar(20, 1);
fila.Enfileirar(30, 2);
fila.Enfileirar(40, 3);

Console.WriteLine(fila.Frente()); // 20
fila.Desenfileirar();
Console.WriteLine(fila.Frente()); // 10
CS;
function codigoPrioridade($codigo) { echo htmlspecialchars($codigo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Fila de Prioridades Encadeada FIFO</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estruturas.css">
    <link rel="stylesheet" href="../css/fila_fifo.css">
    <link rel="stylesheet" href="../css/fila_prioridade.css">
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
    <main class="aula-fila aula-prioridade">
        <section class="banner-interno fila-hero">
            <span class="etiqueta">Estruturas de Dados · Aula</span>
            <h1>Fila de Prioridades <span>Encadeada FIFO</span></h1>
            <p>Prioridade primeiro, ordem de chegada em caso de empate. Acompanhe a inserção ordenada dos nós e implemente uma fila estável em C#.</p>
            <a class="fila-voltar" href="../view/estruturas.php">← Todas as estruturas</a>
        </section>
        <nav class="fila-indice" aria-label="Nesta aula">
            <a href="#conceito">Conceito</a><a href="#representacao">Nós</a><a href="#insercao">Inserção</a>
            <a href="#operacoes">Remoção e consulta</a><a href="#implementacao">C# completo</a>
            <a href="#complexidade">Complexidade</a><a href="#revisao">Revisão</a>
        </nav>

        <section class="fila-secao" id="conceito">
            <span class="fila-kicker">01 · Conceito</span><h2>Prioridade decide; empate respeita a chegada</h2>
            <p>Numa fila FIFO comum, todos são atendidos somente pela ordem de chegada. Nesta fila, cada elemento tem uma <strong>Prioridade</strong>. Adotamos uma convenção única: <strong>menor número = maior prioridade</strong>. Assim, prioridade 1 vem antes de 2, e 2 vem antes de 3. Entre elementos com a <strong>mesma</strong> prioridade, a ordem de chegada continua valendo: isso é FIFO no empate.</p>
            <div class="fila-alerta prioridade-regra"><h3>Regra importante</h3><p><strong>Prioridades diferentes:</strong> menor número vem primeiro. <strong>Prioridades iguais:</strong> quem chegou primeiro permanece primeiro. Uma fila que preserva essa ordem nos empates é chamada <strong>estável</strong>.</p></div>
            <p>Imagine Ana, prioridade 2, chegando primeiro; Bruno, prioridade 1, chegando depois; e Carla, prioridade 2, chegando por último. O atendimento será <strong>Bruno → Ana → Carla</strong>. Bruno ultrapassa Ana por ter prioridade maior. Ana permanece antes de Carla porque ambas são P2 e Ana chegou primeiro.</p>
            <div class="fila-exemplo" aria-label="Ordem de atendimento: Bruno prioridade 1, Ana prioridade 2, Carla prioridade 2"><strong>Bruno · P1</strong><i aria-hidden="true">→</i><strong>Ana · P2</strong><i aria-hidden="true">→</i><strong>Carla · P2</strong></div>
            <p><strong>Valor e prioridade são dados diferentes.</strong> Um nó com Valor 500 e Prioridade 1 fica antes de outro com Valor 10 e Prioridade 3. Não é o tamanho de <code>Valor</code> que define o atendimento; é o número de <code>Prioridade</code>.</p>
        </section>

        <section class="fila-secao" id="representacao">
            <span class="fila-kicker">02 · Representação</span><h2>Uma cadeia já organizada para o próximo atendimento</h2>
            <p>Cada <strong>No</strong> armazena <code>Valor</code>, <code>Prioridade</code> e a referência <code>Proximo</code>. A referência <code>inicio</code> aponta para o primeiro nó, que será o próximo atendido. Os nós ficam em ordem crescente de prioridade numérica; dentro de cada grupo de mesma prioridade, conservam a ordem de chegada. O último <code>Proximo</code> é <code>null</code>.</p>
            <div class="fila-diagrama" role="img" aria-label="inicio aponta para valor 20 prioridade 1, depois valor 10 prioridade 2, depois valor 30 prioridade 2, depois valor 40 prioridade 3 e nulo">
                <div class="fila-no"><span>INÍCIO · próximo</span><strong>20</strong><small>P1 · Proximo →</small></div><b aria-hidden="true">→</b>
                <div class="fila-no"><span>chegou antes</span><strong>10</strong><small>P2 · Proximo →</small></div><b aria-hidden="true">→</b>
                <div class="fila-no"><span>mesma prioridade</span><strong>30</strong><small>P2 · Proximo →</small></div><b aria-hidden="true">→</b>
                <div class="fila-no"><span>último nó</span><strong>40</strong><small>P3 · Proximo = null</small></div>
            </div>
            <div class="fila-grade tres"><article><h3>Valor</h3><p>O dado armazenado, por exemplo um identificador. Seu tamanho numérico não muda a posição.</p></article><article><h3>Prioridade</h3><p>Define a posição lógica: P1 antes de P2, P2 antes de P3.</p></article><article><h3>Proximo e inicio</h3><p><code>Proximo</code> liga nós; <code>inicio</code> identifica o primeiro a sair.</p></article></div>
            <h3>Classe No em C#</h3><div class="fila-codigo"><span>C# · nó da fila</span><pre><code><?php codigoPrioridade($codigoNo); ?></code></pre></div>
            <p>O construtor recebe valor e prioridade; <code>Proximo</code> começa em <code>null</code>. A classe <code>FilaPrioridadeEncadeada</code> precisa apenas de <code>inicio</code>: a inserção procura a posição correta e a remoção sempre ocorre na frente.</p>
        </section>

        <section class="fila-secao" id="insercao">
            <span class="fila-kicker">03 · Operação principal</span><h2>Enfileirar: encontrar a posição sem desfazer FIFO</h2>
            <p><code>Enfileirar(valor, prioridade)</code> cria um nó e o insere na lista ordenada. Se a fila estiver vazia, <code>inicio = null</code>, o novo nó se torna o início. Se o novo nó tiver prioridade numericamente menor que a do atual início, entra antes dele. Nos demais casos, o método percorre os nós até encontrar o último que deve ficar à frente do novo.</p>
            <div class="fila-codigo"><span>C# · Enfileirar</span><pre><code><?php codigoPrioridade($codigoEnfileirar); ?></code></pre></div>
            <div class="fila-alerta prioridade-regra"><h3>Por que a condição usa &lt;= ?</h3><p>O laço avança enquanto <code>atual.Proximo.Prioridade &lt;= prioridade</code>. Para inserir um novo P2, ele passa pelos P1 e também por <em>todos os P2 que chegaram antes</em>. Então o novo P2 entra após os antigos P2 e antes do primeiro P3. Se usássemos apenas <code>&lt;</code> nesse percurso, o recém-chegado poderia ultrapassar P2 antigos, violando FIFO.</p></div>
            <div class="fila-grade duas"><article><h3>Prioridade diferente: entre nós</h3><p>Antes: <strong>[10 | P1] → [20 | P3]</strong>. Ao inserir <strong>30 | P2</strong>, o percurso para antes de P3. Depois: <strong>[10 | P1] → [30 | P2] → [20 | P3]</strong>. P1 continua primeiro.</p></article><article><h3>Inserção no início</h3><p>Antes: <strong>[10 | P2] → [20 | P3]</strong>. Inserir <strong>30 | P1</strong> atualiza <code>inicio</code>. Depois: <strong>[30 | P1] → [10 | P2] → [20 | P3]</strong>. No caso vazio, inserir <strong>10 | P2</strong> produz <code>inicio → [10 | P2] → null</code>.</p></article></div>
            <div class="fila-grade duas"><article><h3>Inserção no final</h3><p>Antes: <strong>[10 | P1] → [20 | P2]</strong>. Inserir <strong>30 | P3</strong> percorre até a última posição e produz <strong>[10 | P1] → [20 | P2] → [30 | P3]</strong>.</p></article><article><h3>Um erro de estabilidade</h3><p>Inserir o novo P2 <em>antes</em> dos P2 antigos inverteria sua ordem de chegada. Entre iguais, o comportamento se pareceria com LIFO. O operador <code>&lt;=</code> impede essa ultrapassagem.</p></article></div>
            <h3>Empate de prioridade, passo a passo</h3><p>Estado: <strong>[10 | P1] → [20 | P2] → [30 | P2] → [40 | P3]</strong>. Inserimos <strong>50 | P2</strong>. O laço passa por 20 e 30, pois ambos têm prioridade 2. Ele para antes de 40, prioridade 3, e liga o novo nó nesse ponto.</p>
            <div class="fila-diagrama" role="img" aria-label="resultado do empate: 10 prioridade 1, 20 prioridade 2, 30 prioridade 2, 50 prioridade 2, 40 prioridade 3">
                <div class="fila-no"><span>INÍCIO</span><strong>10</strong><small>P1</small></div><b aria-hidden="true">→</b><div class="fila-no"><span>1º P2</span><strong>20</strong><small>P2</small></div><b aria-hidden="true">→</b><div class="fila-no"><span>2º P2</span><strong>30</strong><small>P2</small></div><b aria-hidden="true">→</b><div class="fila-no prioridade-novo"><span>NOVO · 3º P2</span><strong>50</strong><small>P2</small></div><b aria-hidden="true">→</b><div class="fila-no"><span>último</span><strong>40</strong><small>P3</small></div>
            </div>
            <p>O nó 50 <strong>não</strong> pode aparecer antes de 20 nem de 30: todos são P2, mas chegaram nessa ordem. A estabilidade preserva exatamente <strong>20 → 30 → 50</strong> dentro do grupo.</p>
        </section>

        <section class="fila-secao" id="operacoes">
            <span class="fila-kicker">04 · Atender e consultar</span><h2>O próximo elemento já está no início</h2>
            <p>A lista permanece ordenada após cada inserção. Por isso, <code>inicio</code> sempre identifica o próximo atendimento; não há busca na remoção. <code>Desenfileirar</code> salva o valor, avança <code>inicio = inicio.Proximo</code> e devolve o valor. Na fila vazia, lança uma exceção antes de acessar o nó.</p>
            <div class="fila-codigo"><span>C# · Desenfileirar</span><pre><code><?php codigoPrioridade($codigoDesenfileirar); ?></code></pre></div>
            <div class="fila-grade duas"><article><h3>Frente / Peek</h3><p>Consulta <code>inicio.Valor</code> <strong>sem remover</strong> o nó. Uma nova chamada retorna o mesmo valor até ocorrer uma remoção. Também verifica fila vazia.</p><div class="fila-codigo"><span>C# · Frente</span><pre><code><?php codigoPrioridade($codigoFrente); ?></code></pre></div></article><article><h3>EstaVazia</h3><p>Retorna se <code>inicio == null</code>. Não há nó a atender quando a referência inicial é nula.</p><div class="fila-codigo"><span>C# · EstaVazia</span><pre><code><?php codigoPrioridade($codigoEstaVazia); ?></code></pre></div></article></div>
        </section>

        <section class="fila-secao" id="sequencia"><span class="fila-kicker">05 · Acompanhe a ordem</span><h2>Prioridade e FIFO ao mesmo tempo</h2>
            <ol class="fila-linha-tempo"><li><strong>Enfileirar(A, P2)</strong><span>[A | P2].</span></li><li><strong>Enfileirar(B, P1)</strong><span>[B | P1] → [A | P2]. B passa à frente por ter prioridade maior.</span></li><li><strong>Enfileirar(C, P2)</strong><span>[B | P1] → [A | P2] → [C | P2]. A continua antes de C.</span></li><li><strong>Enfileirar(D, P1)</strong><span>[B | P1] → [D | P1] → [A | P2] → [C | P2]. B chegou antes de D, então mantém a frente do grupo P1.</span></li></ol>
        </section>

        <section class="fila-secao" id="implementacao"><span class="fila-kicker">06 · Código C#</span><h2>Implementação consolidada</h2>
            <p>O exemplo reúne <code>No</code>, construtor, <code>EstaVazia</code>, <code>Enfileirar</code>, <code>Desenfileirar</code> e <code>Frente</code>. A fila guarda só <code>inicio</code>; o percurso de inserção cuida da posição e dos empates. O site apenas exibe o código como texto.</p>
            <div class="fila-codigo"><span>C# · No + FilaPrioridadeEncadeada</span><pre><code><?php codigoPrioridade($codigoCompleto); ?></code></pre></div>
            <h3>Exemplo de execução</h3><div class="fila-codigo"><span>C# · uso da fila</span><pre><code><?php codigoPrioridade($codigoUso); ?></code></pre></div>
            <p>Após as inserções, a sequência é <strong>20 | P1 → 10 | P2 → 30 | P2 → 40 | P3</strong>. A primeira chamada de <code>Frente()</code> imprime <strong>20</strong>. Após <code>Desenfileirar()</code>, imprime <strong>10</strong>. O nó 10 continua antes de 30 porque ambos são P2 e 10 chegou primeiro.</p>
        </section>

        <section class="fila-secao" id="complexidade"><span class="fila-kicker">07 · Análise</span><h2>O custo da ordem está na inserção</h2>
            <div class="fila-tabela"><table><caption>Complexidade da fila de prioridades encadeada ordenada</caption><thead><tr><th scope="col">Operação</th><th scope="col">Pior caso</th><th scope="col">Motivo</th></tr></thead><tbody><tr><th scope="row">Enfileirar</th><td>O(n)</td><td>Pode percorrer os n nós até a posição correta.</td></tr><tr><th scope="row">Desenfileirar</th><td>O(1)</td><td>O próximo já está em <code>inicio</code>.</td></tr><tr><th scope="row">Frente</th><td>O(1)</td><td>Lê <code>inicio.Valor</code>.</td></tr><tr><th scope="row">EstaVazia</th><td>O(1)</td><td>Testa <code>inicio == null</code>.</td></tr></tbody></table></div>
            <p><strong>Enfileirar é O(n) no pior caso</strong> porque o novo nó pode precisar atravessar toda a cadeia, inclusive grupos de prioridade igual, até encontrar sua posição. <strong>Desenfileirar é O(1)</strong> porque a ordenação já aconteceu: basta mudar uma referência. O espaço total é <strong>O(n)</strong> para n nós. Outras implementações, como heaps, têm outros custos; aqui estudamos especificamente a lista encadeada ordenada.</p>
        </section>

        <section class="fila-secao" id="comparacao"><span class="fila-kicker">08 · Comparações</span><h2>O que muda em relação à fila FIFO comum?</h2>
            <div class="fila-tabela"><table><caption>Fila comum e fila de prioridades</caption><thead><tr><th scope="col">Aspecto</th><th scope="col">Fila FIFO comum</th><th scope="col">Fila de prioridades encadeada FIFO</th></tr></thead><tbody><tr><th scope="row">Ordem</th><td>Somente chegada: A → B → C.</td><td>Prioridade decide entre grupos; chegada decide dentro do mesmo grupo.</td></tr><tr><th scope="row">Inserção</th><td>No fim, com referência direta: O(1).</td><td>Na posição ordenada; pode percorrer a lista: O(n).</td></tr><tr><th scope="row">Remoção</th><td>Do início: O(1).</td><td>Do início, já ordenado: O(1).</td></tr></tbody></table></div>
            <div class="fila-grade duas"><article><h3>Vantagens</h3><ul><li>Prioridades explícitas, sem reordenar na remoção.</li><li>Próximo atendimento em O(1).</li><li>Tamanho dinâmico, sem capacidade fixa de vetor.</li><li>Os nós tornam visível a ordem de prioridade.</li></ul></article><article><h3>Desvantagens</h3><ul><li>Inserção pode percorrer O(n) nós.</li><li>Cada nó carrega uma referência adicional.</li><li>Empates exigem comparação estável.</li><li>Mais regras de ligação que a fila FIFO comum; um sinal errado quebra a ordem.</li></ul></article></div>
            <div class="fila-alerta"><h3>E a pilha?</h3><p>A fila de prioridades remove do início da sequência ordenada. Uma pilha remove o último elemento inserido. São regras de acesso diferentes, mesmo que ambas possam usar nós.</p></div>
        </section>

        <section class="fila-secao" id="erros"><span class="fila-kicker">09 · Cuidados</span><h2>Erros comuns nesta implementação</h2>
            <ol class="fila-erros"><li><strong>Inverter a convenção:</strong> aqui P1 vem antes de P2; comparar como se o maior número fosse mais urgente muda todos os resultados.</li><li><strong>Usar <code>&lt;</code> no percurso no lugar de <code>&lt;=</code>:</strong> o novo nó pode ultrapassar antigos de prioridade igual.</li><li><strong>Inserir antes dos P2 antigos:</strong> quebra FIFO no empate, mesmo se a ordem numérica permanecer correta.</li><li><strong>Não atualizar <code>inicio</code>:</strong> um novo P1 pode ficar invisível na frente quando o antigo início era P2.</li><li><strong>Procurar o menor número ao remover:</strong> a lista já está ordenada, logo o percurso é desnecessário.</li><li><strong>Confundir <code>Valor</code> com <code>Prioridade</code>:</strong> Valor 500 P1 precede Valor 10 P3 por causa de P1.</li><li><strong>Ignorar fila vazia:</strong> acessar <code>inicio.Valor</code> com início nulo causa erro.</li><li><strong>Tratar como fila FIFO comum:</strong> ordem de chegada só decide quando a prioridade empata.</li></ol>
        </section>

        <section class="fila-secao" id="revisao"><span class="fila-kicker">10 · Fixação</span><h2>Verifique o raciocínio</h2>
            <div class="fila-perguntas"><details><summary>Entre P1 e P3, quem fica na frente?</summary><p>P1: menor número representa maior prioridade.</p></details><details><summary>Dois nós P2 chegam em momentos diferentes. Qual fica antes?</summary><p>O que chegou primeiro. O laço passa pelos P2 antigos antes de ligar o novo nó, mantendo FIFO.</p></details><details><summary>Por que Enfileirar pode custar O(n)?</summary><p>Em uma lista ordenada, talvez seja necessário percorrer todos os nós para achar a posição do novo.</p></details><details><summary>Por que Desenfileirar custa O(1)?</summary><p>O próximo atendimento já está em <code>inicio</code>; basta avançar a referência.</p></details></div>
            <a class="fila-cta" href="../view/quiz.php?assunto=fila-prioridade">Praticar no Quiz de Fila de Prioridades →</a>
        </section>
    </main>
</body>
</html>
