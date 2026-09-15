<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../view/login.php");
    exit();
}

$nomeUsuario = $_SESSION['usuario_nome'] ?? "Usuário";
$fotoBanco = $_SESSION['usuario_foto'] ?? null;

$fotoExibicao = "../img/default-img.avif";

if (!empty($fotoBanco)) {
    $fotoBanco = trim($fotoBanco);

    if (str_starts_with($fotoBanco, "data:image")) {
        $fotoExibicao = $fotoBanco;
    } else {
        $caminhosPossiveis = [
            "../" . $fotoBanco,
            "../uploads/usuarios/" . basename($fotoBanco),
            "../img/" . basename($fotoBanco)
        ];

        foreach ($caminhosPossiveis as $caminho) {
            if (file_exists(__DIR__ . "/" . $caminho)) {
                $fotoExibicao = $caminho;
                break;
            }
        }
    }
}

$pagina = 'estruturas';
require_once __DIR__ . '/partials/multimidia.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Estruturas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estruturas.css">
    <link rel="stylesheet" href="../css/multimidia.css">
    <script src="../js/multimidia.js" defer></script>
    <link rel="stylesheet" href="../css/navegacao.css">
    <script src="../js/navegacao.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

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

        <article id="tad" onclick="window.location.href='../view/exemplos.php?estrutura=tad'">
            <h2>TAD — Tipo Abstrato de Dados</h2>
            <p>O Tipo Abstrato de Dados (TAD) representa um modelo lógico que define
             quais dados existirão e quais operações poderão ser realizadas sobre eles,
            sem mostrar como serão implementados internamente. O objetivo é separar
            o comportamento da estrutura da sua implementação.</p>
        </article>

        <article id="lista-simples"
        onclick="window.location.href='../view/exemplos.php?estrutura=simples'">
            <h2>Lista Simplesmente Encadeada</h2>
            <p>Uma Lista Simplesmente Encadeada é composta por nós conectados entre si.
            Cada nó armazena um valor e uma referência para o próximo elemento da lista.
            Essa estrutura permite inserções e remoções com maior flexibilidade quando
            comparada a vetores tradicionais.</p>
        </article>

        <article id="lista-dupla"
        onclick="window.location.href='../view/exemplos.php?estrutura=dupla'">
            <h2>Lista Duplamente Encadeada</h2>
            <p>A Lista Duplamente Encadeada funciona de forma semelhante à lista simples,
            porém cada nó possui duas referências: uma para o próximo elemento e outra
            para o elemento anterior. Isso permite percorrer a estrutura em ambas as
            direções e facilita determinadas operações.</p>
        </article>

        <a class="conteudo-link" id="fila-fifo" href="../view/fila_fifo.php">
            <h2>Fila Encadeada FIFO</h2>
            <p>Aprenda a enfileirar no fim e desenfileirar no início com nós ligados, referências <code>inicio</code> e <code>fim</code> e exemplos completos em C#.</p>
            <span>Estudar Fila Encadeada FIFO →</span>
        </a>

        <a class="conteudo-link" id="fila-prioridade" href="../view/fila_prioridade.php">
            <h2>Fila de Prioridades Encadeada FIFO</h2>
            <p>Entenda como nós são ordenados por prioridade e por que a ordem de chegada continua valendo quando há empate.</p>
            <span>Estudar Fila de Prioridades →</span>
        </a>

        <a class="conteudo-link" id="pilha-encadeada" href="../view/pilha_encadeada.php">
            <h2>Pilha Encadeada</h2>
            <p>Entenda LIFO, os nós e a referência topo. Aprenda a empilhar, desempilhar e consultar com exemplos completos em C#.</p>
            <span>Estudar Pilha Encadeada →</span>
        </a>

    </section>
    <nav class="midia-navegacao" aria-label="Recursos visuais das seis estruturas">
        <a href="#midia-tad">TAD visual</a>
        <a href="#midia-simples">Lista Simples visual</a>
        <a href="#midia-dupla">Lista Dupla visual</a>
        <a href="fila_fifo.php#midia-fila-fifo">Fila FIFO visual</a>
        <a href="fila_prioridade.php#midia-fila-prioridade">Fila de Prioridades visual</a>
        <a href="pilha_encadeada.php#midia-pilha-encadeada">Pilha visual</a>
    </nav>
    <?php
    renderizarMultimidia('tad');
    renderizarMultimidia('simples');
    renderizarMultimidia('dupla');
    ?>
</main>
</body>
</html>
