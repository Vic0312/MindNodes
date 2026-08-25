<?php
require_once __DIR__ . '/../model/Usuario.php';

class AuthController
{
    private $usuarioModel;
    public function __construct($usuarioModel = null) { $this->usuarioModel = $usuarioModel ?: new Usuario(); }
    public function efetuarLogin($email, $senha)
    {
        $usuario = $this->usuarioModel->buscarPorEmailESenha($email, $senha);
        $_SESSION['estaLogado'] = (bool) $usuario;
        return $usuario;
    }
    public function cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $fotoPerfil)
    {
        return (new Usuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $fotoPerfil))->cadastrar();
    }
    public function logout() { $_SESSION = []; session_destroy(); }
}
