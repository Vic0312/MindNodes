<?php
$pagina = 'inicio';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="index.php" aria-label="MindNodes">
            <span class="simbolo-logo">∞</span>
            <strong>Mind<span>Nodes</span></strong>
        </a>

        <nav class="menu">
            <a class="ativo" href="home.php">Início</a>
            <a href="estruturas.php">Estruturas</a>
            <a href="exemplos.php">Exemplos em C#</a>
            <a href="simulador.php">Simulador</a>
            <a href="quiz.php">Quiz</a>
            <a href="sobre.php">Sobre</a>
        </nav>

        <a class="botao-conta" href="view/login.php">👤</a>
    </header>

    <main>
        <section class="hero">
            <section class="hero-texto">
                <span class="etiqueta">Ambiente de Ensino de Estruturas de Dados</span>
                <h1>Aprenda Estruturas de Dados conectando ideias.</h1>
                <p>Uma plataforma educacional para entender TAD, Listas Simplesmente Encadeadas e Listas Duplamente Encadeadas com teoria, exemplos, recursos visuais e prática em C#.</p>
                <section class="acoes">
                    <a class="botao primario" href="estruturas.php">Começar estudos</a>
                    <a class="botao secundario" href="exemplos.php">Ver exemplos</a>
                </section>
            </section>

            <section class="hero-visual" aria-label="Representação visual de nós conectados">
                <span class="codigo-flutuante">&lt;/&gt;</span>
                <section class="fluxo-nos">
                    <span>10</span>
                    <strong>→</strong>
                    <span>20</span>
                    <strong>→</strong>
                    <span>30</span>
                    <strong>→</strong>
                    <span>40</span>
                </section>
                <section class="codigo-card">
                    <small>public class No&lt;T&gt;</small>
                    <small>{ Dado; Proximo; Anterior; }</small>
                </section>
            </section>
        </section>

        <section class="cards-principais">
            <article class="card destaque-claro">
                <span class="icone">⬡</span>
                <h2>TAD</h2>
                <p>Entenda o conceito de Tipo Abstrato de Dados, suas operações e sua importância na organização da lógica.</p>
                <a href="estruturas.php#tad">Explorar →</a>
            </article>

            <article class="card destaque-roxo">
                <span class="mini-nos">10 → 20 → 30</span>
                <h2>Lista Simplesmente Encadeada</h2>
                <p>Aprenda como cada nó aponta para o próximo elemento da sequência.</p>
                <a href="estruturas.php#lista-simples">Explorar →</a>
            </article>

            <article class="card destaque-azul">
                <span class="mini-nos">10 ⇄ 20 ⇄ 30</span>
                <h2>Lista Duplamente Encadeada</h2>
                <p>Veja como os nós se conectam em duas direções para facilitar navegação e remoção.</p>
                <a href="estruturas.php#lista-dupla">Explorar →</a>
            </article>
        </section>

        <section class="recursos">
            <article>
                <span>📘</span>
                <h3>Textos explicativos</h3>
                <p>Conteúdos claros para facilitar o entendimento.</p>
            </article>
            <article>
                <span>🖼️</span>
                <h3>Imagens e gifs</h3>
                <p>Recursos visuais para aprender de forma dinâmica.</p>
            </article>
            <article>
                <span>▶️</span>
                <h3>Vídeos</h3>
                <p>Aulas e demonstrações para reforçar o conteúdo.</p>
            </article>
            <article>
                <span>C#</span>
                <h3>Exemplos em C#</h3>
                <p>Códigos comentados mostrando a teoria na prática.</p>
            </article>
        </section>
    </main>
</body>
</html>
