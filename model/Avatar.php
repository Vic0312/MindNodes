<?php
require_once __DIR__ . '/Inventario.php';

class Avatar
{
    private const BASE = 'img/avatar/base/node_base.png';
    private const SLOTS = [
        'roupa' => 'id_roupa',
        'rosto' => 'id_rosto',
        'cabelo' => 'id_cabelo',
        'acessorio' => 'id_acessorio',
    ];

    private $conexao;
    private $itens;
    private $inventario;

    public function __construct($conexao = null)
    {
        $this->conexao = $conexao ?: Conexao::obter();
        $this->itens = new Item($this->conexao);
        $this->inventario = new Inventario($this->conexao);
    }

    private function consultar($sql, $tipos, ...$valores)
    {
        $stmt = $this->conexao->prepare($sql);
        if (!$stmt) throw new RuntimeException('Falha ao preparar operacao de avatar.');
        try {
            if ($tipos !== '' && !$stmt->bind_param($tipos, ...$valores)) throw new RuntimeException('Falha nos parametros do avatar.');
            if (!$stmt->execute()) throw new RuntimeException('Falha na operacao de avatar.');
            return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
        } finally { $stmt->close(); }
    }

    private function validarUsuario($idUsuario)
    {
        $idUsuario = Item::validarId($idUsuario);
        if (!$this->consultar('SELECT id_usuario FROM usuario WHERE id_usuario = ?', 'i', $idUsuario)) {
            throw new OutOfBoundsException('Usuario inexistente.');
        }
        return $idUsuario;
    }

    public function criarAvatarPadrao($idUsuario)
    {
        $idUsuario = $this->validarUsuario($idUsuario);
        if ($this->consultar('SELECT id_avatar FROM avatar_usuario WHERE id_usuario = ?', 'i', $idUsuario)) return true;
        $padroes = [];
        foreach ($this->itens->buscarPadroes() as $item) {
            if (!$this->inventario->possuiItem($idUsuario, $item['id_item'])) {
                throw new LogicException('Item padrao ausente do inventario.');
            }
            $padroes[$item['categoria']] = (int) $item['id_item'];
        }
        $this->consultar(
            'INSERT INTO avatar_usuario (id_usuario, id_cabelo, id_rosto, id_roupa, id_acessorio) VALUES (?, ?, ?, ?, NULL) ON DUPLICATE KEY UPDATE id_usuario = id_usuario',
            'iiii', $idUsuario, $padroes['cabelo'], $padroes['rosto'], $padroes['roupa']
        );
        return true;
    }

    public function buscarDoUsuario($idUsuario)
    {
        $idUsuario = $this->validarUsuario($idUsuario);
        $linhas = $this->consultar('SELECT id_cabelo, id_rosto, id_roupa, id_acessorio FROM avatar_usuario WHERE id_usuario = ?', 'i', $idUsuario);
        if (!$linhas) {
            $this->criarAvatarPadrao($idUsuario);
            $linhas = $this->consultar('SELECT id_cabelo, id_rosto, id_roupa, id_acessorio FROM avatar_usuario WHERE id_usuario = ?', 'i', $idUsuario);
        }
        $registro = $linhas[0];
        $avatar = ['base' => ['imagem' => self::BASE]];
        foreach (self::SLOTS as $categoria => $slot) {
            $idItem = $registro[$slot];
            $item = $idItem === null ? null : $this->itens->buscarPorId($idItem);
            if ($categoria !== 'acessorio' && (!$item || $item['categoria'] !== $categoria)) {
                throw new UnexpectedValueException('Slot obrigatorio do avatar inconsistente.');
            }
            if ($item && $item['categoria'] !== $categoria) throw new UnexpectedValueException('Categoria do avatar inconsistente.');
            $avatar[$categoria] = $item;
        }
        return $avatar;
    }

    /** Apenas itens equipados contribuem; posse isolada nao concede habilidade. */
    public function obterHabilidadesEquipadas($idUsuario)
    {
        $avatar = $this->buscarDoUsuario($idUsuario);
        $habilidades = [];
        foreach (['cabelo', 'rosto', 'roupa', 'acessorio'] as $slot) {
            $item = $avatar[$slot];
            if (!$item || !in_array($item['habilidade'], ['dica', 'eliminar_alternativa', 'resumo_rapido'], true)) continue;
            $quantidade = (int) $item['valor_habilidade'];
            if ($quantidade <= 0) continue;
            $chave = $item['habilidade'];
            if (!isset($habilidades[$chave])) {
                $habilidades[$chave] = ['quantidade_total' => 0, 'quantidade_restante' => 0, 'itens' => []];
            }
            $habilidades[$chave]['quantidade_total'] += $quantidade;
            $habilidades[$chave]['quantidade_restante'] += $quantidade;
            $habilidades[$chave]['itens'][] = $item['nome'];
        }
        return $habilidades;
    }

    public function equiparItem($idUsuario, $idItem)
    {
        $idUsuario = $this->validarUsuario($idUsuario);
        $idItem = Item::validarId($idItem);
        $item = $this->itens->buscarPorId($idItem);
        if (!$item) throw new OutOfBoundsException('Item inexistente.');
        $categoria = Item::validarCategoria($item['categoria']);
        if (!$this->inventario->possuiItem($idUsuario, $idItem)) throw new DomainException('Item nao pertence ao usuario.');
        $this->criarAvatarPadrao($idUsuario);
        $slot = self::SLOTS[$categoria];
        $this->consultar("UPDATE avatar_usuario SET $slot = ? WHERE id_usuario = ?", 'ii', $idItem, $idUsuario);
        return true;
    }

    public function removerAcessorio($idUsuario)
    {
        $idUsuario = $this->validarUsuario($idUsuario);
        $this->criarAvatarPadrao($idUsuario);
        $this->consultar('UPDATE avatar_usuario SET id_acessorio = NULL WHERE id_usuario = ?', 'i', $idUsuario);
        return true;
    }
}
