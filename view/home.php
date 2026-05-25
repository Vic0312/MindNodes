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
</head>
<body>
    <header class="topo">
        <a class="marca" href="../index.php" aria-label="MindNodes">
            <span class="simbolo-logo">∞</span>
            <strong>Mind<span>Nodes</span></strong>
        </a>

        <nav class="menu">
            <a class="ativo" href="../view/home.php">Início</a>
            <a href="../view/estruturas.php">Estruturas</a>
            <a href="../view/exemplos.php">Exemplos em C#</a>
            <a href="../view/simulador.php">Simulador</a>
            <a href="../view/quiz.php">Quiz</a>
            <a href="../view/sobre.php">Sobre</a>
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
        <section class="hero">
            <section class="hero-texto">
                <span class="etiqueta">Ambiente de Ensino de Estruturas de Dados</span>

                <h1>Aprenda estruturas de dados de um jeito visual e conectado.</h1>

                <p>
                    Estude TAD, listas encadeadas e conceitos essenciais com explicações organizadas,
                    exemplos em C#, recursos visuais e atividades práticas para reforçar o aprendizado.
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
                <strong>3</strong>
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
                <article class="card card-branco">
                    <span class="icone-card">⬡</span>
                    <h3>TAD</h3>
                    <p>
                        Entenda o conceito de Tipo Abstrato de Dados, suas operações e sua importância
                        para organizar melhor a lógica dos programas.
                    </p>
                    <a href="../view/estruturas.php#tad">Explorar conteúdo →</a>
                </article>

                <article class="card card-verde">
                    <span class="mini-nos">10 → 20 → 30</span>
                    <h3>Lista Simplesmente Encadeada</h3>
                    <p>
                        Aprenda como cada nó armazena um dado e aponta para o próximo elemento da sequência.
                    </p>
                    <a href="../view/estruturas.php#lista-simples">Ver estrutura →</a>
                </article>

                <article class="card card-branco">
                    <span class="mini-nos">10 ⇄ 20 ⇄ 30</span>
                    <h3>Lista Duplamente Encadeada</h3>
                    <p>
                        Veja como a navegação em duas direções facilita remoções, inserções e percursos.
                    </p>
                    <a href="../view/estruturas.php#lista-dupla">Estudar agora →</a>
                </article>
            </section>
        </section>

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
