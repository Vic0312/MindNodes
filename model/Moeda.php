<?php

require_once __DIR__ . '/../config/Conexao.php';

/** Ponto unico de escrita de usuario.moedas para os modulos da aplicacao. */
class Moeda
{
    private $conexao;
    private const MAX_INTEIRO = 2147483647;

    public function __construct($conexao = null)
    {
        $this->conexao = $conexao ?: Conexao::obter();
    }

    private function inteiroPositivo($valor)
    {
        if ((!is_int($valor) && !is_string($valor))
            || !preg_match('/^[1-9][0-9]*$/D', (string) $valor)
            || strlen((string) $valor) > 10 || $valor > self::MAX_INTEIRO) {
            throw new InvalidArgumentException('Informe um inteiro positivo de ate 2147483647.');
        }
        return (int) $valor;
    }

    private function executar($sql, $tipos = '', ...$valores)
    {
        $stmt = $this->conexao->prepare($sql);
        if (!$stmt) throw new RuntimeException('Falha ao preparar operacao de moedas.');
        try {
            if ($tipos !== '' && !$stmt->bind_param($tipos, ...$valores)) {
                throw new RuntimeException('Falha nos parametros da operacao.');
            }
            if (!$stmt->execute()) throw new RuntimeException('Falha ao executar operacao de moedas.');
            return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
        } finally {
            $stmt->close();
        }
    }

    private function saldoDaLinha($linhas)
    {
        if (!$linhas) throw new OutOfBoundsException('Usuario inexistente.');
        $saldo = (int) $linhas[0]['moedas'];
        if ($saldo < 0) throw new UnexpectedValueException('Saldo armazenado invalido.');
        return $saldo;
    }

    public function obterSaldo($idUsuario)
    {
        $idUsuario = $this->inteiroPositivo($idUsuario);
        return $this->saldoDaLinha($this->executar(
            'SELECT moedas FROM usuario WHERE id_usuario = ?', 'i', $idUsuario
        ));
    }

    /** Retorna o saldo confirmado; falhas sao comunicadas por excecoes. */
    public function creditar($idUsuario, $valor, $origem, $descricao = null)
    {
        return $this->movimentar($idUsuario, $valor, $origem, $descricao, 'credito');
    }

    public function debitar($idUsuario, $valor, $origem, $descricao = null)
    {
        return $this->movimentar($idUsuario, $valor, $origem, $descricao, 'debito');
    }

    private function movimentar($idUsuario, $valor, $origem, $descricao, $tipo)
    {
        $idUsuario = $this->inteiroPositivo($idUsuario);
        $valor = $this->inteiroPositivo($valor);
        if (!in_array($origem, ['quiz', 'loja', 'bonus'], true)) {
            throw new InvalidArgumentException('Origem invalida.');
        }
        if ($descricao !== null) {
            if (!is_string($descricao) || !preg_match('//u', $descricao)
                || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $descricao)) {
                throw new InvalidArgumentException('Descricao invalida.');
            }
            $descricao = trim($descricao);
            if (preg_match_all('/./us', $descricao) > 255) {
                throw new InvalidArgumentException('Descricao deve ter no maximo 255 caracteres.');
            }
            $descricao = $descricao === '' ? null : $descricao;
        }
        // START TRANSACTION confirmaria implicitamente uma transacao externa.
        $estado = $this->executar('SELECT @@in_transaction AS ativa, @@autocommit AS automatica')[0];
        if ($estado['ativa'] || !$estado['automatica']) {
            throw new LogicException('Moedas exige conexao sem transacao externa e com autocommit ativo.');
        }
        if (!$this->conexao->begin_transaction()) throw new RuntimeException('Falha ao iniciar transacao.');
        try {
            $saldo = $this->saldoDaLinha($this->executar(
                'SELECT moedas FROM usuario WHERE id_usuario = ? FOR UPDATE', 'i', $idUsuario
            ));
            if ($tipo === 'debito' && $saldo < $valor) throw new DomainException('Saldo insuficiente.');
            if ($tipo === 'credito' && $valor > self::MAX_INTEIRO - $saldo) {
                throw new OverflowException('Limite de saldo excedido.');
            }
            $novoSaldo = $tipo === 'credito' ? $saldo + $valor : $saldo - $valor;
            $this->executar('UPDATE usuario SET moedas = ? WHERE id_usuario = ?', 'ii', $novoSaldo, $idUsuario);
            $this->executar(
                'INSERT INTO transacao_moeda (id_usuario, tipo, valor, origem, descricao) VALUES (?, ?, ?, ?, ?)',
                'isiss', $idUsuario, $tipo, $valor, $origem, $descricao
            );
            if (!$this->conexao->commit()) throw new RuntimeException('Falha ao confirmar transacao.');
            return $novoSaldo;
        } catch (Throwable $erro) {
            if (!$this->conexao->rollback()) throw new RuntimeException('Falha ao reverter transacao.', 0, $erro);
            throw $erro;
        }
    }

    public function listarHistorico($idUsuario, $limite = null)
    {
        $idUsuario = $this->inteiroPositivo($idUsuario);
        if ($limite !== null) $limite = $this->inteiroPositivo($limite);
        $this->obterSaldo($idUsuario);
        $sql = 'SELECT id_transacao, tipo, valor, origem, descricao, data_transacao FROM transacao_moeda WHERE id_usuario = ? ORDER BY data_transacao DESC, id_transacao DESC';
        return $limite === null
            ? $this->executar($sql, 'i', $idUsuario)
            : $this->executar($sql . ' LIMIT ?', 'ii', $idUsuario, $limite);
    }
}
