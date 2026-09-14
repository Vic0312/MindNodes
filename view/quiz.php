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

$pagina = 'quiz';
$controlador = new QuizController();
$assuntos = $controlador->listarAssuntosQuiz();
$assuntoSelecionado = isset($_GET['assunto']) ? $_GET['assunto'] : (isset($assuntos[0]['slug']) ? $assuntos[0]['slug'] : null);
$assuntoAtual = $assuntoSelecionado ? $controlador->buscarAssuntoQuiz($assuntoSelecionado) : null;
$perguntas = $assuntoAtual ? $controlador->buscarPerguntasQuiz($assuntoSelecionado) : [];
$mensagemErro = isset($_GET['erro']) ? $_GET['erro'] : null;
$estadoTentativa = $assuntoAtual && $perguntas ? $controlador->iniciarTentativaQuiz($assuntoSelecionado) : null;
$mensagemHabilidade = $_SESSION['quiz_habilidade_mensagem'] ?? null;
unset($_SESSION['quiz_habilidade_mensagem']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/quiz.css">
    <script src="../js/quiz-habilidades.js" defer></script>
</head>

<body>
    <header class="topo">
        <a class="marca" href="../view/home.php">
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
            <span class="etiqueta">Quiz</span>
            <h1>Teste seus <span>conhecimentos</span></h1>
            <p>Escolha um assunto, responda as perguntas e acompanhe seus acertos, erros e evolução na página de desempenho.</p>
        </section>

        <?php if ($mensagemErro): ?>
            <section class="alerta-quiz">
                Não foi possível salvar sua tentativa. Verifique se o banco foi atualizado com as tabelas do quiz.
            </section>
        <?php endif; ?>

        <?php if ($mensagemHabilidade): ?>
            <section class="alerta-quiz" role="status"><?php echo htmlspecialchars($mensagemHabilidade, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></section>
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

                    <section class="poderes-avatar" aria-labelledby="poderes-titulo">
                        <div class="poderes-cabecalho"><span class="subtitulo-secao">Avatar equipado</span><h3 id="poderes-titulo">Poderes do Avatar</h3></div>
                        <?php if (!$estadoTentativa['habilidades']): ?>
                            <p>Equipe itens com habilidades para liberar poderes no Quiz.</p>
                        <?php else: ?>
                            <div class="poderes-lista">
                                <?php foreach (['dica' => 'Dica', 'eliminar_alternativa' => 'Eliminar alternativa', 'resumo_rapido' => 'Resumo rápido'] as $chave => $rotulo): ?>
                                    <?php if (isset($estadoTentativa['habilidades'][$chave])): ?>
                                        <span class="poder-indicador" data-habilidade-contador="<?php echo $chave; ?>"><?php echo htmlspecialchars($rotulo); ?>: <strong><?php echo (int) $estadoTentativa['habilidades'][$chave]['quantidade_restante']; ?></strong>/<?php echo (int) $estadoTentativa['habilidades'][$chave]['quantidade_total']; ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($estadoTentativa['habilidades']['resumo_rapido'])): ?>
                                <form class="quiz-habilidade-form" method="post" action="../processamento/processamento.php">
                                    <input type="hidden" name="acao" value="usarHabilidade"><input type="hidden" name="habilidade" value="resumo_rapido"><input type="hidden" name="csrf" value="<?php echo htmlspecialchars($estadoTentativa['token']); ?>">
                                    <button type="submit" data-habilidade-botao="resumo_rapido" <?php echo $estadoTentativa['habilidades']['resumo_rapido']['quantidade_restante'] <= 0 ? 'disabled' : ''; ?>>Resumo rápido</button>
                                </form>
                            <?php endif; ?>
                            <div class="painel-resumo" id="painel-resumo" <?php echo $estadoTentativa['resumo_revelado'] === null ? 'hidden' : ''; ?>><?php echo htmlspecialchars($estadoTentativa['resumo_revelado'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <p class="poderes-feedback" id="poderes-feedback" role="status" aria-live="polite"></p>
                    </section>

                    <div class="form-quiz">

                        <?php foreach ($perguntas as $indice => $pergunta): ?>
                            <article class="quiz-card" id="questao-<?php echo (int) $pergunta['id_pergunta']; ?>">
                                <span class="numero-questao">Questão <?php echo $indice + 1; ?></span>
                                <h3><?php echo htmlspecialchars($pergunta['enunciado']); ?></h3>

                                <?php if ($pergunta['tipo'] === 'codigo' && !empty($pergunta['codigo'])): ?>
                                    <div class="codigo-questao"><span>C#</span><pre><code><?php echo htmlspecialchars($pergunta['codigo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></code></pre></div>
                                <?php endif; ?>

                                <?php if (isset($estadoTentativa['habilidades']['dica']) || isset($estadoTentativa['habilidades']['eliminar_alternativa'])): ?>
                                    <div class="acoes-habilidades">
                                        <?php foreach (['dica' => 'Dica', 'eliminar_alternativa' => 'Eliminar alternativa'] as $chave => $rotulo): ?>
                                            <?php if (isset($estadoTentativa['habilidades'][$chave])): ?>
                                                <form class="quiz-habilidade-form" method="post" action="../processamento/processamento.php">
                                                    <input type="hidden" name="acao" value="usarHabilidade"><input type="hidden" name="habilidade" value="<?php echo $chave; ?>"><input type="hidden" name="id_pergunta" value="<?php echo (int) $pergunta['id_pergunta']; ?>"><input type="hidden" name="csrf" value="<?php echo htmlspecialchars($estadoTentativa['token']); ?>">
                                                    <button type="submit" data-habilidade-botao="<?php echo $chave; ?>" <?php echo $estadoTentativa['habilidades'][$chave]['quantidade_restante'] <= 0 ? 'disabled' : ''; ?>><?php echo $rotulo; ?></button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="painel-dica" data-dica-pergunta="<?php echo (int) $pergunta['id_pergunta']; ?>" <?php echo !isset($estadoTentativa['dicas_reveladas'][$pergunta['id_pergunta']]) ? 'hidden' : ''; ?>><?php echo htmlspecialchars($estadoTentativa['dicas_reveladas'][$pergunta['id_pergunta']] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                                <?php endif; ?>

                                <section class="alternativas">
                                    <?php foreach ($pergunta['alternativas'] as $alternativa): ?>
                                        <?php $eliminada = in_array((int) $alternativa['id_alternativa'], $estadoTentativa['eliminadas'][$pergunta['id_pergunta']] ?? [], true); ?>
                                        <label class="<?php echo $eliminada ? 'alternativa-eliminada' : ''; ?>" data-alternativa-id="<?php echo (int) $alternativa['id_alternativa']; ?>">
                                            <input
                                                type="radio"
                                                form="quiz-respostas"
                                                name="respostas[<?php echo (int) $pergunta['id_pergunta']; ?>]"
                                                value="<?php echo (int) $alternativa['id_alternativa']; ?>"
                                                required
                                                <?php echo $eliminada ? 'disabled' : ''; ?>
                                            >
                                            <span><?php echo htmlspecialchars($alternativa['texto']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </section>
                            </article>
                        <?php endforeach; ?>

                        <form id="quiz-respostas" method="POST" action="../processamento/processamento.php">
                            <input type="hidden" name="acao" value="salvarQuiz">
                            <input type="hidden" name="assunto" value="<?php echo htmlspecialchars($assuntoAtual['slug']); ?>">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($estadoTentativa['token']); ?>">
                        </form>
                        <section class="acoes-quiz">
                            <button type="submit" form="quiz-respostas">Finalizar quiz</button>
                            <a class="botao secundario" href="../view/desempenho.php">Meus resultados</a>
                        </section>
                    </div>
                    <form class="form-reiniciar" method="post" action="../processamento/processamento.php">
                        <input type="hidden" name="acao" value="reiniciarQuiz"><input type="hidden" name="csrf" value="<?php echo htmlspecialchars($estadoTentativa['token']); ?>">
                        <button type="submit">Iniciar nova tentativa</button>
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
