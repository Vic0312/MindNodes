<?php

require_once("../model/BancoDeDados.php");

class Controlador{

    //Atributo
    private $bancoDeDados;

    function __construct(){
        $this->bancoDeDados = new BancoDeDados("localhost","root","","xhoppi");
    }

    public function efetuarLogin($email, $senha) {
        $dadosFuncionario = $this->bancoDeDados->autenticarUsuario($email, $senha);
        
        if ($dadosFuncionario) {
            $_SESSION['estaLogado'] = true;
            $_SESSION['usuario_id'] = $dadosFuncionario['cpf'];
            $_SESSION['usuario_nome'] = $dadosFuncionario['nome'];
            return true;
        } else {
            $_SESSION['estaLogado'] = false;
            return false;
        }
    }

    public function cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto_perfil){
        
        $cliente = new Cliente($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto_perfil);
        $this->bancoDeDados->inserirUsuario($usuario);
        
    }

}

?>