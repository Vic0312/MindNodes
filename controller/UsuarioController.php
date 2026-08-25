<?php
require_once __DIR__ . '/../model/Usuario.php';

class UsuarioController
{
    private $usuarioModel;
    public function __construct($usuarioModel = null) { $this->usuarioModel = $usuarioModel ?: new Usuario(); }
    public function buscarPerfil($idUsuario) { return $this->usuarioModel->buscarPerfil((int) $idUsuario); }
    public function editarPerfilUsuario($idUsuario, $nome, $sobrenome, $email, $telefone, $senha, $fotoPerfil)
    {
        if ($senha !== '' && strlen($senha) < 8) {
            return false;
        }
        $senhaHash = $senha === '' ? '' : password_hash($senha, PASSWORD_DEFAULT);
        return $this->usuarioModel->atualizarDados((int) $idUsuario, $nome, $sobrenome, $email, $telefone, $senhaHash, $fotoPerfil);
    }
}
