<?php

session_start();

require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/UsuarioController.php';
require_once __DIR__ . '/../controller/QuizController.php';

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
        $_SESSION['usuario_id'] = isset($usuario['id_usuario']) ? $usuario['id_usuario'] : (isset($usuario['id']) ? $usuario['id'] : null);
        $_SESSION['usuario_nome'] = isset($usuario['nome']) ? $usuario['nome'] : 'Usuario';
        $_SESSION['usuario_sobrenome'] = isset($usuario['sobrenome']) ? $usuario['sobrenome'] : '';
        $_SESSION['usuario_email'] = isset($usuario['email']) ? $usuario['email'] : $email;
        $_SESSION['usuario_telefone'] = isset($usuario['telefone']) ? $usuario['telefone'] : '';
        $_SESSION['usuario_foto'] = isset($usuario['foto_perfil']) ? $usuario['foto_perfil'] : null;
        $_SESSION['login_sucesso'] = true;

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
    isset($_POST['inputSenha'])
) {
    $nome = trim($_POST['inputNome']);
    $sobrenome = trim($_POST['inputSobrenome']);
    $cpf = trim($_POST['inputCPF']);
    $dataNasc = trim($_POST['inputDataNasc']);
    $telefone = trim($_POST['inputTelefone']);
    $email = trim($_POST['inputEmail']);
    $senha = trim($_POST['inputSenha']);

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

    $authController->cadastrarUsuario(
        $cpf,
        $nome,
        $sobrenome,
        $dataNasc,
        $telefone,
        $email,
        $senha,
        $foto_perfil
    );

    header("Location: ../view/login.php");
    exit();
}

header("Location: ../index.php");
exit();

?>
