<?php
require_once __DIR__ . '/../model/Avatar.php';

class AvatarController
{
    private $avatarModel;

    public function __construct($avatarModel = null)
    {
        $this->avatarModel = $avatarModel ?: new Avatar();
    }

    private function usuarioAtual()
    {
        if (empty($_SESSION['estaLogado']) || !isset($_SESSION['usuario_id'])) {
            throw new LogicException('Autenticacao necessaria.');
        }
        return Item::validarId($_SESSION['usuario_id']);
    }

    public function buscarDoUsuario()
    {
        return $this->avatarModel->buscarDoUsuario($this->usuarioAtual());
    }

    public function obterHabilidadesEquipadas()
    {
        return $this->avatarModel->obterHabilidadesEquipadas($this->usuarioAtual());
    }

    public function criarAvatarPadrao()
    {
        return $this->avatarModel->criarAvatarPadrao($this->usuarioAtual());
    }

    public function equiparItem($idItem)
    {
        return $this->avatarModel->equiparItem($this->usuarioAtual(), $idItem);
    }

    public function removerAcessorio()
    {
        return $this->avatarModel->removerAcessorio($this->usuarioAtual());
    }
}
