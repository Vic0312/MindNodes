<?php
$pagina = 'estruturas';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Estruturas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estruturas.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="../index.php"><span class="simbolo-logo">∞</span><strong>Mind<span>Nodes</span></strong></a>
        <nav class="menu">
            <a href="../index.php">Início</a>
            <a class="ativo" href="view/estruturas.php">Estruturas</a>
            <a href="../view/exemplos.php">Exemplos em C#</a>
            <a href="../view/simulador.php">Simulador</a>
            <a href="../view/quiz.php">Quiz</a>
            <a href="../view/sobre.php">Sobre</a>
        </nav>
        <a class="botao-conta" href="#">👤</a>
    </header>

    <main>
        <section class="banner-interno">
            <span class="etiqueta">Estruturas de Dados</span>
            <h1>O que são <span>Estruturas de Dados?</span></h1>
            <p>São formas de organizar, armazenar e manipular dados para que um programa funcione com mais clareza, eficiência e lógica.</p>
        </section>

        <section class="painel-conceito">
            <section class="coluna-info">
                <article>
                    <span>⬡</span>
                    <h2>Conceito</h2>
                    <p>Estruturas de Dados definem como as informações serão guardadas e quais operações podem ser feitas sobre elas.</p>
                </article>
                <article>
                    <span>✓</span>
                    <h2>Importância</h2>
                    <p>Escolher a estrutura correta ajuda a reduzir tempo de execução, economizar memória e deixar o código mais organizado.</p>
                </article>
                <article>
                    <span>📍</span>
                    <h2>Onde são usadas</h2>
                    <p>Aplicações web, jogos, sistemas de cadastro, banco de dados, histórico de navegação e muito mais.</p>
                </article>
            </section>

            <section class="diagrama">
                <h2>Organizando dados para uso eficiente</h2>
                <section class="linha-diagrama">
                    <p>Dados brutos</p>
                    <strong>→</strong>
                    <ul>
                        <li>Lista</li>
                        <li>Fila</li>
                        <li>Pilha</li>
                        <li>Árvore</li>
                    </ul>
                    <strong>→</strong>
                    <p>Aplicações</p>
                </section>
            </section>
        </section>

        <section class="conteudos" id="tad">
            <article>
                <h2>TAD — Tipo Abstrato de Dados</h2>
                <p>Um TAD descreve um conjunto de dados e operações sem focar diretamente em como isso será implementado internamente.</p>
            </article>
            <article id="lista-simples">
                <h2>Lista Simplesmente Encadeada</h2>
                <p>É formada por nós, onde cada nó possui um dado e uma referência para o próximo nó da lista.</p>
            </article>
            <article id="lista-dupla">
                <h2>Lista Duplamente Encadeada</h2>
                <p>É semelhante à lista simples, mas cada nó aponta para o próximo e também para o anterior.</p>
            </article>
        </section>
    </main>
</body>
</html>
