<?php
// Autenticação e cabeçalho seguem as demais aulas de estruturas.
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../view/login.php');
    exit();
}
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
require_once __DIR__ . '/partials/multimidia.php';
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
$codigoBase = <<<'CS'
public class PilhaEncadeada
{
    private No topo;

    public PilhaEncadeada()
    {
        topo = null;
    }

    // Os métodos serão acrescentados a seguir.
}
CS;
$codigoEmpilhar = <<<'CS'
public void Empilhar(int valor)
{
    No novo = new No(valor);

    novo.Proximo = topo;
    topo = novo;
}
CS;
$codigoDesempilhar = <<<'CS'
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
CS;
$codigoTopo = <<<'CS'
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
CS;
$codigoEstaVazia = <<<'CS'
public bool EstaVazia()
{
    return topo == null;
}
CS;
// Consolida os mesmos trechos ensinados, evitando versões divergentes.
$metodos = implode("\n\n", [$codigoEstaVazia, $codigoEmpilhar, $codigoDesempilhar, $codigoTopo]);
$codigoCompleto = "using System;\n\n" . $codigoNo . "\n\n" . str_replace(
    '    // Os métodos serão acrescentados a seguir.',
    '    ' . str_replace("\n", "\n    ", $metodos),
    $codigoBase
);
$codigoUso = <<<'CS'
public static class Programa
{
    public static void Main()
    {
        PilhaEncadeada pilha = new PilhaEncadeada();

        pilha.Empilhar(10);
        pilha.Empilhar(20);
        pilha.Empilhar(30);

        Console.WriteLine(pilha.Topo());
        pilha.Desempilhar();
        Console.WriteLine(pilha.Topo());
    }
}
CS;
function codigoPilha($codigo) { echo htmlspecialchars($codigo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function diagramaPilha($valores, $descricao) {
    ?>
    <figure class="pilha-diagrama">
        <figcaption><?php codigoPilha($descricao); ?></figcaption>
        <strong>TOPO</strong><span aria-hidden="true">↓</span>
        <?php foreach ($valores as $valor): ?>
            <div class="fila-no"><span>VALOR</span><strong><?php codigoPilha((string) $valor); ?></strong><small>PRÓXIMO ↓</small></div>
            <span aria-hidden="true">↓</span>
        <?php endforeach; ?>
        <code>null</code>
    </figure>
    <?php
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Pilha Encadeada</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estruturas.css">
    <link rel="stylesheet" href="../css/fila_fifo.css">
    <link rel="stylesheet" href="../css/pilha_encadeada.css">
    <link rel="stylesheet" href="../css/multimidia.css">
    <script src="../js/multimidia.js" defer></script>
    <link rel="stylesheet" href="../css/navegacao.css">
    <script src="../js/navegacao.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>
    <main class="aula-fila aula-pilha">
        <section class="banner-interno fila-hero">
            <span class="etiqueta">Estruturas de Dados · Aula</span>
            <h1>Pilha <span>Encadeada</span></h1>
            <p>Último a entrar, primeiro a sair. Aprenda LIFO, acompanhe as referências e implemente a pilha com nós em C#.</p>
            <a class="fila-voltar" href="../view/estruturas.php">← Todas as estruturas</a>
        </section>
        <nav class="fila-indice" aria-label="Nesta aula">
            <a href="#conceito">Conceito e LIFO</a><a href="#representacao">Nós e topo</a><a href="#empilhar">Empilhar</a><a href="#desempilhar">Desempilhar</a><a href="#consultas">Consultas</a><a href="#implementacao">C# completo</a><a href="#exemplo">Exemplo</a><a href="#complexidade">Complexidade</a><a href="#comparacao">Comparações</a><a href="#erros">Erros</a><a href="#revisao">Revisão</a>
        </nav>
        <nav class="midia-navegacao" aria-label="Recurso visual"><a href="#midia-pilha-encadeada">Ver LIFO passo a passo e vídeo</a></nav>
        <section class="fila-secao" id="conceito">
            <span class="fila-kicker">01 · Conceito</span><h2>Uma estrutura linear com uma regra de acesso</h2>
            <p>Uma pilha organiza elementos em sequência e concentra inserções, remoções e consultas no <strong>topo</strong>. Ela segue <strong>LIFO</strong>, do inglês <em>Last In, First Out</em>: <strong>último a entrar, primeiro a sair</strong>.</p>
            <p>Pense em uma pilha de pratos: coloque o prato A, depois B e depois C. C fica sobre B, que fica sobre A. Ao retirar um prato por cima, C sai primeiro, depois B e finalmente A.</p>
            <div class="pilha-diagrama" role="img" aria-label="Pratos de cima para baixo: C, B e A. Retirada: C, depois B, depois A."><strong>TOPO · retirada</strong><div class="fila-no">Prato C</div><div class="fila-no">Prato B</div><div class="fila-no">Prato A</div></div>
            <aside class="fila-alerta"><h3>LIFO: último a entrar = primeiro a sair</h3><p>Empilhar <strong>10, 20, 30</strong>, nessa ordem, produz a ordem de remoção <strong>30, 20, 10</strong>. A regra vale para as operações da pilha independentemente da representação interna.</p></aside>
            <h3>Relação com TAD</h3><p>Uma pilha é um <a href="../view/exemplos.php?estrutura=tad">Tipo Abstrato de Dados (TAD)</a> definido pelas operações <code>Empilhar</code>, <code>Desempilhar</code>, <code>Topo</code> e <code>EstaVazia</code>. O contrato descreve o comportamento LIFO; a implementação pode usar vetor ou nós. <strong>Nesta aula, a implementação é encadeada.</strong></p>
        </section>
        <section class="fila-secao" id="representacao">
            <span class="fila-kicker">02 · Representação</span><h2>O que significa “encadeada”?</h2>
            <p>A pilha encadeada não precisa de vetor: é formada por <strong>nós</strong> ligados por referências. Cada nó guarda um valor e uma referência para o nó abaixo dele. Os nós não precisam ocupar posições consecutivas na memória. O último nó da cadeia aponta para <code>null</code>, indicando que não há outro abaixo.</p>
            <?php diagramaPilha([30, 20, 10], 'De cima para baixo: topo aponta para 30; 30 aponta para 20; 20 para 10; 10 para null.'); ?>
            <div class="fila-grade duas"><article><h3>Nó</h3><p>Armazena <strong>valor + referência para o próximo</strong>. Aqui, “próximo” significa o elemento abaixo na pilha.</p></article><article><h3>topo</h3><p>Referência para o primeiro nó da cadeia: o <strong>último elemento inserido</strong> e o <strong>próximo a ser removido</strong>. Não é um índice nem uma cópia do valor.</p></article></div>
            <h3>Semelhante à lista, com acesso restrito</h3><p>Os nós são semelhantes aos da <a href="../view/exemplos.php?estrutura=simples">Lista Simplesmente Encadeada</a>. Porém, uma lista genérica pode oferecer operações em várias posições; a pilha oferece acesso pelo topo, seguindo LIFO. Não se escolhe livremente um elemento interno para remover. Ao contrário do nó da <a href="../view/exemplos.php?estrutura=dupla">Lista Duplamente Encadeada</a>, este nó não precisa de referência para o anterior.</p>
            <h3>Classe No em C#</h3><div class="fila-codigo"><span>C# · nó</span><pre tabindex="0"><code><?php codigoPilha($codigoNo); ?></code></pre></div>
            <p><code>Valor</code> contém o inteiro armazenado. <code>Proximo</code> referencia outro <code>No</code>, abaixo na pilha. O construtor recebe o valor e inicia a ligação com <code>null</code>.</p>
            <h3>Classe PilhaEncadeada e estado inicial</h3><div class="fila-codigo"><span>C# · estrutura inicial</span><pre tabindex="0"><code><?php codigoPilha($codigoBase); ?></code></pre></div>
            <p>O campo privado <code>topo</code> concentra o acesso aos nós. O construtor faz <code>topo = null</code>: não há nó algum, portanto a pilha está vazia. Não é necessário manter uma referência de fim.</p>
        </section>
        <?php renderizarMultimidia('pilha-encadeada'); ?>
        <section class="fila-secao" id="empilhar">
            <span class="fila-kicker">03 · Inserção</span><h2>Empilhar / Push</h2><p>Adiciona um novo elemento no topo, preservando todos os nós anteriores abaixo dele.</p>
            <div class="fila-codigo"><span>C# · Empilhar</span><pre tabindex="0"><code><?php codigoPilha($codigoEmpilhar); ?></code></pre></div>
            <ol class="fila-erros"><li><code>No novo = new No(valor);</code> cria um nó com o valor recebido.</li><li><code>novo.Proximo = topo;</code> liga o novo nó ao <strong>topo antigo</strong>, antes de substituí-lo.</li><li><code>topo = novo;</code> faz o novo nó ser o topo. O último que entrou será o primeiro a sair, preservando LIFO.</li></ol>
            <div class="fila-grade duas"><article><h3>Empilhar em pilha vazia</h3><p>Antes: <code>topo = null</code>. Ao executar <code>Empilhar(10)</code>, <code>novo.Proximo = topo;</code> atribui <code>null</code> à ligação. Depois, o novo nó se torna imediatamente o topo.</p><?php diagramaPilha([10], 'Depois de Empilhar(10): topo aponta para 10, cujo próximo é null.'); ?></article><article><h3>Empilhar em pilha não vazia</h3><p>Antes, o topo aponta para 20, que aponta para 10. Em <code>Empilhar(30)</code>: (1) cria-se o nó 30; (2) seu <code>Proximo</code> recebe o nó 20; (3) <code>topo</code> recebe o nó 30. A cadeia antiga continua ligada.</p><?php diagramaPilha([30, 20, 10], 'Depois de Empilhar(30): 30 acima de 20 e 10.'); ?></article></div>
        </section>
        <section class="fila-secao" id="desempilhar">
            <span class="fila-kicker">04 · Remoção</span><h2>Desempilhar / Pop</h2><p>Remove e devolve o valor do topo. Não é necessário percorrer a estrutura: basta guardar o valor e avançar a referência para o nó abaixo.</p>
            <div class="fila-codigo"><span>C# · Desempilhar</span><pre tabindex="0"><code><?php codigoPilha($codigoDesempilhar); ?></code></pre></div>
            <ol class="fila-erros"><li>Verificar <code>topo == null</code>. Sem nós, lançar <code>InvalidOperationException</code>, pois não há valor para remover.</li><li>Guardar <code>topo.Valor</code> na variável <code>valor</code>.</li><li>Atualizar <code>topo = topo.Proximo;</code>. O nó abaixo passa a ser o topo.</li><li>Retornar o valor salvo, pertencente ao nó removido.</li></ol>
            <div class="fila-grade duas"><article><?php diagramaPilha([30, 20, 10], 'Antes de Desempilhar(): topo em 30.'); ?></article><article><?php diagramaPilha([20, 10], 'Depois: sai 30 e o novo topo é 20.'); ?></article></div>
            <p>O nó removido deixa de fazer parte da pilha. Em C#, quando não há referências que o mantenham alcançável, sua memória pode ser recuperada pelo coletor de lixo; não é preciso liberar o nó manualmente.</p>
            <aside class="fila-alerta"><h3>Removendo o último elemento</h3><p>Com apenas <strong>[10 | null]</strong>, o método salva 10 e atribui <code>topo.Proximo</code>, que é <code>null</code>, a <code>topo</code>. Retorna 10 e a pilha volta a ficar vazia. Não há referência de fim para atualizar. Uma nova inserção funciona com as mesmas duas atribuições de Push.</p></aside>
        </section>
        <section class="fila-secao" id="consultas">
            <span class="fila-kicker">05 · Consultas</span><h2>Consultar sem alterar a pilha</h2>
            <div class="fila-grade duas"><article id="topo"><h3>Topo / Peek</h3><p>Consulta o valor do topo <strong>sem remover</strong>. Repetir <code>Topo()</code> retorna o mesmo valor enquanto nenhuma operação modificar a pilha. Na pilha vazia, lança uma exceção antes de acessar <code>topo.Valor</code>.</p><div class="fila-codigo"><span>C# · Topo</span><pre tabindex="0"><code><?php codigoPilha($codigoTopo); ?></code></pre></div></article><article id="esta-vazia"><h3>EstaVazia</h3><p>Retorna <code>true</code> se não há nós e <code>false</code> se há pelo menos um. Basta testar <code>topo == null</code>: em uma pilha bem formada, todo nó pertencente à pilha é alcançável a partir do topo.</p><div class="fila-codigo"><span>C# · EstaVazia</span><pre tabindex="0"><code><?php codigoPilha($codigoEstaVazia); ?></code></pre></div></article></div>
            <p>Não use 0 ou -1 para indicar pilha vazia: ambos são valores inteiros válidos para empilhar. A exceção em Pop e Peek distingue a ausência de elemento de um valor armazenado. Valores repetidos também são permitidos: cada Push cria um nó independente.</p>
        </section>
        <section class="fila-secao" id="implementacao">
            <span class="fila-kicker">06 · Implementação completa</span><h2>No + PilhaEncadeada em C#</h2><p>Esta versão reúne os trechos anteriores: um campo <code>topo</code>, construtor e quatro operações. O código é exibido apenas como texto para estudo.</p>
            <p>Os exemplos usam referências anuláveis no estilo tradicional de C#, com <em>nullable reference types</em> desabilitado. Em projetos com essa análise habilitada, declare <code>No?</code> em <code>Proximo</code> e no campo <code>topo</code>, pois ambos podem receber <code>null</code>. Isso não muda o algoritmo.</p>
            <div class="fila-codigo"><span>C# · implementação consolidada</span><pre tabindex="0" id="codigo-completo"><code><?php codigoPilha($codigoCompleto); ?></code></pre></div>
        </section>
        <section class="fila-secao" id="exemplo">
            <span class="fila-kicker">07 · Execução</span><h2>Do estado vazio ao novo topo</h2>
            <ol class="fila-linha-tempo"><li><strong>Estado inicial</strong><span>Pilha vazia: topo = null.</span></li><li><strong>Empilhar(10)</strong><span>Topo: 10. Abaixo: nenhum nó.</span></li><li><strong>Empilhar(20)</strong><span>Topo: 20. Abaixo: 10.</span></li><li><strong>Empilhar(30)</strong><span>Topo: 30. Abaixo: 20, depois 10.</span></li><li><strong>Desempilhar()</strong><span>Sai 30. Novo topo: 20. Abaixo: 10.</span></li><li><strong>Topo()</strong><span>Resultado: 20. Os nós 20 e 10 permanecem na pilha.</span></li></ol>
            <h3>Exemplo de execução em C#</h3><p>Acrescente a classe <code>Programa</code> abaixo ao mesmo arquivo da implementação completa. O <code>using System;</code> já está no início daquele arquivo.</p>
            <div class="fila-codigo"><span>C# · Programa.Main</span><pre tabindex="0" id="codigo-uso"><code><?php codigoPilha($codigoUso); ?></code></pre></div>
            <div class="fila-codigo"><span>Saída no console</span><pre tabindex="0"><code>30
20</code></pre></div><p>A primeira consulta imprime 30, o último inserido. Pop remove 30; a segunda consulta imprime 20, sem removê-lo. Se continuarmos desempilhando, sairão 20 e depois 10.</p>
        </section>
        <section class="fila-secao" id="complexidade">
            <span class="fila-kicker">08 · Complexidade</span><h2>Acesso direto ao topo: O(1)</h2>
            <div class="fila-tabela" tabindex="0" role="region" aria-label="Tabela de complexidade"><table><caption>Complexidade da implementação encadeada apresentada</caption><thead><tr><th scope="col">Operação</th><th scope="col">Tempo</th><th scope="col">Motivo</th></tr></thead><tbody><tr><th scope="row">Empilhar</th><td>O(1)</td><td>Cria um nó e atualiza duas referências.</td></tr><tr><th scope="row">Desempilhar</th><td>O(1)</td><td>Guarda o valor e avança o topo.</td></tr><tr><th scope="row">Topo</th><td>O(1)</td><td>Lê diretamente o valor do topo.</td></tr><tr><th scope="row">Está vazia</th><td>O(1)</td><td>Compara topo com null.</td></tr></tbody></table></div>
            <p><strong>Empilhar é O(1)</strong> porque <code>novo.Proximo = topo;</code> e <code>topo = novo;</code> fazem um número constante de operações, independentemente da quantidade de nós. Não existe percurso nem deslocamento dos elementos.</p>
            <p><strong>Desempilhar é O(1)</strong> porque acessa apenas o topo e seu próximo: <code>topo = topo.Proximo;</code>. Não procura o último nó. Topo e EstaVazia também fazem trabalho constante.</p>
            <p><strong>Espaço total: O(n)</strong>, para <code>n</code> nós, cada um com um inteiro e uma referência. O campo topo ocupa espaço constante. Essa análise considera o modelo usual de custo das operações; não descreve pausas do gerenciamento de memória do ambiente C#.</p>
        </section>
        <section class="fila-secao" id="comparacao">
            <span class="fila-kicker">09 · Comparações e escolhas</span><h2>Pilha, fila e formas de armazenamento</h2>
            <div class="fila-grade duas"><article><h3>Pilha: LIFO</h3><p><strong>Último a entrar, primeiro a sair.</strong> Inserindo 10, 20, 30, remove 30 primeiro. Push e Pop acontecem no topo.</p></article><article><h3>Fila: FIFO</h3><p><strong>Primeiro a entrar, primeiro a sair.</strong> Inserindo 10, 20, 30, remove 10 primeiro. Na <a href="../view/fila_fifo.php">Fila Encadeada FIFO</a>, insere-se no fim e remove-se do início.</p></article></div>
            <p>A <a href="../view/fila_prioridade.php">Fila de Prioridades Encadeada FIFO</a> seleciona por prioridade e mantém FIFO nos empates. A pilha desta aula não seleciona por prioridade: sua regra é a ordem inversa de inserção.</p>
            <div class="fila-grade duas"><article><h3>Pilha com vetor</h3><ul><li>Elementos em armazenamento indexado.</li><li>Capacidade fixa ou redimensionável.</li><li>Usa um índice para controlar o topo.</li><li>Redimensionar pode exigir copiar elementos.</li></ul></article><article><h3>Pilha encadeada</h3><ul><li>Utiliza nós e referências.</li><li>Cresce dinamicamente, conforme a necessidade e a memória disponível.</li><li>topo aponta diretamente para o nó mais recente.</li><li>Cada inserção aloca um novo nó.</li></ul></article></div>
            <p>Ambas podem implementar o mesmo TAD Pilha e preservar LIFO. A escolha depende da capacidade esperada, dos custos de memória e do padrão de uso; nenhuma é universalmente melhor.</p>
            <div class="fila-grade duas"><article><h3>Vantagens</h3><ul><li>Tamanho dinâmico.</li><li>Push O(1) e Pop O(1).</li><li>Não exige deslocamento de elementos.</li><li>Implementação conceitualmente simples.</li><li>Adequada quando a quantidade varia em tempo de execução.</li></ul></article><article><h3>Desvantagens</h3><ul><li>Cada nó necessita uma referência adicional.</li><li>Há overhead de memória: referências, objetos e alocações têm custo.</li><li>Não existe acesso direto por índice.</li><li>Depende de gerenciamento correto das referências.</li><li>Acessar diretamente elementos internos exige quebrar a abstração normal da pilha; pela interface apresentada, é preciso retirar os que estão acima.</li></ul></article></div>
            <h3>Casos de uso</h3><ul class="fila-erros"><li><strong>Desfazer/refazer:</strong> pilhas podem guardar ações recentes, geralmente uma para cada direção.</li><li><strong>Histórico de navegação:</strong> voltar e avançar podem ser modelados com pilhas.</li><li><strong>Chamadas de funções:</strong> o retorno da chamada mais recente ilustra LIFO.</li><li><strong>Análise de expressões:</strong> guardar operadores ou conferir parênteses.</li><li><strong>Busca e retrocesso:</strong> guardar caminhos para retornar à decisão mais recente.</li><li><strong>Processamento em ordem inversa:</strong> retirar os dados na ordem oposta à entrada.</li></ul>
            <aside class="fila-alerta"><h3>Pilha de chamadas: uma analogia conceitual</h3><p>Se A chama B e B chama C, normalmente C termina antes de B, que retorna a A. Linguagens usam o conceito de pilha para controlar chamadas, mas isso não significa que a implementação interna seja a classe encadeada desta aula.</p></aside>
        </section>
        <section class="fila-secao" id="erros">
            <span class="fila-kicker">10 · Atenção às referências</span><h2>Erros comuns</h2>
            <ol class="fila-erros"><li><strong>Inserir no final em vez do topo:</strong> nesta representação, o novo elemento deixaria de ser o próximo a sair.</li><li><strong>Remover um elemento diferente do topo:</strong> viola o contrato LIFO.</li><li><strong>Esquecer <code>novo.Proximo = topo;</code>:</strong> perde a ligação do novo nó com os elementos anteriores.</li><li><strong>Atualizar topo antes de guardar o valor em Pop:</strong> perde o acesso ao valor que deveria retornar.</li><li><strong>Acessar <code>topo.Valor</code> na pilha vazia:</strong> tenta acessar uma referência nula. Verifique antes.</li><li><strong>Confundir LIFO com FIFO:</strong> na pilha, o mais recente sai primeiro; na fila, o mais antigo.</li><li><strong>Percorrer toda a pilha para Push ou Pop:</strong> adiciona trabalho O(n) sem necessidade; o topo já fornece acesso direto.</li></ol>
            <h3>Erro de Push: a ordem das atribuições importa</h3><div class="fila-codigo"><span>C# · trecho incorreto, para análise</span><pre tabindex="0"><code>topo = novo;
novo.Proximo = topo;</code></pre></div><p>Após a primeira linha, topo já referencia novo. A segunda faz o nó apontar para <strong>ele mesmo</strong>, criando um ciclo e perdendo a ligação com a cadeia antiga. A ordem correta guarda primeiro o topo antigo:</p><div class="fila-codigo"><span>C# · ordem correta</span><pre tabindex="0"><code>novo.Proximo = topo;
topo = novo;</code></pre></div>
            <h3>Erro de Pop: retornar o novo topo</h3><div class="fila-codigo"><span>C# · trecho incorreto, para análise</span><pre tabindex="0"><code>topo = topo.Proximo;
return topo.Valor;</code></pre></div><p>Isso retorna o <strong>novo topo</strong>, não o valor removido. Se havia apenas um nó, ainda tenta acessar <code>Valor</code> em <code>null</code>. Depois da verificação de pilha vazia, o correto é:</p><div class="fila-codigo"><span>C# · guardar, avançar e retornar</span><pre tabindex="0"><code>int valor = topo.Valor;
topo = topo.Proximo;
return valor;</code></pre></div>
        </section>
        <section class="fila-secao" id="revisao">
            <span class="fila-kicker">11 · Fixação</span><h2>Revise antes de praticar</h2>
            <div class="fila-perguntas"><details><summary>Após Empilhar(10), Empilhar(20), Empilhar(30), qual é o topo?</summary><p>30, pois foi o último inserido.</p></details><details><summary>Qual elemento será removido primeiro? E qual será o novo topo?</summary><p>Pop remove 30 e o novo topo passa a ser 20.</p></details><details><summary>Por que Empilhar é O(1)?</summary><p>Cria um nó, liga-o ao topo antigo e atualiza topo. O número de operações não cresce com a quantidade de elementos.</p></details><details><summary>Qual é a diferença fundamental entre Pilha e Fila?</summary><p>Pilha segue LIFO: último a entrar, primeiro a sair. Fila FIFO retira o primeiro que entrou.</p></details><details><summary>Topo() remove o elemento? O que acontece na pilha vazia?</summary><p>Não remove. Na pilha vazia, Topo e Desempilhar lançam InvalidOperationException. EstaVazia retorna true.</p></details><details><summary>O que acontece ao desempilhar o único nó?</summary><p>Seu próximo é null, então topo passa a null e a pilha fica vazia.</p></details></div>
            <a class="fila-cta" href="../view/quiz.php?assunto=pilha-encadeada">Praticar no Quiz →</a>
            <p><a href="../view/estruturas.php">Voltar às estruturas</a> · <a href="../view/fila_fifo.php">Revisar Fila Encadeada FIFO</a></p>
        </section>
    </main>
</body>
</html>
