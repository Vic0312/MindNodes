<?php
require_once("Usuario.php");

class BancoDeDados{

    private $host; //IP ou localhost
    private $login;
    private $senha;
    private $dataBase;

    public function __construct($Host, $Login, $Senha, $DataBase){
        $this->host = $Host;
        $this->login = $Login;
        $this->senha = $Senha;
        $this->dataBase = $DataBase;
    }

    //Métodos
    public function conectarBD(){

        $conexao = mysqli_connect($this->host,$this->login,$this->senha,$this->dataBase);
        return($conexao);
    }

    public function autenticarUsuario($email, $senha) {
        $conexao = $this->conectarBD();
        
        $email = mysqli_real_escape_string($conexao, $email);
        $senha = mysqli_real_escape_string($conexao, $senha);

        $consulta = "SELECT * FROM usuario WHERE email = '$email' AND senha = '$senha'";
        $resultado = mysqli_query($conexao, $consulta);

        if (mysqli_num_rows($resultado) == 1) {
            return mysqli_fetch_assoc($resultado); 
        } else {
            return false;
        }
    }
    
    public function inserirUsuario($usuario){
        
        $conexao = $this->conectarBD();
        $consulta = "INSERT INTO usuario (cpf, nome, sobrenome, dataNasc, telefone, email, senha, foto_perfil) 
                     VALUES ('{$usuario->get_Cpf()}',
                             '{$usuario->get_Nome()}',
                             '{$usuario->get_Sobrenome()}',
                             '{$usuario->get_DataNasc()}',
                             '{$usuario->get_Telefone()}',
                             '{$usuario->get_Email()}',
                             '{$usuario->get_Senha()}',
                             '{$usuario->get_Foto()}')";
        mysqli_query($conexao,$consulta);
    }

}

?>