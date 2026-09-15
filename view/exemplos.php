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

$pagina = 'exemplos';
require __DIR__ . '/partials/exemplos_conteudo.php';
$estrutura = $_GET['estrutura'] ?? 'tad';
if (!is_string($estrutura) || !isset($exemplos[$estrutura])) $estrutura = 'tad';
$exemplo = $exemplos[$estrutura];
function textoExemplo($texto) {
    echo htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Exemplos em C#</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/exemplos.css">
    <script src="../js/exemplos.js" defer></script>
</head>
<body>
    <header class="topo">
        <a class="marca" href="../view/home.php"><span class="simbolo-logo">∞</span><strong>Mind<span>Nodes</span></strong></a>
        <nav class="menu">
            <a href="../view/home.php">Início</a>
            <a href="../view/sobre.php">Sobre</a>
            <a href="../view/estruturas.php">Estruturas</a>
            <a class="ativo" href="../view/exemplos.php">Exemplos</a>
            <a href="../view/simulador.php">Simulador</a>
            <a href="../view/quiz.php">Quiz</a>
            <a href="../view/desempenho.php">Desempenho</a>
            
        </nav>
        <a class="perfil-usuario" href="../view/perfil.php" title="Perfil de <?php echo htmlspecialchars($nomeUsuario); ?>">
            <img
                src="<?php echo htmlspecialchars($fotoExibicao); ?>"
                alt="Foto de perfil de <?php echo htmlspecialchars($nomeUsuario); ?>"
                class="foto-perfil-nav"
            >
        </a>
    </header>

    <main>
        <section class="banner-interno">
            <span class="etiqueta">Exemplos práticos</span>
            <h1>Exemplos em <span>C#</span></h1>
            <p>Veja como as estruturas de dados podem ser representadas em código.</p>
        </section>

        <section class="area-codigo">
            <nav class="menu-lateral" aria-label="Exemplos por estrutura">
                <?php foreach ($exemplos as $chave => $item): ?>
                    <a href="?estrutura=<?php textoExemplo($chave); ?>" data-exemplo="<?php textoExemplo($chave); ?>"<?php if ($estrutura === $chave): ?> class="ativo" aria-current="page"<?php endif; ?>><?php textoExemplo($item['titulo']); ?></a>
                <?php endforeach; ?>
            </nav>

            <section class="codigo-box" aria-labelledby="titulo-codigo">
                <h2 id="titulo-codigo"><?php textoExemplo($exemplo['titulo']); ?></h2>
                <div class="exemplo-resumo">
                    <p id="texto-explicacao"><?php textoExemplo($exemplo['descricao']); ?></p>
                    <h3>Operações demonstradas</h3>
                    <ul id="operacoes-exemplo"><?php foreach ($exemplo['operacoes'] as $operacao): ?><li><?php textoExemplo($operacao); ?></li><?php endforeach; ?></ul>
                    <p class="exemplo-nota">Cada exemplo é independente e tem sua própria classe No, quando necessária. Para executar localmente, reúna a implementação e a classe Programa no mesmo arquivo. Os exemplos usam referências no estilo tradicional de C#; com análise de nulidade habilitada, use No? nas referências que podem receber null.</p>
                </div>
                <h3 class="codigo-rotulo">Implementação C#</h3>
                <pre tabindex="0" aria-label="Implementação C#"><code id="codigo-exemplo"><?php textoExemplo($exemplo['codigo']); ?></code></pre>
                <h3 class="codigo-rotulo">Exemplo de uso · Programa.Main</h3>
                <pre tabindex="0" aria-label="Exemplo de uso C#"><code id="uso-exemplo"><?php textoExemplo($exemplo['uso']); ?></code></pre>
                <h3 class="codigo-rotulo">Saída esperada</h3>
                <pre tabindex="0" aria-label="Saída esperada"><code id="saida-exemplo"><?php textoExemplo($exemplo['saida']); ?></code></pre>
                <p class="exemplo-resumo" id="execucao-exemplo"><?php textoExemplo($exemplo['execucao']); ?></p>
            </section>

            <section class="quiz-card explicacao-card" aria-labelledby="observar-titulo">
                <h2 id="observar-titulo">O que observar</h2>
                <ul id="observar-exemplo"><?php foreach ($exemplo['observar'] as $observacao): ?><li><?php textoExemplo($observacao); ?></li><?php endforeach; ?></ul>
                <h3>Complexidade resumida</h3>
                <p id="complexidade-exemplo"><?php textoExemplo($exemplo['complexidade']); ?></p>
                <p>n representa a quantidade de elementos armazenados.</p>
                <div class="exemplo-acoes">
                    <a id="teoria-exemplo" href="<?php textoExemplo($exemplo['teoria']); ?>">Ver conteúdo</a>
                    <a id="quiz-exemplo" href="quiz.php?assunto=<?php textoExemplo($exemplo['quiz']); ?>">Praticar no Quiz</a>
                </div>
            </section>
        </section>
    </main>
    <script type="application/json" id="dados-exemplos"><?php echo json_encode($exemplos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); ?></script>
</body>
</html>
