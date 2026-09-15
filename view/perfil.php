<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../view/login.php");
    exit();
}

$pagina = "perfil";

$usuarioId = $_SESSION['usuario_id'];
require_once __DIR__ . '/../controller/UsuarioController.php';
$usuarioController = new UsuarioController();
$dadosUsuario = $usuarioController->buscarPerfil($usuarioId);

$nomeUsuario = $dadosUsuario['nome'] ?? ($_SESSION['usuario_nome'] ?? "");
$sobrenomeUsuario = $dadosUsuario['sobrenome'] ?? ($_SESSION['usuario_sobrenome'] ?? "");
$emailUsuario = $dadosUsuario['email'] ?? ($_SESSION['usuario_email'] ?? "");
$telefoneUsuario = $dadosUsuario['telefone'] ?? ($_SESSION['usuario_telefone'] ?? "");
$fotoBanco = $dadosUsuario['foto_perfil'] ?? ($_SESSION['usuario_foto'] ?? null);

$fotoExibicao = "../img/default-img.avif";

if (!empty($fotoBanco)) {
    $fotoBanco = trim($fotoBanco);

    // Compatível com versões antigas do PHP, sem depender de str_starts_with().
    if (substr($fotoBanco, 0, 10) === "data:image") {
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
    <link rel="stylesheet" href="../css/navegacao.css">
    <script src="../js/navegacao.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main>
        <section class="perfil-resumo" aria-labelledby="perfil-titulo">
            <h1 id="perfil-titulo">Meu Perfil</h1>
            <p><?php echo htmlspecialchars(trim($nomeUsuario . ' ' . $sobrenomeUsuario), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p><?php echo htmlspecialchars($emailUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p class="perfil-saldo">Moedas disponíveis: <strong data-saldo-perfil><?php echo $navegacao['saldo'] === null ? 'Indisponível' : (int) $navegacao['saldo']; ?></strong></p>
            <div class="perfil-atalhos"><a href="avatar.php">Personalizar Avatar</a><a href="loja.php">Ir para Loja</a><a href="desempenho.php">Ver meu desempenho</a></div>
        </section>
        <?php if ($mensagemSucesso): ?>
            <section class="alerta sucesso" role="status">
                Perfil atualizado com sucesso.
            </section>
        <?php endif; ?>

        <?php if ($mensagemErro): ?>
            <section class="alerta erro" role="alert">
                <?php
                    if ($mensagemErro === "tipo") {
                        echo "Formato de imagem inválido. Use JPG, JPEG, PNG, WEBP ou AVIF.";
                    } elseif ($mensagemErro === "upload") {
                        echo "Não foi possível enviar a imagem. Tente novamente.";
                    } elseif ($mensagemErro === "campos") {
                        echo "Preencha nome e e-mail corretamente.";
                    } else {
                        echo "Não foi possível atualizar o perfil. Verifique os dados e tente novamente.";
                    }
                ?>
            </section>
        <?php endif; ?>

        <section class="perfil-layout">
            <section class="card-foto">
                <span class="subtitulo-secao">Foto de perfil</span>

                <h2>Imagem da conta</h2>

                <a class="botao primario" href="avatar.php">Personalizar Avatar</a>

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

                    <section class="campo-upload">
                        <label for="inputFotoPerfil">Alterar foto</label>

                        <div class="upload-box">
                            <span id="nomeArquivoPerfil">Selecionar nova imagem</span>
                            <input
                                type="file"
                                id="inputFotoPerfil"
                                name="inputFotoPerfil"
                                accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif"
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
                            minlength="8"
                            placeholder="Preencha somente se quiser alterar"
                        >
                    </section>

                    <section class="acoes-form">
                        <a class="botao cancelar" href="../view/home.php">Cancelar</a>
                        <a class="botao cancelar" href="../processamento/logout.php">Sair</a>
                        <button type="submit" class="botao primario">Salvar alterações</button>
                    </section>
                </form>
            </section>
        </section>
    </main>

    <script>
        const inputFoto = document.getElementById("inputFotoPerfil");
        const previewFotoNav = document.getElementById("previewFotoNav");
        const previewFotoLateral = document.getElementById("previewFotoLateral");
        const nomeArquivoPerfil = document.getElementById("nomeArquivoPerfil");

        if (inputFoto) {
            inputFoto.addEventListener("change", function () {
                const arquivo = this.files[0];

                if (!arquivo) {
                    return;
                }

                if (nomeArquivoPerfil) {
                    nomeArquivoPerfil.textContent = arquivo.name;
                }

                const leitor = new FileReader();

                leitor.onload = function (evento) {
                    if (previewFotoNav) {
                        previewFotoNav.src = evento.target.result;
                    }

                    if (previewFotoLateral) {
                        previewFotoLateral.src = evento.target.result;
                    }
                };

                leitor.readAsDataURL(arquivo);
            });
        }
    </script>
</body>
</html>
