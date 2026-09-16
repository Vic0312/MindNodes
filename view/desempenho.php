<?php

session_start();

if (empty($_SESSION['estaLogado']) || !isset($_SESSION['usuario_id'])) {
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
$desempenho = $controlador->buscarMeuDesempenho();
$resumo = $desempenho['resumo'];
$assuntos = $desempenho['assuntos'];
$tentativas = $desempenho['tentativas'];
$tentativaSelecionada = null;
$tentativaInvalida = false;

if (isset($_GET['tentativa'])) {
    $tentativaSelecionada = $controlador->buscarMinhaTentativa($_GET['tentativa']);
    $tentativaInvalida = !$tentativaSelecionada;
}

$totalPerguntas = (int) $resumo['total_perguntas'];
$totalAcertos = (int) $resumo['total_acertos'];
$formatarPercentual = static function ($acertos, $total) {
    return $total > 0 ? number_format($acertos / $total * 100, 1, ',', '.') . '%' : '0%';
};

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
            <p>Veja seus resultados por assunto, acompanhe suas tentativas e revise suas respostas.</p>
        </section>

        <section class="resumo-desempenho">
            <article>
                <span>Quizzes realizados</span>
                <strong><?php echo (int) (isset($resumo['total_tentativas']) ? $resumo['total_tentativas'] : 0); ?></strong>
            </article>

            <article>
                <span>Questões respondidas</span>
                <strong><?php echo $totalPerguntas; ?></strong>
            </article>

            <article>
                <span>Acertos</span>
                <strong><?php echo $totalAcertos; ?></strong>
            </article>

            <article>
                <span>Taxa geral de acertos</span>
                <strong><?php echo $formatarPercentual($totalAcertos, $totalPerguntas); ?></strong>
            </article>

            <article>
                <span>Moedas ganhas em Quizzes</span>
                <strong><?php echo (int) $resumo['moedas_ganhas']; ?> <span aria-hidden="true">🪙</span></strong>
            </article>
        </section>

        <section class="desempenho-assuntos" aria-labelledby="titulo-assuntos">
            <div class="desempenho-secao-topo">
                <div><span class="subtitulo-secao">Por assunto</span><h2 id="titulo-assuntos">Desempenho por assunto</h2></div>
                <a class="botao primario" href="quiz.php">Praticar no Quiz</a>
            </div>
            <div class="desempenho-assuntos-grid">
                <?php foreach ($assuntos as $assunto): ?>
                    <?php $praticado = (int) $assunto['total_tentativas'] > 0; ?>
                    <article class="desempenho-assunto-card">
                        <h3><?php echo htmlspecialchars($assunto['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h3>
                        <?php if ($praticado): ?>
                            <?php $percentualAssunto = $formatarPercentual((int) $assunto['total_acertos'], (int) $assunto['total_perguntas']); ?>
                            <p class="desempenho-assunto-taxa"><?php echo $percentualAssunto; ?> de aproveitamento</p>
                            <div class="desempenho-barra" role="img" aria-label="Aproveitamento: <?php echo $percentualAssunto; ?>"><span style="width: <?php echo (int) round((int) $assunto['total_perguntas'] > 0 ? (int) $assunto['total_acertos'] / (int) $assunto['total_perguntas'] * 100 : 0); ?>%"></span></div>
                            <p><?php echo (int) $assunto['total_tentativas']; ?> tentativas · <?php echo (int) $assunto['total_perguntas']; ?> questões · <?php echo (int) $assunto['total_acertos']; ?> acertos</p>
                            <p>+<?php echo (int) $assunto['moedas_ganhas']; ?> moedas ganhas</p>
                        <?php else: ?>
                            <p>Ainda não praticado.</p>
                            <a href="quiz.php?assunto=<?php echo rawurlencode($assunto['slug']); ?>">Praticar este assunto</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="desempenho-layout">
            <section class="historico-card">
                <span class="subtitulo-secao">Histórico</span>
                <h2>Tentativas realizadas</h2>

                <?php if (count($tentativas) > 0): ?>
                    <section class="lista-tentativas">
                        <?php foreach ($tentativas as $tentativa): ?>
                            <?php
                                $percentual = $formatarPercentual((int) $tentativa['total_acertos'], (int) $tentativa['total_perguntas']);
                            ?>

                            <a
                                class="<?php echo $tentativaSelecionada && (int) $tentativaSelecionada['id_tentativa'] === (int) $tentativa['id_tentativa'] ? 'ativo' : ''; ?>"
                                href="../view/desempenho.php?tentativa=<?php echo (int) $tentativa['id_tentativa']; ?>"
                            >
                                <div>
                                    <strong><?php echo htmlspecialchars($tentativa['titulo']); ?></strong>
                                    <span><?php echo date('d/m/Y', strtotime($tentativa['data_tentativa'])); ?> às <?php echo date('H:i', strtotime($tentativa['data_tentativa'])); ?></span>
                                </div>

                                <b><?php echo (int) $tentativa['total_acertos']; ?>/<?php echo (int) $tentativa['total_perguntas']; ?> acertos · <?php echo $percentual; ?><br>+<?php echo (int) $tentativa['moedas_ganhas']; ?> moedas <span class="revisar-link">Revisar tentativa</span></b>
                            </a>
                        <?php endforeach; ?>
                    </section>
                <?php else: ?>
                    <p class="texto-vazio">Você ainda não realizou nenhum Quiz.</p>
                    <a class="botao primario" href="quiz.php">Começar um Quiz</a>
                <?php endif; ?>
            </section>

            <section class="revisao-card">
                <?php if ($tentativaSelecionada): ?>
                    <?php
                        $percentualTentativa = $formatarPercentual((int) $tentativaSelecionada['total_acertos'], (int) $tentativaSelecionada['total_perguntas']);
                    ?>

                    <span class="subtitulo-secao">Revisão</span>
                    <h2><?php echo htmlspecialchars($tentativaSelecionada['titulo']); ?></h2>
                    <p class="resultado-final">
                        Resultado: <?php echo (int) $tentativaSelecionada['total_acertos']; ?>
                        de <?php echo (int) $tentativaSelecionada['total_perguntas']; ?> acertos
                        (<?php echo $percentualTentativa; ?>).
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
                <?php elseif ($tentativaInvalida): ?>
                    <section class="estado-inicial" role="status">
                        <span class="subtitulo-secao">Revisão</span>
                        <h2>Tentativa não encontrada</h2>
                        <p>Esta tentativa não está disponível para sua conta.</p>
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
