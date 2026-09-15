<?php

session_start();


$pagina = "inicio";

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

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Início</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/navegacao.css">
    <script src="../js/navegacao.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main>
        <section class="hero">
            <section class="hero-texto">
                <span class="etiqueta">Ambiente de Ensino de Estruturas de Dados</span>
                <?php if ($navegacao['autenticado']): ?><p class="home-saudacao">Olá, <?php echo htmlspecialchars($navegacao['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>!</p><?php endif; ?>

                <h1>Aprenda Estruturas de Dados de forma visual e interativa.</h1>

                <p>
                    Estude conceitos e operações, consulte exemplos em C# e pratique no Quiz.
                    Ganhe moedas, explore a Loja e personalize seu Avatar com itens e habilidades.
                </p>

                <section class="acoes">
                    <a class="botao primario" href="../view/estruturas.php">Começar estudos</a>
                    <a class="botao secundario" href="../view/simulador.php">Abrir simulador</a>
                </section>
            </section>

            <section class="hero-visual" aria-label="Representação visual de nós conectados">
                <span class="codigo-flutuante">estrutura de nós</span>

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

        <section class="resumo-plataforma">
            <article>
                <strong>6</strong>
                <span>estruturas principais</span>
            </article>

            <article>
                <strong>C#</strong>
                <span>exemplos comentados</span>
            </article>

            <article>
                <strong>Visual</strong>
                <span>aprendizado por conexões</span>
            </article>

            <article>
                <strong>Quiz</strong>
                <span>fixação do conteúdo</span>
            </article>
        </section>

        <section class="secao-conteudo">
            <section class="cabecalho-secao">
                <span class="subtitulo-secao">Conteúdos principais</span>
                <h2>Escolha por onde continuar seus estudos</h2>
                <p>
                    A plataforma foi organizada para separar teoria, prática e revisão,
                    deixando cada etapa do aprendizado mais fácil de acompanhar.
                </p>
            </section>

            <section class="cards-principais">
                <article class="card card-branco"><h3>Conteúdos</h3><p>Aprenda os conceitos e operações das principais estruturas estudadas.</p><a href="estruturas.php">Estudar estruturas →</a></article>
                <article class="card card-verde"><h3>Exemplos em C#</h3><p>Veja implementações práticas das estruturas ensinadas.</p><a href="exemplos.php">Ver exemplos →</a></article>
                <article class="card card-branco"><h3>Quiz</h3><p>Teste seus conhecimentos com questões teóricas e de código.</p><a href="<?php echo $navegacao['autenticado'] ? 'quiz.php' : 'login.php'; ?>">Praticar →</a></article>
                <article class="card card-branco"><h3>Moedas e personalização</h3><p>Ganhe moedas respondendo ao Quiz, use-as na Loja e personalize seu Avatar. Equipe itens com habilidades para usar durante o Quiz.</p><a href="<?php echo $navegacao['autenticado'] ? 'avatar.php' : 'cadastrar_usuario.php'; ?>">Conhecer a personalização →</a></article>
            </section>
        </section>
        <section class="home-atalhos" aria-labelledby="home-estruturas">
            <h2 id="home-estruturas">O que você pode aprender</h2>
            <div><?php foreach ($navegacao['estruturas'] as $nome => $destino): ?><a href="<?php echo $destino; ?>"><?php echo $nome; ?></a><?php endforeach; ?></div>
        </section>
        <?php if ($navegacao['autenticado']): ?>
        <section class="home-atalhos" aria-labelledby="home-continuar"><h2 id="home-continuar">Continuar no MindNodes</h2><div><a href="quiz.php">Quiz</a><a href="avatar.php">Meu Avatar</a><a href="loja.php">Loja</a><a href="desempenho.php">Meu Desempenho</a></div></section>
        <?php endif; ?>

        <section class="bloco-aprendizado">
            <section class="bloco-texto">
                <span class="subtitulo-secao">Como estudar</span>
                <h2>Um caminho mais simples para aprender sem se perder.</h2>
                <p>
                    A ideia do MindNodes é organizar o conteúdo em etapas. Primeiro você entende o conceito,
                    depois visualiza como a estrutura funciona, analisa exemplos em código e por fim testa
                    seus conhecimentos.
                </p>

                <section class="lista-etapas">
                    <article>
                        <strong>01</strong>
                        <div>
                            <h4>Leia a explicação</h4>
                            <p>Comece pela teoria apresentada de forma direta.</p>
                        </div>
                    </article>

                    <article>
                        <strong>02</strong>
                        <div>
                            <h4>Observe a estrutura</h4>
                            <p>Use os recursos visuais para entender as ligações entre os nós.</p>
                        </div>
                    </article>

                    <article>
                        <strong>03</strong>
                        <div>
                            <h4>Pratique com exemplos</h4>
                            <p>Veja códigos em C# e teste seu entendimento no quiz.</p>
                        </div>
                    </article>
                </section>
            </section>

            <section class="painel-estudo">
                <h3>Trilha recomendada</h3>

                <div class="linha-trilha">
                    <span></span>
                    <p>TAD e operações básicas</p>
                </div>

                <div class="linha-trilha">
                    <span></span>
                    <p>Lista simplesmente encadeada</p>
                </div>

                <div class="linha-trilha">
                    <span></span>
                    <p>Lista duplamente encadeada</p>
                </div>

                <div class="linha-trilha">
                    <span></span>
                    <p>Exemplos práticos em C#</p>
                </div>

                <a href="../view/estruturas.php" class="botao-trilha">Ver trilha completa</a>
            </section>
        </section>

        <section class="secao-recursos">
            <section class="cabecalho-secao">
                <span class="subtitulo-secao">Recursos da plataforma</span>
                <h2>Ferramentas para deixar o conteúdo mais claro</h2>
            </section>

            <section class="recursos">
                <article>
                    <span>📘</span>
                    <h3>Textos explicativos</h3>
                    <p>Conteúdos organizados para facilitar a compreensão dos conceitos.</p>
                </article>

                <article>
                    <span>🖼️</span>
                    <h3>Imagens e gifs</h3>
                    <p>Representações visuais para acompanhar o funcionamento das estruturas.</p>
                </article>

                <article>
                    <span>▶️</span>
                    <h3>Vídeos</h3>
                    <p>Aulas e demonstrações para reforçar o aprendizado.</p>
                </article>

                <article>
                    <span>C#</span>
                    <h3>Exemplos em C#</h3>
                    <p>Códigos comentados mostrando a teoria aplicada na prática.</p>
                </article>
            </section>
        </section>

        <section class="chamada-final">
            <section>
                <span class="subtitulo-secao">Próximo passo</span>
                <h2>Comece pela estrutura mais importante para sua revisão.</h2>
                <p>
                    Acesse a área de estruturas e siga a trilha recomendada para estudar com mais organização.
                </p>
            </section>

            <a class="botao primario" href="../view/estruturas.php">Ir para estruturas</a>
        </section>
    </main>

    <div id="toast" class="toast-notificacao">
        <div class="toast-icon">✔</div>

        <div class="toast-texto">
            <h4>Bem-vindo, <?php echo htmlspecialchars($nomeUsuario); ?>!</h4>
            <p>Login realizado com sucesso.</p>
        </div>
    </div>

    <script>
        window.addEventListener("load", function () {
            <?php if (isset($_SESSION['login_sucesso'])): ?>
                const toast = document.getElementById("toast");

                if (toast) {
                    toast.classList.add("mostrar");

                    setTimeout(() => {
                        toast.classList.remove("mostrar");
                    }, 4000);
                }

                <?php unset($_SESSION['login_sucesso']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
