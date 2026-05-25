<?php

session_start();

$pagina = "sobre";

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
    <title>MindNodes | Sobre</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sobre.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="../index.php" aria-label="MindNodes">
            <span class="simbolo-logo">∞</span>
            <strong>Mind<span>Nodes</span></strong>
        </a>

        <nav class="menu">
            <a href="../view/home.php">Início</a>
            <a class="ativo" href="../view/sobre.php">Sobre</a>
            <a href="../view/estruturas.php">Estruturas</a>
            <a href="../view/exemplos.php">Exemplos em C#</a>
            <a href="../view/simulador.php">Simulador</a>
            <a href="../view/quiz.php">Quiz</a>
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
        <section class="sobre-hero">
            <section class="sobre-hero-conteudo">
                <span class="etiqueta">Sobre o MindNodes</span>

                <h1>Um espaço para aprender programação com mais clareza, prática e conexão.</h1>

                <p>
                    O MindNodes é um site educacional criado para auxiliar estudantes no aprendizado
                    de conteúdos de programação, especialmente estruturas de dados. A proposta é apresentar
                    temas como TAD, listas simplesmente encadeadas e listas duplamente encadeadas de uma forma
                    mais organizada, visual e próxima da prática.
                </p>
            </section>

        </section>

        <section class="sobre-introducao">
            <section class="texto-principal">
                <span class="subtitulo-secao">Objetivo do site</span>

                <h2>Promover o ensino de programação de forma acessível.</h2>

                <p>
                    O objetivo do MindNodes é ajudar no entendimento de diferentes assuntos da programação,
                    oferecendo explicações, exemplos e recursos que tornam o estudo menos confuso. O projeto
                    busca apoiar quem está começando ou revisando conteúdos importantes, principalmente aqueles
                    ligados à organização de dados e construção de algoritmos.
                </p>

                <p>
                    Em vez de apresentar apenas textos longos ou códigos isolados, o site valoriza uma abordagem
                    mais visual e estruturada, facilitando a compreensão de como cada conceito funciona e como
                    ele pode ser aplicado no desenvolvimento.
                </p>
            </section>

            <section class="quadro-objetivos">
                <article>
                    <strong>01</strong>
                    <div>
                        <h3>Explicar conceitos</h3>
                        <p>Apresentar os conteúdos com uma linguagem mais clara e objetiva.</p>
                    </div>
                </article>

                <article>
                    <strong>02</strong>
                    <div>
                        <h3>Mostrar aplicações</h3>
                        <p>Relacionar teoria, exemplos em código e situações práticas de programação.</p>
                    </div>
                </article>

                <article>
                    <strong>03</strong>
                    <div>
                        <h3>Apoiar o estudo</h3>
                        <p>Organizar os conteúdos para facilitar revisão, prática e fixação.</p>
                    </div>
                </article>
            </section>
        </section>

        <section class="conteudos-sobre">
            <section class="cabecalho-secao centralizado">
                <span class="subtitulo-secao">Conteúdos trabalhados</span>
                <h2>Assuntos que fazem parte da proposta</h2>
                <p>
                    A plataforma reúne conteúdos fundamentais para o aprendizado de estruturas de dados
                    e programação, com foco em compreensão gradual e prática.
                </p>
            </section>

            <section class="grade-conteudos">
                <article>
                    <span>TAD</span>
                    <h3>Tipos Abstratos de Dados</h3>
                    <p>
                        Estudo da organização de dados por meio de operações, regras e comportamentos.
                    </p>
                </article>

                <article>
                    <span>LS</span>
                    <h3>Lista Simplesmente Encadeada</h3>
                    <p>
                        Estrutura em que cada nó aponta para o próximo elemento da sequência.
                    </p>
                </article>

                <article>
                    <span>LD</span>
                    <h3>Lista Duplamente Encadeada</h3>
                    <p>
                        Estrutura em que os nós possuem ligação com o elemento anterior e o próximo.
                    </p>
                </article>

                <article>
                    <span>C#</span>
                    <h3>Exemplos em Programação</h3>
                    <p>
                        Demonstrações em código para visualizar como a teoria aparece na prática.
                    </p>
                </article>
            </section>
        </section>

        <section class="secao-desenvolvedores">
            <section class="cabecalho-secao">
                <span class="subtitulo-secao">Desenvolvedores</span>
                <h2>Quem está por trás do projeto</h2>
                <p>
                    O MindNodes foi desenvolvido por três estudantes com o propósito de construir uma
                    experiência de estudo mais organizada, visual e útil para o aprendizado de programação.
                </p>
            </section>

            <section class="grade-devs">
                <article class="dev-card">
                    <div class="avatar-dev">MV</div>
                    <h3>Maria Vitória</h3>
                    <p>
                        Responsável por contribuir na construção do projeto, organização das telas
                        e desenvolvimento da proposta visual e educacional da plataforma.
                    </p>
                </article>

                <article class="dev-card destaque-dev">
                    <div class="avatar-dev">M</div>
                    <h3>Mariana</h3>
                    <p>
                        Responsável por colaborar com a estruturação do site, disposição dos conteúdos
                        e construção da experiência de aprendizagem.
                    </p>
                </article>

                <article class="dev-card">
                    <div class="avatar-dev">G</div>
                    <h3>Guilherme</h3>
                    <p>
                        Responsável por contribuir no desenvolvimento do projeto, apoio técnico e
                        organização das funcionalidades da plataforma.
                    </p>
                </article>
            </section>
        </section>

        <section class="identidade-projeto">
            <section class="identidade-card escuro">
                <span class="subtitulo-secao">Nossa ideia</span>
                <h2>Transformar conteúdos complexos em uma trilha mais fácil de acompanhar.</h2>
                <p>
                    A programação envolve muitos conceitos que se conectam. Por isso, o MindNodes foi pensado
                    como um ambiente em que o aluno consegue visualizar relações, revisar conteúdos e avançar
                    com mais segurança.
                </p>
            </section>

            <section class="identidade-lista">
                <article>
                    <span>✓</span>
                    <p>Conteúdos organizados por temas.</p>
                </article>

                <article>
                    <span>✓</span>
                    <p>Explicações com apoio visual.</p>
                </article>

                <article>
                    <span>✓</span>
                    <p>Exemplos pensados para estudantes.</p>
                </article>

                <article>
                    <span>✓</span>
                    <p>Ambiente preparado para evolução futura.</p>
                </article>
            </section>
        </section>

    </main>
</body>
</html>
