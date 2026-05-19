<?php

require_once("../model/BancoDeDados.php");

class Controlador{

    //Atributo
    private $bancoDeDados;

    function __construct(){
        $this->bancoDeDados = new BancoDeDados("localhost","root","","mindnode");
    }

    public function efetuarLogin($email, $senha) {
        $dadosUsuario = $this->bancoDeDados->autenticarUsuario($email, $senha);
        
        if ($dadosUsuario) {
            $_SESSION['estaLogado'] = true;
            return $dadosUsuario; 
        } else {
            $_SESSION['estaLogado'] = false;
            return false;
        }
    }

    public function cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto_perfil){
        
        $usuario = new usuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto_perfil);
        $this->bancoDeDados->inserirUsuario($usuario);
        
    }

}

?>