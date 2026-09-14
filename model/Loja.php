<?php
require_once __DIR__ . '/Moeda.php';
require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/Inventario.php';

class ItemJaPossuidoException extends DomainException {}
class ItemIndisponivelException extends DomainException {}

class Loja
{
    private $conexao;
    private $moeda;
    private $itens;
    private $inventario;

    public function __construct($conexao = null)
    {
        $this->conexao = $conexao ?: Conexao::obter();
        $this->moeda = new Moeda($this->conexao);
        $this->itens = new Item($this->conexao);
        $this->inventario = new Inventario($this->conexao);
    }

    public function carregarDados($idUsuario)
    {
        $idUsuario = Item::validarId($idUsuario);
        $saldo = $this->moeda->obterSaldo($idUsuario);
        $possuido = array_fill_keys(array_column($this->inventario->listarDoUsuario($idUsuario), 'id_item'), true);
        $itens = [];
        foreach ($this->itens->listarAtivos() as $item) {
            if ((int) $item['preco'] <= 0) continue;
            $item['adquirido'] = isset($possuido[$item['id_item']]);
            $itens[] = $item;
        }
        return ['saldo' => $saldo, 'itens' => $itens];
    }

    public function comprar($idUsuario, $idItem)
    {
        $idUsuario = Item::validarId($idUsuario);
        $idItem = Item::validarId($idItem);
        $estado = $this->conexao->query('SELECT @@in_transaction AS ativa, @@autocommit AS automatica');
        if (!$estado) throw new RuntimeException('Falha ao consultar estado da transacao.');
        $transacao = $estado->fetch_assoc();
        $estado->free();
        if ($transacao['ativa'] || !$transacao['automatica']) {
            throw new LogicException('Compra exige conexao sem transacao externa e com autocommit ativo.');
        }
        if (!$this->conexao->begin_transaction()) throw new RuntimeException('Falha ao iniciar compra.');
        try {
            // A linha do usuario serializa compras concorrentes da mesma conta.
            $this->moeda->obterSaldoBloqueado($idUsuario);
            $item = $this->itens->buscarPorIdBloqueado($idItem);
            if (!$item || (int) $item['ativo'] !== 1 || (int) $item['preco'] <= 0) {
                throw new ItemIndisponivelException('Item indisponivel.');
            }
            if ($this->inventario->possuiItem($idUsuario, $idItem)) {
                throw new ItemJaPossuidoException('Item ja possuido.');
            }
            $this->moeda->debitarNaTransacao($idUsuario, (int) $item['preco'], 'loja', 'Compra: ' . $item['nome']);
            try {
                $this->inventario->registrarCompra($idUsuario, $idItem);
            } catch (mysqli_sql_exception $erro) {
                if ($erro->getCode() === 1062) throw new ItemJaPossuidoException('Item ja possuido.', 0, $erro);
                throw $erro;
            }
            if (!$this->conexao->commit()) throw new RuntimeException('Falha ao confirmar compra.');
            return $item;
        } catch (Throwable $erro) {
            if (!$this->conexao->rollback()) throw new RuntimeException('Falha ao reverter compra.', 0, $erro);
            throw $erro;
        }
    }
}
