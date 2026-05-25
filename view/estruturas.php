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
            <a href="home.php">Início</a>
            <a href="../view/sobre.php">Sobre</a>
            <a class="ativo" href="view/estruturas.php">Estruturas</a>
            <a href="../view/exemplos.php">Exemplos em C#</a>
            <a href="../view/simulador.php">Simulador</a>
            <a href="../view/quiz.php">Quiz</a>
        </nav>
        <a class="botao-conta" href="#">👤</a>
    </header>

    <main>
    <section class="banner-interno">
        <span class="etiqueta">Estruturas de Dados</span>
        <h1>O que são <span>Estruturas de Dados?</span></h1>
        <p>São formas de organizar, armazenar e manipular dados para que um programa funcione com mais clareza, eficiência e lógica.</p>
    </section>
    <section class="info-faixa">
    <div class="info-item">
        <div>
            <h2>Importância</h2>
            <p>Escolher a estrutura de dados correta melhora o desempenho do sistema,
            reduz o consumo de memória e facilita a organização do código.
            Cada estrutura possui características próprias e influencia diretamente
            na velocidade das operações realizadas pelo programa.</p>
        </div>
    </div>
    <div class="divisor"></div>
    <div class="info-item">
        <div>
            <h2>Onde são usadas</h2>
            <p>Estruturas de dados estão presentes em aplicações web, jogos,
            redes sociais, sistemas bancários, bancos de dados e mecanismos
            de busca. Elas ajudam no armazenamento, organização e acesso
            eficiente às informações.</p>
        </div>
    </div>
</section>

    <section class="conteudos">
        <article>
            <h2>TAD — Tipo Abstrato de Dados</h2>
            <p>O Tipo Abstrato de Dados (TAD) representa um modelo lógico que define
             quais dados existirão e quais operações poderão ser realizadas sobre eles,
            sem mostrar como serão implementados internamente. O objetivo é separar
            o comportamento da estrutura da sua implementação.</p>
        </article>

        <article id="lista-simples">
            <h2>Lista Simplesmente Encadeada</h2>
            <p>Uma Lista Simplesmente Encadeada é composta por nós conectados entre si.
            Cada nó armazena um valor e uma referência para o próximo elemento da lista.
            Essa estrutura permite inserções e remoções com maior flexibilidade quando
            comparada a vetores tradicionais.</p>
        </article>

        <article id="lista-dupla">
            <h2>Lista Duplamente Encadeada</h2>
            <p>A Lista Duplamente Encadeada funciona de forma semelhante à lista simples,
            porém cada nó possui duas referências: uma para o próximo elemento e outra
            para o elemento anterior. Isso permite percorrer a estrutura em ambas as
            direções e facilita determinadas operações.</p>
        </article>
    </section>
</main>
</body>
</html>
