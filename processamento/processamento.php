<?php

session_start();
require_once("../model/Usuario.php");
require_once("../controller/controlador.php");

$controlador = new Controlador();

//Login
if(isset($_POST['inputEmailLog']) && isset($_POST['inputSenhaLog'])){
    $email = $_POST['inputEmailLog'];
    $senha = $_POST['inputSenhaLog'];

    $sucesso = $controlador->efetuarLogin($email, $senha);

    if($sucesso){
        header('Location:../view/home.php');
    } else {
        header('Location:../view/index.php?erro=1');
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
    $foto_perfil = $_POST['inputFoto'];
    
    $controlador->cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto_perfil);

    header('Location:../view/cadastro_usuario.php');
    die();
}


?>