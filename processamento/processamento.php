<?php

session_start();

require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/UsuarioController.php';
require_once __DIR__ . '/../controller/QuizController.php';

if (($_POST['acao'] ?? null) === 'comprarItem') {
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['estaLogado'])) {
        header('Location: ../view/login.php');
        exit();
    }
    if (!is_string($_POST['csrf'] ?? null) || !isset($_SESSION['loja_csrf'])
        || !hash_equals($_SESSION['loja_csrf'], $_POST['csrf'])) {
        $_SESSION['loja_mensagem'] = ['tipo' => 'erro', 'texto' => 'Sessão expirada. Atualize a página e tente novamente.'];
    } else {
        require_once __DIR__ . '/../controller/LojaController.php';
        try {
            $texto = (new LojaController())->comprar($_POST['id_item'] ?? null);
            $_SESSION['loja_mensagem'] = ['tipo' => 'sucesso', 'texto' => $texto];
        } catch (ItemJaPossuidoException $erro) {
            $_SESSION['loja_mensagem'] = ['tipo' => 'erro', 'texto' => 'Você já possui este item.'];
        } catch (ItemIndisponivelException | OutOfBoundsException | InvalidArgumentException $erro) {
            $_SESSION['loja_mensagem'] = ['tipo' => 'erro', 'texto' => 'Item indisponível.'];
        } catch (DomainException $erro) {
            $_SESSION['loja_mensagem'] = ['tipo' => 'erro', 'texto' => 'Saldo insuficiente.'];
        } catch (Throwable $erro) {
            error_log('Falha na compra: ' . $erro->getMessage());
            $_SESSION['loja_mensagem'] = ['tipo' => 'erro', 'texto' => 'Não foi possível concluir a compra.'];
        }
    }
    header('Location: ../view/loja.php', true, 303);
    exit();
}

if (($_POST['acao'] ?? null) === 'equiparAvatar') {
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['estaLogado'])) {
        header('Location: ../view/login.php');
        exit();
    }
    if (!is_string($_POST['csrf'] ?? null) || !isset($_SESSION['avatar_csrf'])
        || !hash_equals($_SESSION['avatar_csrf'], $_POST['csrf'])) {
        $_SESSION['avatar_mensagem'] = ['tipo' => 'erro', 'texto' => 'Sessão expirada. Atualize a página e tente novamente.'];
    } else {
        require_once __DIR__ . '/../controller/AvatarController.php';
        try {
            (new AvatarController())->equiparItem($_POST['id_item'] ?? null);
            $_SESSION['avatar_mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Item equipado com sucesso.'];
        } catch (DomainException $erro) {
            $_SESSION['avatar_mensagem'] = ['tipo' => 'erro', 'texto' => 'Você não possui esse item.'];
        } catch (InvalidArgumentException | OutOfBoundsException $erro) {
            $_SESSION['avatar_mensagem'] = ['tipo' => 'erro', 'texto' => 'Item inválido ou indisponível.'];
        } catch (Throwable $erro) {
            error_log('Falha ao equipar avatar: ' . $erro->getMessage());
            $_SESSION['avatar_mensagem'] = ['tipo' => 'erro', 'texto' => 'Não foi possível alterar o Avatar.'];
        }
    }
    header('Location: ../view/avatar.php', true, 303);
    exit();
}

if (in_array($_POST['acao'] ?? null, ['recuperarSenha', 'redefinirSenha'], true)) {
    $authController = new AuthController();
    $token = $_POST['csrf'] ?? '';
    if ($_POST['acao'] === 'recuperarSenha') {
        $erro = $authController->iniciarRecuperacao($_POST['cpf'] ?? '', $_POST['email'] ?? '', $_POST['dataNascimento'] ?? '', $token);
        $destino = $erro === null ? 'redefinir_senha.php' : 'recuperar_senha.php';
    } else {
        $erro = $authController->redefinirSenha($_POST['novaSenha'] ?? '', $_POST['confirmacao'] ?? '', $token);
        $destino = $erro === null ? 'login.php' : ($erro === 'sessao' ? 'recuperar_senha.php' : 'redefinir_senha.php');
    }
    if ($erro !== null) $_SESSION['recuperacao_erro'] = $erro;
    header('Location: ../view/' . $destino);
    exit();
}

$authController = new AuthController();
$usuarioController = new UsuarioController();
$quizController = new QuizController();

if (isset($_POST['acao']) && $_POST['acao'] === 'salvarQuiz') {
    $idUsuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

    if (!$idUsuario) {
        header("Location: ../view/login.php");
        exit();
    }

    $slug = trim(isset($_POST['assunto']) ? $_POST['assunto'] : '');
    $respostas = isset($_POST['respostas']) ? $_POST['respostas'] : [];
    $idTentativa = $quizController->salvarTentativaQuiz($idUsuario, $slug, $respostas);

    if ($idTentativa) {
        header("Location: ../view/desempenho.php?tentativa=" . $idTentativa);
        exit();
    }

    header("Location: ../view/quiz.php?erro=1");
    exit();
}

if (isset($_POST['acao']) && $_POST['acao'] === 'editarPerfil') {
    $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

    if (!$id_usuario) {
        header("Location: ../view/login.php");
        exit();
    }

    $nome = trim(isset($_POST['inputNomePerfil']) ? $_POST['inputNomePerfil'] : '');
    $sobrenome = trim(isset($_POST['inputSobrenomePerfil']) ? $_POST['inputSobrenomePerfil'] : '');
    $email = trim(isset($_POST['inputEmailPerfil']) ? $_POST['inputEmailPerfil'] : '');
    $telefone = trim(isset($_POST['inputTelefonePerfil']) ? $_POST['inputTelefonePerfil'] : '');
    $senha = trim(isset($_POST['inputSenhaPerfil']) ? $_POST['inputSenhaPerfil'] : '');

    if ($nome === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../view/perfil.php?erro=campos");
        exit();
    }

    $foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : null;

    if (isset($_FILES['inputFotoPerfil']) && $_FILES['inputFotoPerfil']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['inputFotoPerfil']['error'] !== UPLOAD_ERR_OK) {
            header("Location: ../view/perfil.php?erro=upload");
            exit();
        }

        $arquivo = $_FILES['inputFotoPerfil'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (!in_array($extensao, $extensoesPermitidas)) {
            header("Location: ../view/perfil.php?erro=tipo");
            exit();
        }

        $pastaFisica = __DIR__ . "/../uploads/usuarios/";

        if (!is_dir($pastaFisica)) {
            mkdir($pastaFisica, 0777, true);
        }

        $nomeArquivo = uniqid("perfil_", true) . "." . $extensao;
        $destinoFisico = $pastaFisica . $nomeArquivo;

        if (!move_uploaded_file($arquivo['tmp_name'], $destinoFisico)) {
            header("Location: ../view/perfil.php?erro=upload");
            exit();
        }

        $foto_perfil = "uploads/usuarios/" . $nomeArquivo;
    }

    $atualizou = $usuarioController->editarPerfilUsuario(
        $id_usuario,
        $nome,
        $sobrenome,
        $email,
        $telefone,
        $senha,
        $foto_perfil
    );

    if ($atualizou) {
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_sobrenome'] = $sobrenome;
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario_telefone'] = $telefone;
        $_SESSION['usuario_foto'] = $foto_perfil;

        header("Location: ../view/perfil.php?sucesso=1");
        exit();
    }

    header("Location: ../view/perfil.php?erro=1");
    exit();
}

if (isset($_POST['inputEmailLog']) && isset($_POST['inputSenhaLog'])) {
    $email = trim($_POST['inputEmailLog']);
    $senha = trim($_POST['inputSenhaLog']);

    $usuario = $authController->efetuarLogin($email, $senha);

    if ($usuario) {
        header("Location: ../view/home.php");
        exit();
    }

    header("Location: ../view/login.php?erro=1");
    exit();
}

if (
    isset($_POST['inputNome']) &&
    isset($_POST['inputSobrenome']) &&
    isset($_POST['inputCPF']) &&
    isset($_POST['inputDataNasc']) &&
    isset($_POST['inputTelefone']) &&
    isset($_POST['inputEmail']) &&
    isset($_POST['inputSenha']) &&
    isset($_POST['inputConfirmarSenha'])
) {
    $nome = trim($_POST['inputNome']);
    $sobrenome = trim($_POST['inputSobrenome']);
    $cpf = trim($_POST['inputCPF']);
    $dataNasc = trim($_POST['inputDataNasc']);
    $telefone = trim($_POST['inputTelefone']);
    $email = trim($_POST['inputEmail']);
    $senha = trim($_POST['inputSenha']);
    $confirmacaoSenha = trim($_POST['inputConfirmarSenha']);

    $erroCadastro = $authController->validarCadastro($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $confirmacaoSenha);
    if ($erroCadastro !== null) {
        header("Location: ../view/cadastrar_usuario.php?erro=" . urlencode($erroCadastro));
        exit();
    }

    $foto_perfil = null;

    if (isset($_FILES['inputFoto']) && $_FILES['inputFoto']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['inputFoto'];

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (in_array($extensao, $extensoesPermitidas)) {
            $pastaFisica = __DIR__ . "/../uploads/usuarios/";

            if (!is_dir($pastaFisica)) {
                mkdir($pastaFisica, 0777, true);
            }

            $nomeArquivo = uniqid("perfil_", true) . "." . $extensao;
            $destinoFisico = $pastaFisica . $nomeArquivo;

            if (move_uploaded_file($arquivo['tmp_name'], $destinoFisico)) {
                $foto_perfil = "uploads/usuarios/" . $nomeArquivo;
            }
        }
    }

    $resultadoCadastro = $authController->cadastrarUsuario(
        $cpf,
        $nome,
        $sobrenome,
        $dataNasc,
        $telefone,
        $email,
        $senha,
        $foto_perfil,
        $confirmacaoSenha
    );

    if (!$resultadoCadastro['sucesso']) {
        header("Location: ../view/cadastrar_usuario.php?erro=" . urlencode($resultadoCadastro['erro']));
        exit();
    }

    header("Location: ../view/login.php?cadastro=1");
    exit();
}

header("Location: ../index.php");
exit();

?>
