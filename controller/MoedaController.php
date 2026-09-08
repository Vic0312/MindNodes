<?php

require_once __DIR__ . '/../model/Moeda.php';

/** API interna PHP. Nao despacha requisicoes HTTP nem recebe GET/POST.
 * O modulo chamador deve autorizar a acao e determinar usuario e valor no servidor.
 */
class MoedaController
{
    private $moedaModel;

    public function __construct($moedaModel = null)
    {
        $this->moedaModel = $moedaModel ?: new Moeda();
    }

    public function obterSaldo($idUsuario)
    {
        return $this->moedaModel->obterSaldo($idUsuario);
    }

    public function listarHistorico($idUsuario, $limite = null)
    {
        return $this->moedaModel->listarHistorico($idUsuario, $limite);
    }

    public function creditar($idUsuario, $valor, $origem, $descricao = null)
    {
        return $this->moedaModel->creditar($idUsuario, $valor, $origem, $descricao);
    }

    public function debitar($idUsuario, $valor, $origem, $descricao = null)
    {
        return $this->moedaModel->debitar($idUsuario, $valor, $origem, $descricao);
    }
}
