<?php
require_once __DIR__ . '/../config/Conexao.php';

class Item
{
    private $conexao;

    public function __construct($conexao = null)
    {
        $this->conexao = $conexao ?: Conexao::obter();
    }

    public static function validarId($valor)
    {
        if ((!is_int($valor) && !is_string($valor))
            || !preg_match('/^[1-9][0-9]*$/D', (string) $valor)
            || strlen((string) $valor) > 10 || $valor > 2147483647) {
            throw new InvalidArgumentException('Informe um ID inteiro positivo de ate 2147483647.');
        }
        return (int) $valor;
    }

    public static function validarCategoria($categoria)
    {
        if (!in_array($categoria, ['cabelo', 'rosto', 'roupa', 'acessorio'], true)) {
            throw new InvalidArgumentException('Categoria invalida.');
        }
        return $categoria;
    }

    private function consultar($sql, $tipos = '', ...$valores)
    {
        $stmt = $this->conexao->prepare($sql);
        if (!$stmt) throw new RuntimeException('Falha ao preparar consulta de itens.');
        try {
            if ($tipos !== '' && !$stmt->bind_param($tipos, ...$valores)) throw new RuntimeException('Falha nos parametros.');
            if (!$stmt->execute()) throw new RuntimeException('Falha ao consultar itens.');
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally { $stmt->close(); }
    }

    public function listarTodos()
    {
        return $this->consultar('SELECT * FROM item ORDER BY categoria, nome, id_item');
    }

    public function listarAtivos()
    {
        return $this->consultar('SELECT * FROM item WHERE ativo = 1 ORDER BY categoria, nome, id_item');
    }

    /** Item inexistente retorna null; falhas de banco propagam excecoes. */
    public function buscarPorId($idItem)
    {
        return $this->consultar('SELECT * FROM item WHERE id_item = ?', 'i', self::validarId($idItem))[0] ?? null;
    }

    public function listarPorCategoria($categoria)
    {
        return $this->consultar('SELECT * FROM item WHERE categoria = ? ORDER BY nome, id_item', 's', self::validarCategoria($categoria));
    }

    /** Identificacao por nome e categoria; ausencia ou ambiguidade impede cadastro. */
    public function buscarPadroes()
    {
        $itens = [];
        foreach (['cabelo' => 'Cabelo Padrão', 'rosto' => 'Rosto Padrão', 'roupa' => 'Roupa Padrão'] as $categoria => $nome) {
            $linhas = $this->consultar('SELECT * FROM item WHERE categoria = ? AND nome = ?', 'ss', $categoria, $nome);
            if (count($linhas) !== 1) throw new UnexpectedValueException('Item padrao ausente ou ambiguo: ' . $nome);
            $itens[] = $linhas[0];
        }
        return $itens;
    }
}
