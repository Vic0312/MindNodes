<?php
require_once __DIR__ . '/../model/Loja.php';

class LojaController
{
    private $loja;

    public function __construct($loja = null)
    {
        $this->loja = $loja ?: new Loja();
    }

    private function usuarioAtual()
    {
        if (empty($_SESSION['estaLogado']) || !isset($_SESSION['usuario_id'])) {
            throw new LogicException('Autenticacao necessaria.');
        }
        return Item::validarId($_SESSION['usuario_id']);
    }

    public function carregarDados()
    {
        return $this->loja->carregarDados($this->usuarioAtual());
    }

    public function comprar($idItem)
    {
        $item = $this->loja->comprar($this->usuarioAtual(), $idItem);
        return $item['nome'] . ' comprado com sucesso.';
    }
}
