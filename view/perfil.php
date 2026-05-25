<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../view/login.php");
    exit();
}

$pagina = "perfil";

$usuarioId = $_SESSION['usuario_id'];
$nomeUsuario = $_SESSION['usuario_nome'] ?? "";
$sobrenomeUsuario = $_SESSION['usuario_sobrenome'] ?? "";
$emailUsuario = $_SESSION['usuario_email'] ?? "";
$telefoneUsuario = $_SESSION['usuario_telefone'] ?? "";
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

$mensagemSucesso = $_GET['sucesso'] ?? null;
$mensagemErro = $_GET['erro'] ?? null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Meu Perfil</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/perfil.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="../index.php" aria-label="MindNodes">
            <span class="simbolo-logo">∞</span>
            <strong>Mind<span>Nodes</span></strong>
        </a>

        <nav class="menu">
            <a href="../view/home.php">Início</a>
            <a href="../view/sobre.php">Sobre</a>
            <a href="../view/estruturas.php">Estruturas</a>
            <a href="../view/exemplos.php">Exemplos em C#</a>
            <a href="../view/simulador.php">Simulador</a>
            <a href="../view/quiz.php">Quiz</a>
        </nav>

        <a class="perfil-usuario ativo-perfil" href="../view/perfil.php" title="Meu perfil">
            <img
                src="<?php echo htmlspecialchars($fotoExibicao); ?>"
                alt="Foto de perfil de <?php echo htmlspecialchars($nomeUsuario); ?>"
                class="foto-perfil-nav"
            >
        </a>
    </header>

    <main>

        <?php if ($mensagemSucesso): ?>
            <section class="alerta sucesso">
                Perfil atualizado com sucesso.
            </section>
        <?php endif; ?>

        <?php if ($mensagemErro): ?>
            <section class="alerta erro">
                Não foi possível atualizar o perfil. Verifique os dados e tente novamente.
            </section>
        <?php endif; ?>

        <section class="perfil-layout">
            <section class="card-foto">
                <span class="subtitulo-secao">Foto de perfil</span>

                <h2>Imagem da conta</h2>

                <p>
                    Escolha uma imagem clara.
                </p>

                <div class="foto-grande">
                    <img
                        id="previewFotoLateral"
                        src="<?php echo htmlspecialchars($fotoExibicao); ?>"
                        alt="Foto atual do usuário"
                    >
                </div>

                <p class="dica-foto">
                    Formatos aceitos: JPG, JPEG, PNG, WEBP ou AVIF.
                </p>
            </section>

            <section class="card-formulario">
                <span class="subtitulo-secao">Informações pessoais</span>

                <h2>Dados do usuário</h2>

                <form method="POST" action="../processamento/processamento.php" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="editarPerfil">
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuarioId); ?>">

                    <section class="campo-upload">
                        <label for="inputFotoPerfil">Alterar foto</label>

                        <div class="upload-box">
                            <span>Selecionar nova imagem</span>
                            <input
                                type="file"
                                id="inputFotoPerfil"
                                name="inputFotoPerfil"
                                accept="image/*"
                            >
                        </div>
                    </section>

                    <section class="grid-inputs">
                        <section class="input-box">
                            <label for="inputNomePerfil">Nome</label>
                            <input
                                type="text"
                                id="inputNomePerfil"
                                name="inputNomePerfil"
                                value="<?php echo htmlspecialchars($nomeUsuario); ?>"
                                required
                            >
                        </section>

                        <section class="input-box">
                            <label for="inputSobrenomePerfil">Sobrenome</label>
                            <input
                                type="text"
                                id="inputSobrenomePerfil"
                                name="inputSobrenomePerfil"
                                value="<?php echo htmlspecialchars($sobrenomeUsuario); ?>"
                            >
                        </section>
                    </section>

                    <section class="grid-inputs">
                        <section class="input-box">
                            <label for="inputEmailPerfil">E-mail</label>
                            <input
                                type="email"
                                id="inputEmailPerfil"
                                name="inputEmailPerfil"
                                value="<?php echo htmlspecialchars($emailUsuario); ?>"
                                required
                            >
                        </section>

                        <section class="input-box">
                            <label for="inputTelefonePerfil">Telefone</label>
                            <input
                                type="text"
                                id="inputTelefonePerfil"
                                name="inputTelefonePerfil"
                                value="<?php echo htmlspecialchars($telefoneUsuario); ?>"
                                placeholder="(00) 00000-0000"
                            >
                        </section>
                    </section>

                    <section class="input-box">
                        <label for="inputSenhaPerfil">Nova senha</label>
                        <input
                            type="password"
                            id="inputSenhaPerfil"
                            name="inputSenhaPerfil"
                            placeholder="Preencha somente se quiser alterar"
                        >
                    </section>

                    <section class="acoes-form">
                        <a class="botao cancelar" href="../view/home.php">Cancelar</a>
                        <button type="submit" class="botao primario">Salvar alterações</button>
                    </section>
                </form>
            </section>
        </section>
    </main>

    <script>
        const inputFoto = document.getElementById("inputFotoPerfil");
        const previewFoto = document.getElementById("previewFoto");
        const previewFotoLateral = document.getElementById("previewFotoLateral");

        if (inputFoto) {
            inputFoto.addEventListener("change", function () {
                const arquivo = this.files[0];

                if (arquivo) {
                    const leitor = new FileReader();

                    leitor.onload = function (evento) {
                        previewFoto.src = evento.target.result;
                        previewFotoLateral.src = evento.target.result;
                    };

                    leitor.readAsDataURL(arquivo);
                }
            });
        }
    </script>
</body>
</html>
