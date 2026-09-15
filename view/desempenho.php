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

require_once __DIR__ . '/../controller/QuizController.php';

$pagina = 'desempenho';
$controlador = new QuizController();
$idUsuario = $_SESSION['usuario_id'];
$desempenho = $controlador->buscarDesempenhoUsuario($idUsuario);
$resumo = $desempenho['resumo'];
$tentativas = $desempenho['tentativas'];
$tentativaSelecionada = null;

if (isset($_GET['tentativa'])) {
    $tentativaSelecionada = $controlador->buscarTentativaQuiz($_GET['tentativa'], $idUsuario);
}

$totalPerguntas = (int) (isset($resumo['total_perguntas']) ? $resumo['total_perguntas'] : 0);
$totalAcertos = (int) (isset($resumo['total_acertos']) ? $resumo['total_acertos'] : 0);
$aproveitamento = $totalPerguntas > 0 ? round(($totalAcertos / $totalPerguntas) * 100) : 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Desempenho</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/quiz.css">
    <link rel="stylesheet" href="../css/navegacao.css">
    <script src="../js/navegacao.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main>
        <section class="banner-interno">
            <span class="etiqueta">Meus Resultados</span>
            <h1>Acompanhe seu <span>desempenho</span></h1>
            <p>Veja seu aproveitamento geral, histórico de tentativas e uma revisão detalhada dos seus erros.</p>
        </section>

        <section class="resumo-desempenho">
            <article>
                <span>Tentativas</span>
                <strong><?php echo (int) (isset($resumo['total_tentativas']) ? $resumo['total_tentativas'] : 0); ?></strong>
            </article>

            <article>
                <span>Acertos</span>
                <strong><?php echo $totalAcertos; ?>/<?php echo $totalPerguntas; ?></strong>
            </article>

            <article>
                <span>Aproveitamento</span>
                <strong><?php echo $aproveitamento; ?>%</strong>
            </article>
        </section>

        <section class="desempenho-layout">
            <section class="historico-card">
                <span class="subtitulo-secao">Histórico</span>
                <h2>Tentativas realizadas</h2>

                <?php if (count($tentativas) > 0): ?>
                    <section class="lista-tentativas">
                        <?php foreach ($tentativas as $tentativa): ?>
                            <?php
                                $percentual = (int) $tentativa['total_perguntas'] > 0
                                    ? round(((int) $tentativa['total_acertos'] / (int) $tentativa['total_perguntas']) * 100)
                                    : 0;
                            ?>

                            <a
                                class="<?php echo isset($_GET['tentativa']) && (int) $_GET['tentativa'] === (int) $tentativa['id_tentativa'] ? 'ativo' : ''; ?>"
                                href="../view/desempenho.php?tentativa=<?php echo (int) $tentativa['id_tentativa']; ?>"
                            >
                                <div>
                                    <strong><?php echo htmlspecialchars($tentativa['titulo']); ?></strong>
                                    <span><?php echo date('d/m/Y H:i', strtotime($tentativa['data_tentativa'])); ?></span>
                                </div>

                                <b><?php echo (int) $tentativa['total_acertos']; ?>/<?php echo (int) $tentativa['total_perguntas']; ?> · <?php echo $percentual; ?>% · +<?php echo (int) $tentativa['moedas_ganhas']; ?> moedas</b>
                            </a>
                        <?php endforeach; ?>
                    </section>
                <?php else: ?>
                    <p class="texto-vazio">Você ainda não finalizou nenhum quiz.</p>
                    <a class="botao primario" href="../view/quiz.php">Responder primeiro quiz</a>
                <?php endif; ?>
            </section>

            <section class="revisao-card">
                <?php if ($tentativaSelecionada): ?>
                    <?php
                        $percentualTentativa = (int) $tentativaSelecionada['total_perguntas'] > 0
                            ? round(((int) $tentativaSelecionada['total_acertos'] / (int) $tentativaSelecionada['total_perguntas']) * 100)
                            : 0;
                    ?>

                    <span class="subtitulo-secao">Revisão</span>
                    <h2><?php echo htmlspecialchars($tentativaSelecionada['titulo']); ?></h2>
                    <p class="resultado-final">
                        Resultado: <?php echo (int) $tentativaSelecionada['total_acertos']; ?>
                        de <?php echo (int) $tentativaSelecionada['total_perguntas']; ?> acertos
                        (<?php echo $percentualTentativa; ?>%).
                    </p>
                    <p class="recompensa-quiz">+<?php echo (int) $tentativaSelecionada['moedas_ganhas']; ?> moedas ganhas</p>

                    <section class="lista-revisao">
                        <?php foreach ($tentativaSelecionada['respostas'] as $resposta): ?>
                            <article class="<?php echo (int) $resposta['acertou'] === 1 ? 'correta' : 'incorreta'; ?>">
                                <span><?php echo (int) $resposta['acertou'] === 1 ? 'Acertou' : 'Revisar'; ?></span>
                                <h3><?php echo htmlspecialchars($resposta['enunciado']); ?></h3>

                                <?php if ($resposta['tipo'] === 'codigo' && !empty($resposta['codigo'])): ?>
                                    <div class="codigo-questao"><span>C#</span><pre><code><?php echo htmlspecialchars($resposta['codigo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></code></pre></div>
                                <?php endif; ?>

                                <p>
                                    Sua resposta:
                                    <strong><?php echo htmlspecialchars(isset($resposta['resposta_marcada']) ? $resposta['resposta_marcada'] : 'Não respondida'); ?></strong>
                                </p>

                                <?php if ((int) $resposta['acertou'] !== 1): ?>
                                    <p>
                                        Resposta correta:
                                        <strong><?php echo htmlspecialchars($resposta['resposta_correta']); ?></strong>
                                    </p>
                                <?php endif; ?>

                                <small><?php echo htmlspecialchars($resposta['explicacao']); ?></small>
                            </article>
                        <?php endforeach; ?>
                    </section>
                <?php else: ?>
                    <section class="estado-inicial">
                        <span class="subtitulo-secao">Revisão</span>
                        <h2>Selecione uma tentativa</h2>
                        <p>Abra uma tentativa do histórico para ver quais questões você acertou, quais errou e o motivo da resposta correta.</p>
                    </section>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
