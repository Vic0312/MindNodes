<?php
require_once __DIR__ . '/Item.php';

/** Escritas sao internas. Nao compra itens nem altera moedas ou avatar. */
class Inventario
{
    private $conexao;
    private $itemModel;

    public function __construct($conexao = null)
    {
        $this->conexao = $conexao ?: Conexao::obter();
        $this->itemModel = new Item($this->conexao);
    }

    private function executar($sql, $tipos, ...$valores)
    {
        $stmt = $this->conexao->prepare($sql);
        if (!$stmt) throw new RuntimeException('Falha ao preparar operacao de inventario.');
        try {
            if (!$stmt->bind_param($tipos, ...$valores)) throw new RuntimeException('Falha nos parametros.');
            if (!$stmt->execute()) throw new RuntimeException('Falha ao executar operacao de inventario.');
            return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
        } finally { $stmt->close(); }
    }

    private function validarUsuario($idUsuario)
    {
        $idUsuario = Item::validarId($idUsuario);
        if (!$this->executar('SELECT id_usuario FROM usuario WHERE id_usuario = ?', 'i', $idUsuario)) {
            throw new OutOfBoundsException('Usuario inexistente.');
        }
        return $idUsuario;
    }

    public function listarDoUsuario($idUsuario)
    {
        $idUsuario = $this->validarUsuario($idUsuario);
        return $this->executar('SELECT i.*, ui.data_compra FROM usuario_item ui JOIN item i ON i.id_item = ui.id_item WHERE ui.id_usuario = ? ORDER BY i.categoria, i.nome, i.id_item', 'i', $idUsuario);
    }

    public function listarPorCategoria($idUsuario, $categoria)
    {
        $categoria = Item::validarCategoria($categoria);
        $idUsuario = $this->validarUsuario($idUsuario);
        return $this->executar('SELECT i.*, ui.data_compra FROM usuario_item ui JOIN item i ON i.id_item = ui.id_item WHERE ui.id_usuario = ? AND i.categoria = ? ORDER BY i.nome, i.id_item', 'is', $idUsuario, $categoria);
    }

    /** Item ausente ou nao possuido retorna false; usuario inexistente e erro. */
    public function possuiItem($idUsuario, $idItem)
    {
        $idItem = Item::validarId($idItem);
        $idUsuario = $this->validarUsuario($idUsuario);
        return (bool) $this->executar('SELECT id_usuario_item FROM usuario_item WHERE id_usuario = ? AND id_item = ? LIMIT 1', 'ii', $idUsuario, $idItem);
    }

    /** Idempotente: retorna true inclusive se ja possuido. Participa da transacao do chamador. */
    public function adicionarItem($idUsuario, $idItem)
    {
        $idUsuario = $this->validarUsuario($idUsuario);
        $idItem = Item::validarId($idItem);
        if (!$this->itemModel->buscarPorId($idItem)) throw new OutOfBoundsException('Item inexistente.');
        // UNIQUE garante idempotencia tambem entre conexoes concorrentes, sem INSERT IGNORE.
        $this->executar('INSERT INTO usuario_item (id_usuario, id_item) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_usuario_item = id_usuario_item', 'ii', $idUsuario, $idItem);
        return true;
    }
}
