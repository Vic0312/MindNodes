<?php

session_start();
require_once("../model/Usuario.php");
require_once("../controller/controlador.php");

$controlador = new Controlador();

//Login
// No processamento.php, altere o bloco do Login para:
if(isset($_POST['inputEmailLog']) && isset($_POST['inputSenhaLog'])){
    $email = $_POST['inputEmailLog'];
    $senha = $_POST['inputSenhaLog'];

    // Aqui está o segredo: a variável deve receber os DADOS do usuário
    $usuario = $controlador->efetuarLogin($email, $senha); 

    if($usuario){ // Se o login retornar os dados (sucesso)
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_foto'] = $usuario['foto_perfil']; 
        $_SESSION['login_sucesso'] = true; 
        
        header('Location:../view/home.php');
    } else {
        header('Location:../view/login.php?erro=1');
    }
    die();
}

//Cadastro de Usuario
if(isset($_POST['inputNome']) && isset($_POST['inputSobrenome']) && 
   isset($_POST['inputCPF']) && isset($_POST['inputDataNasc']) && 
   isset($_POST['inputTelefone']) && isset($_POST['inputEmail']) &&
   isset($_POST['inputSenha'])){

    $nome = $_POST['inputNome'];
    $sobrenome = $_POST['inputSobrenome'];
    $cpf = $_POST['inputCPF'];
    $dataNasc = $_POST['inputDataNasc'];
    $telefone = $_POST['inputTelefone'];
    $email = $_POST['inputEmail'];
    $senha = $_POST['inputSenha'];
    $foto_perfil = (isset($_FILES['inputFoto']) && $_FILES['inputFoto']['error'] == 0) ? $_FILES['inputFoto']['name'] : null;
    
    $controlador->cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto_perfil);

    header('Location:../view/login.php');
    die();
}


?>