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

    public function editarPerfilUsuario($id_usuario, $nome, $sobrenome, $email, $telefone, $senha, $foto_perfil) {
        return $this->bancoDeDados->editarPerfilUsuario(
            $id_usuario,
            $nome,
            $sobrenome,
            $email,
            $telefone,
            $senha,
            $foto_perfil
        );
    }

    public function listarAssuntosQuiz() {
        return $this->bancoDeDados->listarAssuntosQuiz();
    }

    public function buscarAssuntoQuiz($slug) {
        return $this->bancoDeDados->buscarAssuntoQuiz($slug);
    }

    public function buscarPerguntasQuiz($slug) {
        return $this->bancoDeDados->buscarPerguntasQuiz($slug);
    }

    public function salvarTentativaQuiz($idUsuario, $slug, $respostas) {
        return $this->bancoDeDados->salvarTentativaQuiz($idUsuario, $slug, $respostas);
    }

    public function buscarDesempenhoUsuario($idUsuario) {
        return $this->bancoDeDados->buscarDesempenhoUsuario($idUsuario);
    }

    public function buscarTentativaQuiz($idTentativa, $idUsuario) {
        return $this->bancoDeDados->buscarTentativaQuiz($idTentativa, $idUsuario);
    }

}

?>
