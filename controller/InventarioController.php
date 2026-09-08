<?php
require_once __DIR__ . '/../model/Inventario.php';

/** API PHP interna de leitura. O chamador deve usar o usuario da sessao em contexto HTTP. */
class InventarioController
{
    private $inventarioModel;

    public function __construct($inventarioModel = null)
    {
        $this->inventarioModel = $inventarioModel ?: new Inventario();
    }

    public function listarDoUsuario($idUsuario)
    {
        return $this->inventarioModel->listarDoUsuario($idUsuario);
    }

    public function listarPorCategoria($idUsuario, $categoria)
    {
        return $this->inventarioModel->listarPorCategoria($idUsuario, $categoria);
    }

    public function possuiItem($idUsuario, $idItem)
    {
        return $this->inventarioModel->possuiItem($idUsuario, $idItem);
    }
}
