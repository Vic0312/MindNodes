<?php

require_once __DIR__ . '/../config/Conexao.php';

class Usuario{

    //Atributos
    protected $cpf;
    protected $nome;
    protected $sobrenome;
    protected $dataNasc;
    protected $telefone;
    protected $email;
    protected $senha;
    protected $foto_perfil;
    private $conexao;

    //Construtor
    public function __construct($cpf = null, $nome = null, $sobrenome = null, $dataNasc = null, $telefone = null, $email = null, $senha = null, $foto_perfil = null, $conexao = null){
        $this->cpf = $cpf;
        $this->nome = $nome;
        $this->sobrenome = $sobrenome;
        $this->dataNasc = $dataNasc;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->senha = $senha;
        $this->foto_perfil = $foto_perfil;
        $this->conexao = $conexao ?: Conexao::obter();
    }

    //Getter e Setter
    public function get_Cpf(){
        return($this->cpf);
    }

    public function set_Cpf($cpf){
        $this->cpf = $cpf;
    }

    public function get_Nome(){
        return($this->nome);
    }

    public function set_Nome($nome){
        $this->nome = $nome;
    }

    public function get_Sobrenome(){
        return($this->sobrenome);
    }

    public function set_Sobrenome($sobrenome){
        $this->sobrenome = $sobrenome;
    }

    public function get_DataNasc(){
        return($this->dataNasc);
    }

    public function set_DataNasc($dataNasc){
        $this->dataNasc = $dataNasc;
    }

    public function get_Telefone(){
        return($this->telefone);
    }

    public function set_Telefone($telefone){
        $this->telefone = $telefone;
    }

    public function get_Email(){
        return($this->email);
    }

    public function set_Email($email){
        $this->email = $email;
    }

    public function get_Senha(){
        return($this->senha);
    }

    public function set_Senha($senha){
        $this->senha = $senha;
    }

    public function get_Foto(){
        return($this->foto_perfil);
    }

    public function set_Foto($foto_perfil){
        $this->foto_perfil = $foto_perfil;
    }

    public function buscarPorEmailESenha($email, $senha){
        $consulta = mysqli_prepare($this->conexao, 'SELECT * FROM usuario WHERE email = ? AND senha = ?');
        mysqli_stmt_bind_param($consulta, 'ss', $email, $senha);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        return $resultado && mysqli_num_rows($resultado) === 1 ? mysqli_fetch_assoc($resultado) : false;
    }

    public function buscarPorEmail($email){
        $consulta = mysqli_prepare($this->conexao, 'SELECT * FROM usuario WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($consulta, 's', $email);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        return $resultado && mysqli_num_rows($resultado) === 1 ? mysqli_fetch_assoc($resultado) : false;
    }

    public function buscarPerfil($idUsuario){
        $consulta = mysqli_prepare($this->conexao, 'SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1');
        mysqli_stmt_bind_param($consulta, 'i', $idUsuario);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        return $resultado && mysqli_num_rows($resultado) === 1 ? mysqli_fetch_assoc($resultado) : false;
    }

    public function cadastrar(){
        $sql = 'INSERT INTO usuario (cpf, nome, sobrenome, dataNasc, telefone, email, senha, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $consulta = mysqli_prepare($this->conexao, $sql);
        mysqli_stmt_bind_param($consulta, 'ssssssss', $this->cpf, $this->nome, $this->sobrenome, $this->dataNasc, $this->telefone, $this->email, $this->senha, $this->foto_perfil);
        return mysqli_stmt_execute($consulta);
    }

    public function atualizar($idUsuario, $nome, $sobrenome, $email, $telefone, $senha, $fotoPerfil){
        if ($senha !== '') {
            $sql = 'UPDATE usuario SET nome = ?, sobrenome = ?, email = ?, telefone = ?, senha = ?, foto_perfil = ? WHERE id_usuario = ?';
            $consulta = mysqli_prepare($this->conexao, $sql);
            mysqli_stmt_bind_param($consulta, 'ssssssi', $nome, $sobrenome, $email, $telefone, $senha, $fotoPerfil, $idUsuario);
        } else {
            $sql = 'UPDATE usuario SET nome = ?, sobrenome = ?, email = ?, telefone = ?, foto_perfil = ? WHERE id_usuario = ?';
            $consulta = mysqli_prepare($this->conexao, $sql);
            mysqli_stmt_bind_param($consulta, 'sssssi', $nome, $sobrenome, $email, $telefone, $fotoPerfil, $idUsuario);
        }
        return mysqli_stmt_execute($consulta);
    }

}
?>
