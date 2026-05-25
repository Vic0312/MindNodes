<?php

session_start();

require_once("../model/Usuario.php");
require_once("../controller/controlador.php");

$controlador = new Controlador();

if (isset($_POST['inputEmailLog']) && isset($_POST['inputSenhaLog'])) {
    $email = trim($_POST['inputEmailLog']);
    $senha = trim($_POST['inputSenhaLog']);

    $usuario = $controlador->efetuarLogin($email, $senha);

    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id_usuario'] ?? $usuario['id'] ?? null;
        $_SESSION['usuario_nome'] = $usuario['nome'] ?? 'Usuário';
        $_SESSION['usuario_sobrenome'] = $usuario['sobrenome'] ?? '';
        $_SESSION['usuario_email'] = $usuario['email'] ?? $email;
        $_SESSION['usuario_foto'] = $usuario['foto_perfil'] ?? null;
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
            $pastaFisica = "../uploads/usuarios/";

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

    $controlador->cadastrarUsuario(
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
