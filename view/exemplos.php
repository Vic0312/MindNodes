<?php
$pagina = 'exemplos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Exemplos em C#</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/exemplos.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="index.php"><span class="simbolo-logo">∞</span><strong>Mind<span>Nodes</span></strong></a>
        <nav class="menu">
            <a href="index.php">Início</a>
            <a href="estruturas.php">Estruturas</a>
            <a class="ativo" href="exemplos.php">Exemplos em C#</a>
            <a href="simulador.php">Simulador</a>
            <a href="quiz.php">Quiz</a>
            <a href="sobre.php">Sobre</a>
        </nav>
        <a class="botao-conta" href="#">👤</a>
    </header>

    <main>
        <section class="banner-interno">
            <span class="etiqueta">Exemplos práticos</span>
            <h1>Exemplos em <span>C#</span></h1>
            <p>Veja como a ideia de nó pode ser representada em código.</p>
        </section>

        <section class="area-codigo">
            <aside class="menu-lateral">
                <a class="ativo" href="#">Classe Nó</a>
                <a href="#">ListaSimples</a>
                <a href="#">Inserir</a>
                <a href="#">Remover</a>
                <a href="#">Percorrer</a>
                <a href="#">ListaDupla</a>
            </aside>

            <section class="codigo-box">
                <h2>Classe que representa um nó</h2>
<pre><code>// Classe que representa um nó da lista
public class No&lt;T&gt;
{
    public T Dado { get; set; }
    public No&lt;T&gt; Proximo { get; set; }
    public No&lt;T&gt; Anterior { get; set; }

    public No(T dado)
    {
        Dado = dado;
        Proximo = null;
        Anterior = null;
    }
}</code></pre>
            </section>

            <section class="quiz-card">
                <h2>Teste rápido</h2>
                <p>Em uma lista simplesmente encadeada, cada nó aponta para:</p>
                <label><input type="radio" name="q1"> O nó anterior</label>
                <label><input type="radio" name="q1" checked> O próximo nó</label>
                <label><input type="radio" name="q1"> Todos os nós</label>
                <strong>Pontuação: 4/5</strong>
            </section>
        </section>
    </main>
</body>
</html>
