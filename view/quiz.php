<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../view/login.php");
    exit();
}

require_once("../controller/controlador.php");

$pagina = 'quiz';
$controlador = new Controlador();
$assuntos = $controlador->listarAssuntosQuiz();
$assuntoSelecionado = isset($_GET['assunto']) ? $_GET['assunto'] : (isset($assuntos[0]['slug']) ? $assuntos[0]['slug'] : null);
$assuntoAtual = $assuntoSelecionado ? $controlador->buscarAssuntoQuiz($assuntoSelecionado) : null;
$perguntas = $assuntoAtual ? $controlador->buscarPerguntasQuiz($assuntoSelecionado) : [];
$mensagemErro = isset($_GET['erro']) ? $_GET['erro'] : null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/quiz.css">
</head>

<body>
    <header class="topo">
        <a class="marca" href="../index.php">
            <span class="simbolo-logo">∞</span>
            <strong>Mind<span>Nodes</span></strong>
        </a>

        <nav class="menu">
            <a href="../view/home.php">Início</a>
            <a href="../view/sobre.php">Sobre</a>
            <a href="../view/estruturas.php">Estruturas</a>
            <a href="../view/exemplos.php">Exemplos</a>
            <a href="../view/simulador.php">Simulador</a>
            <a class="ativo" href="../view/quiz.php">Quiz</a>
            <a href="../view/desempenho.php">Desempenho</a>
            
        </nav>

        <a class="botao-conta" href="../view/perfil.php">👤</a>
    </header>

    <main>
        <section class="banner-interno">
            <span class="etiqueta">Quiz</span>
            <h1>Teste seus <span>conhecimentos</span></h1>
            <p>Escolha um assunto, responda as perguntas e acompanhe seus acertos, erros e evolução na página de desempenho.</p>
        </section>

        <?php if ($mensagemErro): ?>
            <section class="alerta-quiz">
                Não foi possível salvar sua tentativa. Verifique se o banco foi atualizado com as tabelas do quiz.
            </section>
        <?php endif; ?>

        <section class="quiz-layout">
            <aside class="painel-assuntos">
                <span class="subtitulo-secao">Assuntos</span>
                <h2>Escolha o quiz</h2>

                <div class="lista-assuntos">
                    <?php foreach ($assuntos as $assunto): ?>
                        <a
                            class="<?php echo $assuntoSelecionado === $assunto['slug'] ? 'ativo' : ''; ?>"
                            href="../view/quiz.php?assunto=<?php echo urlencode($assunto['slug']); ?>"
                        >
                            <strong><?php echo htmlspecialchars($assunto['titulo']); ?></strong>
                            <span><?php echo (int) $assunto['total_perguntas']; ?> perguntas</span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <a class="botao-desempenho" href="../view/desempenho.php">Ver meus resultados</a>
            </aside>

            <section class="area-quiz">
                <?php if ($assuntoAtual && count($perguntas) > 0): ?>
                    <section class="cabecalho-quiz">
                        <span class="subtitulo-secao">Quiz atual</span>
                        <h2><?php echo htmlspecialchars($assuntoAtual['titulo']); ?></h2>
                        <p><?php echo htmlspecialchars($assuntoAtual['descricao']); ?></p>
                    </section>

                    <form method="POST" action="../processamento/processamento.php" class="form-quiz">
                        <input type="hidden" name="acao" value="salvarQuiz">
                        <input type="hidden" name="assunto" value="<?php echo htmlspecialchars($assuntoAtual['slug']); ?>">

                        <?php foreach ($perguntas as $indice => $pergunta): ?>
                            <article class="quiz-card">
                                <span class="numero-questao">Questão <?php echo $indice + 1; ?></span>
                                <h3><?php echo htmlspecialchars($pergunta['enunciado']); ?></h3>

                                <section class="alternativas">
                                    <?php foreach ($pergunta['alternativas'] as $alternativa): ?>
                                        <label>
                                            <input
                                                type="radio"
                                                name="respostas[<?php echo (int) $pergunta['id_pergunta']; ?>]"
                                                value="<?php echo (int) $alternativa['id_alternativa']; ?>"
                                                required
                                            >
                                            <span><?php echo htmlspecialchars($alternativa['texto']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </section>
                            </article>
                        <?php endforeach; ?>

                        <section class="acoes-quiz">
                            <button type="submit">Finalizar quiz</button>
                            <a class="botao secundario" href="../view/desempenho.php">Meus resultados</a>
                        </section>
                    </form>
                <?php else: ?>
                    <section class="quiz-vazio">
                        <h2>Nenhum quiz encontrado</h2>
                        <p>Atualize o banco de dados com o novo script `mindnode.sql` para carregar os assuntos e perguntas.</p>
                    </section>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
